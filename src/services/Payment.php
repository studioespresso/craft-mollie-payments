<?php

namespace studioespresso\molliepayments\services;

use craft\base\Component;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\elements\Payment as PaymentElement;

class Payment extends Component
{

    public function getStatus($id) {
        $element = PaymentElement::findOne(['id' => $id]);
        if($element) {
            return $element->paymentStatus;
        }
    }
}
