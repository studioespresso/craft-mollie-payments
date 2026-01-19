<?php

namespace studioespresso\molliepayments\variables;

use craft\elements\User;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\SubscriberRecord;

class MollieVariable
{
    public function getSubscriptionsByUid($uid)
    {
        $subscriber = SubscriberRecord::findOne(['uid' => $uid]);
        if (!$subscriber) {
            return [];
        }
        return Subscription::findAll(['email' => $subscriber->email]);
    }

    public function getSubscriptionsByUser(User $user)
    {
        $subscriber = SubscriberRecord::findOne(['userId' => $user->id]);
        if (!$subscriber) {
            return [];
        }
        return Subscription::findAll(['email' => $subscriber->email]);
    }

    public function getPaymentMethods(string $formHandle, array|null $args)
    {
        $data = MolliePayments::getInstance()->mollie->getPaymentMethods($formHandle, $args);
        return $data->getArrayCopy() ?? null;
    }
}
