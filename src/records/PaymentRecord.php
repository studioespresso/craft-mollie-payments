<?php

namespace studioespresso\molliepayments\records;

use craft\db\ActiveRecord;

class PaymentRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%mollie_payments}}';
    }
}
