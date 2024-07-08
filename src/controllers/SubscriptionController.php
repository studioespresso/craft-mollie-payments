<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\base\Element;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Mollie\Api\Types\PaymentStatus;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\SubscriberRecord;
use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class SubscriptionController extends Controller
{
    protected array|int|bool $allowAnonymous = ['subscribe', 'redirect', 'webhook', 'get-customer', 'cancel'];

    public function beforeAction($action): bool
    {
        if ($action->id === 'webhook') {
            $this->enableCsrfValidation = false;
        }

        if (!ConfigHelper::localizedValue(MolliePayments::$plugin->getSettings()->apiKey)) {
            throw new InvalidConfigException("No Mollie API key set");
        }
        return parent::beforeAction($action);
    }

    public function actionSubscribe()
    {
        $redirect = Craft::$app->request->getBodyParam('redirect');
        $redirect = Craft::$app->security->validateData($redirect);

        $email = Craft::$app->request->getRequiredBodyParam('email');
        $amount = Craft::$app->request->getValidatedBodyParam('amount');
        $form = Craft::$app->request->getValidatedBodyParam('form');

        $paymentForm = MolliePayments::getInstance()->forms->getFormByHandle($form);
        if (!$paymentForm) {
            throw new NotFoundHttpException("Form not found", 404);
        }

        if ($paymentForm->type !== PaymentFormModel::TYPE_SUBSCRIPTION) {
            throw new InvalidConfigException("Incorrect form type for this request", 500);
        }

        $times = $this->request->getBodyParam('times', null);
        $interval = $this->request->getRequiredBodyParam('interval');
        $times = $this->request->getBodyParam('times', null);

        if (!MolliePayments::$plugin->mollie->validateInterval($interval)) {
            throw new HttpException(400, Craft::t('mollie-payments', 'Interval must be a valid interval'));
        }

        // Create subscription
        $subscription = new Subscription();
        $subscription->email = $email;
        $subscription->formId = $paymentForm->id;
        $subscription->fieldLayoutId = $paymentForm->fieldLayout;

        $subscription->amount = $amount;
        $subscription->interval = $interval;
        $subscription->times = $times;

        $subscription->subscriptionStatus = "pending";
        $fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');
        $subscription->setFieldValuesFromRequest($fieldsLocation);


        $subscription->setScenario(Element::SCENARIO_LIVE);

        $subscriber = MolliePayments::$plugin->subscriber->getOrCreateSubscriberByEmail($email);
        $subscription->customerId = $subscriber->customerId;

        if (!$subscription->validate()) {
            // TODO Remove this before release
            dd($subscription->getErrors());
            // Send the payment back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $subscription,
            ]);
            return null;
        }

        if (MolliePayments::getInstance()->subscription->save($subscription)) {
            $url = MolliePayments::getInstance()->mollie->createFirstPayment(
                $subscription,
                $subscriber,
                $paymentForm,
                $redirect
            );
            return $this->redirect($url);
        }
    }

    /**
     * @param $uid
     * @since 1.0.0
     */
    public function actionEdit($uid)
    {
        $query = Subscription::find();
        $query->uid = $uid;
        $element = $query->one();

        if ($element->customerId !== null) {
            $subscriber = SubscriberRecord::findOne(['customerId' => $element->customerId]);
        }

        $form = MolliePayments::getInstance()->forms->getFormByid($element->formId);
        $transactions = MolliePayments::getInstance()->transaction->getAllByPayment($element->id);


        $data = [
            'element' => $element,
            'transactions' => $transactions,
            'subscriber' => $subscriber ?? null,
            'form' => $form,
        ];

        return $this->asCpScreen()
            ->title("Subscription - {$form->title} - {$element->email}")
            ->crumbs([
                ['label' => 'Subscriptions', 'url' => UrlHelper::cpUrl('mollie-payments/subscriptions')],
                ['label' => $element->email, 'url' => $element->getCpEditUrl()],
            ])
            ->action('mollie-payments/subscription/save-cp')
            ->selectedSubnavItem('subscriptions')
            ->metaSidebarTemplate('mollie-payments/_subscription/_edit/_details', $data)
            ->contentTemplate('mollie-payments/_subscription/_edit/_content', $data);
    }

    public function actionSaveCp()
    {
        $element = Subscription::findOne(['id' => $this->request->getRequiredBodyParam('elementId')]);
        $element->setFieldValuesFromRequest('fields');
        $element->setScenario('live');
        if (!$element->validate()) {
            // Send the payment back to the template

            return $this->runAction('edit', ['uid' => $element->uid, 'element' => $element]);
        }

        Craft::$app->getElements()->saveElement($element);
        return $this->redirect(UrlHelper::cpUrl($element->getCpEditUrl()));
    }

    public function actionRedirect()
    {
        $request = Craft::$app->getRequest();
        $uid = $request->getQueryParam('subscriptionUid');

        $redirect = $request->getQueryParam('redirect');
        $element = Subscription::findOne(['uid' => $uid]);
        $transaction = MolliePayments::$plugin->transaction->getTransactionbyPayment($element->id);

        try {
            $molliePayment = MolliePayments::$plugin->mollie->getStatus($transaction->id);
            $this->redirect(UrlHelper::url($redirect, ['subscription' => $uid, 'status' => $molliePayment->status]));
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Payments not found', '404');
        }
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     * @since 1.0.0
     */
    public function actionWebhook(): void
    {
        $id = Craft::$app->getRequest()->getRequiredParam('id');
        $transaction = MolliePayments::getInstance()->transaction->getTransactionbyId($id);
        $molliePayment = MolliePayments::getInstance()->mollie->getStatus($id);

        // If we have a subscription id, the payment belongs to an active subscription
        // So we need to create a new transaction for it.
        if ($molliePayment->subscriptionId && !$transaction) {
            $subscription = Subscription::findOne(['subscriptionId' => $molliePayment->subscriptionId]);
            $form = $subscription->getForm();

            $model = new PaymentTransactionModel();
            $model->id = $molliePayment->id;
            $model->payment = $subscription->id;
            $model->currency = $form->currency;
            $model->amount = $molliePayment->amount->value;
            $model->status = $molliePayment->status;
            MolliePayments::getInstance()->transaction->save($model);
            return;
        }

        MolliePayments::getInstance()->transaction->updateTransaction($transaction, $molliePayment);
        $subscriptionElement = Subscription::findOne(['id' => $transaction->payment]);
        if (in_array($molliePayment->status, [PaymentStatus::STATUS_PAID, PaymentStatus::STATUS_OPEN])
            && $molliePayment->metadata->createSubscription
            && !$molliePayment->subscriptionId
        ) {
            MolliePayments::$plugin->mollie->createSubscription($subscriptionElement);
            return;
        }
    }

    public function actionGetLinkForCustomer()
    {
        $email = $this->request->getRequiredBodyParam('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Craft::$app->getUrlManager()->setRouteParams([
                'email' => $email,
                'error' => Craft::t('mollie-payments', 'Invalid email address'),
            ]);
            return null;
        }

        $customer = SubscriberRecord::findOne(['email' => $email]);
        if ($customer) {
            MolliePayments::getInstance()->mail->sendSubscriptionAccessEmail($customer);
        }

        if ($this->request->isAjax) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        return $this->redirectToPostedUrl();
    }

    public function actionCancel()
    {
        $this->requirePostRequest();

        $subscription = $this->request->getRequiredBodyParam('subscription');
        $subscriber = $this->request->getRequiredBodyParam('subscriber');

        $subscription = Subscription::findOne(['id' => $subscription]);
        $subscriber = SubscriberRecord::findOne(['uid' => $subscriber]);
        if (!$subscription->subscriptionId) {
            Craft::error("Subscription ID missing", MolliePayments::class);
            $subscription->subscriptionStatus = 'canceled';
            Craft::$app->getElements()->saveElement($subscription);
        }

        if (MolliePayments::getInstance()->mollie->cancelSubscription($subscriber, $subscription)) {
            $subscription->subscriptionStatus = 'canceled';
            Craft::$app->getElements()->saveElement($subscription);
        }
        if ($this->request->isCpRequest) {
            return $this->redirect($subscription->cpEditUrl);
        }

        if ($this->request->isAjax) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        return $this->redirectToPostedUrl();
    }
}