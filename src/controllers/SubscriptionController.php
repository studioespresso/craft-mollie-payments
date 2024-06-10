<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\base\Element;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Mollie\Api\Types\PaymentStatus;
use studioespresso\molliepayments\elements\Subscription;
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


        $duration = $this->request->getBodyParam('duration', null);
        $interval = $this->request->getRequiredBodyParam('interval');
        if (!MolliePayments::$plugin->mollie->validateInterval($interval)) {
            throw new HttpException(400, "Incorrent subscription interval");
        }


        // Create subscription
        $subscription = new Subscription();
        $subscription->email = $email;
        $subscription->formId = $paymentForm->id;
        $subscription->fieldLayoutId = $paymentForm->fieldLayout;

        $subscription->amount = $amount;
        $subscription->interval = $interval;

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

        $form = MolliePayments::getInstance()->forms->getFormByid($element->formId);
//        $transactions = MolliePayments::getInstance()->transaction->getAllByPayment($element->id);

        $this->renderTemplate('mollie-payments/_subscription/_edit', ['element' => $element, 'form' => $form]);
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

        if ($molliePayment->subscriptionId) {
            //TODO  Create new transation for subsequent recurring payments

        }

        MolliePayments::getInstance()->transaction->updateTransaction($transaction, $molliePayment);
        $subscriptionElement = Subscription::findOne(['id' => $transaction->payment]);
        if (in_array($molliePayment->status, [PaymentStatus::STATUS_PAID, PaymentStatus::STATUS_OPEN])
            && $molliePayment->metadata->createSubscription
            && !$molliePayment->subscriptionId
        ) {
            MolliePayments::$plugin->mollie->createSubscription($subscriptionElement);
        }
    }

    public function actionGetLinkForCustomer() {
        $email = $this->request->getRequiredBodyParam('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Craft::$app->getUrlManager()->setRouteParams([
                'email' => $email,
                'error' => Craft::t('mollie-payments', 'Invalid email address'),
            ]);
            return null;
        }
        $customer = SubscriberRecord::findOne(['email' => $email]);
        MolliePayments::getInstance()->mail->sendSubscriptionAccessEmail($customer);
        return $this->redirectToPostedUrl();
    }

    public function actionCancel($subscription, $subscriber)
    {
        $subscription = Subscription::findOne(['id' => $subscription]);
        $subscriber = SubscriberRecord::findOne(['uid' => $subscriber]);
        if(!$subscription->subscriptionId) {
            Craft::error("Subscription ID missing", MolliePayments::class);
            $subscription->status = 'cancelled';
            Craft::$app->getElements()->saveElement($subscription);
            return $this->redirectToPostedUrl();
        }

        if(MolliePayments::getInstance()->mollie->cancelSubscription($subscriber, $subscription)) {
            // TODO Should we do this here or should we wait for the webhook? Is there a webhook for this?
            return $this->redirectToPostedUrl();
        }
    }
}
