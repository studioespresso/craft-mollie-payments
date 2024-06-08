<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\base\Element;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\MolliePayments;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class SubscriptionController extends Controller
{
    protected array|int|bool $allowAnonymous = ['subscribe', 'redirect', 'webhook'];

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
//        $subscription->setFieldValuesFromRequest($fieldsLocation);
        $subscription->setFieldValue("firstName", "Jan");
        $subscription->setFieldValue("lastName", "Henckens");

        $subscription->setScenario(Element::SCENARIO_LIVE);

        $subscriber = MolliePayments::$plugin->subscriber->getOrCreateSubscriberByEmail($email);

        if (!$subscription->validate()) {
            // TODO Remove this before release
            dd($subscription->getErrors());
            // Send the payment back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $subscription,
            ]);
            return null;
        }
        
        MolliePayments::getInstance()->subscription->save($subscription);
        return $this->redirect($redirect);
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
        $uid = Craft::$app->getRequest()->getRequiredParam('order_id');
        $redirect = Craft::$app->getRequest()->getParam('redirect');

        $payment = Payment::findOne(['uid' => $uid]);
        $transaction = MolliePayments::getInstance()->transaction->getTransactionbyPayment($payment->id);
        if ($redirect != $transaction->redirect) {
            throw new InvalidArgumentException("Invalid redirect");
        }

        try {
            $molliePayment = MolliePayments::getInstance()->mollie->getStatus($transaction->id);
            $this->redirect(UrlHelper::url($redirect, ['payment' => $uid, 'status' => $molliePayment->status]));
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Payment not found', '404');
        }
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     * @since 1.0.0
     */
    public function actionWebhook()
    {
        $id = Craft::$app->getRequest()->getRequiredParam('id');
        $transaction = MolliePayments::getInstance()->transaction->getTransactionbyId($id);
        $molliePayment = MolliePayments::getInstance()->mollie->getStatus($id);
        MolliePayments::getInstance()->transaction->updateTransaction($transaction, $molliePayment);
        return;
    }
}
