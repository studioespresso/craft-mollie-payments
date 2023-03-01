<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use Mollie\Api\MollieApiClient;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

class Mollie extends Component
{
    private MollieApiClient $mollie;

    public function init(): void
    {
        $this->mollie = new \Mollie\Api\MollieApiClient();
        $this->mollie->setApiKey(App::parseEnv()(ConfigHelper::localizedValue(MolliePayments::$plugin->getSettings()->apiKey)));
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
                "value" => number_format($payment->amount, 2, '.', '') // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => $description,
            "redirectUrl" => UrlHelper::url("{$baseUrl}mollie-payments/payment/redirect", [
                "order_id" => $payment->uid,
                "redirect" => $redirect
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

    public function getStatus($orderId)
    {
        return $this->mollie->payments->get($orderId);
    }

}
