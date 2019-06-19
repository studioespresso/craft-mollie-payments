<?php
namespace studioespresso\molliepayments\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;


class PaymentQuery extends ElementQuery
{

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('mollie_payments');

        return parent::beforePrepare();
    }
}
