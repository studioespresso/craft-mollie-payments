<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use studioespresso\molliepayments\elements\Payment as PaymentElement;
use studioespresso\molliepayments\events\PaymentUpdateEvent;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

class Payment extends Component
{
    public function getStatus($id)
    {
        $element = PaymentElement::findOne(['id' => $id]);
        if ($element) {
            return $element->paymentStatus;
        }
    }

    public function save($payment)
    {
        $this->trigger(MolliePayments::EVENT_BEFORE_PAYMENT_SAVE,
            new PaymentUpdateEvent([
                'payment' => $payment,
                'isNew' => true,
            ])
        );

        if (Craft::$app->getElements()->saveElement($payment)) {
            return $payment;
        } else {
            return false;
        };
    }


    public function handleFreePayment(PaymentElement $payment, PaymentFormRecord $form, $redirect)
    {
        $transaction = new PaymentTransactionModel();
        $transaction->id = "free_{$payment->id}";
        $transaction->payment = $payment->id;
        $transaction->currency = $form->currency;
        $transaction->amount = $payment->amount;
        $transaction->status = "free";
        MolliePayments::getInstance()->transaction->save($transaction);

        $payment->paymentStatus = 'free';
        MolliePayments::getInstance()->payment->save($payment);
        MolliePayments::getInstance()->transaction->fireEventAfterTransactionUpdate($transaction, $payment, "free");

        $redirect = UrlHelper::url($redirect, ['payment' => $payment->uid, 'status' => 'free']);
        return $redirect;
    }
}
