<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use studioespresso\molliepayments\elements\Subscription as SubscriptionElement;

class Subscription extends Component
{
    public function getStatus($id)
    {
        $element = SubscriptionElement::findOne(['id' => $id]);
        if ($element) {
            return $element->subscriptionStatus;
        }
    }

    public function save($element)
    {
        //TODO Replace this event
//        $this->trigger(MolliePayments::EVENT_BEFORE_PAYMENT_SAVE,
//            new PaymentUpdateEvent([
//                'payment' => $element,
//                'isNew' => true,
//            ])
//        );
        if (Craft::$app->getElements()->saveElement($element)) {
            return $element;
        } else {
            return false;
        };
    }
}
