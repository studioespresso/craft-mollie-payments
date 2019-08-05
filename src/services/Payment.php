<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use studioespresso\molliepayments\events\PaymentUpdateEvent;
use studioespresso\molliepayments\events\TransactionUpdateEvent;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\elements\Payment as PaymentElement;

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
                'isNew' => true
            ])
        );
        
        Craft::$app->getElements()->saveElement($payment);
        
        return $payment;
    }
}
