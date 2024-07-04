<?php

namespace studioespresso\molliepayments\records;

use craft\db\ActiveRecord;

class SubscriberRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%mollie_subscribers}}';
    }
}
