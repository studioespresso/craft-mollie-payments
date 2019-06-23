<?php

namespace studioespresso\molliepayments\services;

use craft\base\Component;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
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
        $url = $this->mollie->payments->create([
            "amount" => [
                "currency" => "EUR",
                "value" => number_format($payment->amount, 2, '.', '') // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => "Order #{$payment->id}",
            "redirectUrl" => "/mollie-payments/payment/callback?order_id={$payment->uid}",
            "webhookUrl" => "/mollie-payments/payment/webhook",
            "metadata" => [
                "redirectUrl" => $redirect,
            ],
        ]);
    }

}
