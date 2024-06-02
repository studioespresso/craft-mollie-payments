<?php

namespace studioespresso\molliepayments\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use studioespresso\molliepayments\records\SubscriptionRecord;

class SubscriptionQuery extends ElementQuery
{
    public $formId;

    public $interval;

    public $email;

    public function email($value): self
    {
        $this->email = $value;
        return $this;
    }

    public function hash($value): self
    {
        $uid = \Craft::$app->getSecurity()->validateData($value);
        $this->uid = $uid;
        return $this;
    }

    public function status(array|string|null $value): static
    {
        $this->paymentStatus = $value;
        return $this;
    }


    public function paymentStatus($value): self
    {
        $this->paymentStatus = $value;
        return $this;
    }

    public function formId($value): self
    {
        $this->formId = $value;
        return $this;
    }

    protected function statusCondition(string $status): mixed
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
            case 'refunded':
                return ['paymentStatus' => 'refunded'];
            default:
                return parent::statusCondition($status);
        }
    }


    protected function beforePrepare(): bool
    {
        $this->joinElementTable(SubscriptionRecord::tableName());
        // select the columns
        $this->query->select([
            'mollie_payments.email',
            'mollie_payments.amount',
            'mollie_payments.formId',
            'mollie_payments.interval',
        ]);

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('mollie_payments.email', $this->email));
        }

        if ($this->formId) {
            $this->subQuery->andWhere(Db::parseParam('mollie_payments.formId', $this->formId));
        }

        return parent::beforePrepare();
    }
}
