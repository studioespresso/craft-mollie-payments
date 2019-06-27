<?php
namespace studioespresso\molliepayments\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class PaymentQuery extends ElementQuery
{

    public $formId;

    public function formId($value)
    {
        $this->formId = $value;
        return $this;
    }

    protected function statusCondition(string $status)
    {
        switch ($status) {
            case 'paid':
                return ['paid' => true];
            case 'expired':
                return ['expired' => true];
            default:
                return parent::statusCondition($status);
        }
    }


    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('mollie_payments');
        // select the columns
        $this->query->select([
            'mollie_payments.email',
            'mollie_payments.amount'
        ]);

        return parent::beforePrepare();
    }
}
