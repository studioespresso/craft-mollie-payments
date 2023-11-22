<?php

namespace studioespresso\molliepayments\records;

use craft\db\ActiveRecord;

class PaymentFormRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%mollie_forms}}';
    }
}
