<?php

namespace studioespresso\molliepayments\records;

use craft\db\ActiveRecord;

class SubscriptionRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%mollie_subscriptions}}';
    }
}
