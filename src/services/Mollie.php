<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;

class Mollie extends Component
{
    private MollieApiClient $mollie;

    public function init(): void
    {
        $this->mollie = new \Mollie\Api\MollieApiClient();
        $this->mollie->setApiKey(App::parseEnv(ConfigHelper::localizedValue(MolliePayments::$plugin->getSettings()->apiKey)));
    }

    public function generatePayment(Payment $payment, $redirect, $extraMeta = [])
    {
        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($payment->formId);
        $baseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();


        if ($paymentForm->descriptionFormat) {
            $description = Craft::$app->getView()->renderObjectTemplate($paymentForm->descriptionFormat, $payment);
        } else {
            $description = "Order #{$payment->id}";
        }

        $currentSite = Craft::$app->getSites()->getCurrentSite();
        $metaData = [
            "redirectUrl" => $redirect,
            "element" => $payment->uid,
            "e-mail" => $payment->email,
            "description" => $description,
            'currentSite' => $currentSite->handle,
        ];

        if ($extraMeta) {
            foreach (array_keys($metaData) as $key) {
                if (isset($extraMeta[$key])) {
                    unset($extraMeta[$key]);
                }
            }
        }

        $metaData = array_merge($metaData, $extraMeta);

        $authorization = $this->mollie->payments->create([
            "amount" => [
                "currency" => $paymentForm->currency,
                "value" => number_format($payment->amount, 2, '.', ''), // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => $description,
            "redirectUrl" => UrlHelper::url("{$baseUrl}mollie-payments/payment/redirect", [
                "order_id" => $payment->uid,
                "redirect" => $redirect,
            ]),
            "webhookUrl" => "{$baseUrl}mollie-payments/payment/webhook",
            "metadata" => $metaData,
        ]);


        $transaction = new PaymentTransactionModel();
        $transaction->id = $authorization->id;
        $transaction->payment = $payment->id;
        $transaction->currency = $paymentForm->currency;
        $transaction->amount = $payment->amount;
        $transaction->redirect = $redirect;
        $transaction->status = $authorization->status;

        MolliePayments::getInstance()->transaction->save($transaction);


        return $authorization->_links->checkout->href;
    }

    public function createFirstPayment(\studioespresso\molliepayments\elements\Subscription $subscription, PaymentFormModel $form, $redirect)
    {
        if ($form->description) {
            $description = Craft::$app->getView()->renderObjectTemplate($form->description, $subscription);
        } else {
            $description = "Order #{$subscription->id}";
        }

        $response = $this->mollie->payments->create([
            "amount" => [
                "value" => number_format((float)$subscription->amount, 2, '.', ''),
                "currency" => $form->currency
            ],
            "customerId" => $subscriber->customerId,
            "sequenceType" => "first",
            "description" => $description,
            "redirectUrl" => UrlHelper::url("{$this->baseUrl}mollie-subscriptions/subscriptions/process", [
                "formUid" => $form->uid,
                "subscriptionUid" => $subscription->uid,
                "redirect" => $redirect
            ]),
            "webhookUrl" => "{$this->baseUrl}mollie-subscriptions/subscriptions/webhook",
            "metadata" => [
                "plan" => $form->id,
                "customer" => $subscriber->id,
                "createSubscription" => true //  TODO interval true or false
            ]
        ]);

        $transaction = new PaymentTransactionModel();
        $transaction->id = $response->id;
        $transaction->payment = $subscription->id;
        $transaction->currency = $form->currency;
        $transaction->amount = $response->amount;
        $transaction->redirect = $redirect;
        $transaction->status = $authorization->status;

        MollieSubscriptions::$plugin->payments->save($transaction);


        return $response->_links->checkout->href;
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function createCustomer($email): Customer
    {
        $customer = $this->mollie->customers->create([
            "email" => $email,
        ]);
        return $customer;
    }

    public function getStatus($orderId)
    {
        return $this->mollie->payments->get($orderId);
    }

    public function validateInterval($interval): bool
    {
        $split = explode(' ', $interval);

        if (count($split) != 2) {
            return false;
        }

        if (!is_int((int)$split[0])) {
            return false;
        }
        if (!in_array($split[1], ['months', 'weeks', 'days'])) {
            return false;
        }
        return true;

    }
}
