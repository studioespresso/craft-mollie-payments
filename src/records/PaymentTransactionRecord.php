<?php

namespace studioespresso\molliepayments\records;

use craft\db\ActiveRecord;


class PaymentTransactionRecord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%mollie_transactions}}';
    }
}