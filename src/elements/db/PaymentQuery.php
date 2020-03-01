<?php

namespace studioespresso\molliepayments\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class PaymentQuery extends ElementQuery
{

    public $formId;

    public $paymentStatus;

    public $email;

    public function email($value)
    {
        $this->email = $value;
        return $this;
    }

    public function hash($value)
    {
        $uid = \Craft::$app->getSecurity()->validateData($value);
        $this->uid = $uid;
        return $this;
    }

    public function status($value)
    {
        $this->paymentStatus = $value;
        d($this->paymentStatus);
        return $this;
    }


    public function paymentStatus($value)
    {
        $this->paymentStatus = $value;
        return $this;
    }

    public function formId($value)
    {
        $this->formId = $value;
        return $this;
    }

    protected function statusCondition(string $status)
    {
        switch ($status) {
            case 'cart':
                return ['paymentStatus' => 'cart'];
            case 'free':
                return ['paymentStatus' => 'free'];
            case 'pending':
                return ['paymentStatus' => 'pending'];
            case 'paid':
                return ['paymentStatus' => 'paid'];
            case 'expired':
                return ['paymentStatus' => 'expired'];
            default:
                return parent::statusCondition($status);
        }
    }


    protected function beforePrepare(): bool
    {
        $this->joinElementTable('mollie_payments');
        // select the columns
        $this->query->select([
            'mollie_payments.email',
            'mollie_payments.amount',
            'mollie_payments.formId',
            'mollie_payments.paymentStatus',
        ]);

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('mollie_payments.email', $this->email));
        }

        if ($this->formId) {
            $this->subQuery->andWhere(Db::parseParam('mollie_payments.formId', $this->formId));
        }

        if ($this->paymentStatus) {
            $this->subQuery->andWhere(Db::parseParam('mollie_payments.paymentStatus', $this->status));
        }

        return parent::beforePrepare();
    }
}
