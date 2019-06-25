<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

class MollieService extends Component
{
    private $mollie;

    public function init()
    {
        $this->mollie = new \Mollie\Api\MollieApiClient();
        $this->mollie->setApiKey(MolliePayments::getInstance()->getSettings()->apiKey);
    }

    public function generatePayment(Payment $payment, $redirect)
    {
        $baseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
        $authorization = $this->mollie->payments->create([
            "amount" => [
                "currency" => "EUR",
                "value" => number_format($payment->amount, 2, '.', '') // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => "Order #{$payment->id}",
            "redirectUrl" => UrlHelper::url("{$baseUrl}mollie-payments/payment/redirect", [
                "order_id" => $payment->uid,
                "redirect" => $redirect
            ]),
            "webhookUrl" => "{$baseUrl}mollie-payments/payment/webhook",
            "metadata" => [
                "redirectUrl" => $redirect,
                "element" => $payment->uid,
                "description" => $payment->title
            ],
        ]);

        $transaction = new PaymentTransactionModel();
        $transaction->id = $authorization->id;
        $transaction->payment = $payment->id;
        $transaction->status = $authorization->status;

        MolliePayments::getInstance()->transaction->save($transaction);


        return $authorization->_links->checkout->href;
    }

    public function getStatus($orderId)
    {
        return $this->mollie->payments->get($orderId);
    }

}
