<?php

namespace studioespresso\molliepayments\variables;

use craft\elements\User;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\records\SubscriberRecord;

class MollieVariable
{
    public function getSubscriptionsByUid($uid)
    {
        $subscriber = SubscriberRecord::findOne(['uid' => $uid]);
        return Subscription::findAll(['email' => $subscriber->email]);
    }

    public function getSubscriptionsByUser(User $user)
    {
        $subscriber = SubscriberRecord::findOne(['userId' => $user->id]);
        return Subscription::findAll(['email' => $subscriber->email]);
    }
}
