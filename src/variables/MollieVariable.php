<?php

namespace studioespresso\molliepayments\variables;

use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\records\SubscriberRecord;

class MollieVariable
{
    public function getSubscriptionbyUid($uid)
    {
        $subscriber = SubscriberRecord::findOne(['uid' => $uid]);
        return Subscription::findAll(['email' => $subscriber->email]);
    }
}