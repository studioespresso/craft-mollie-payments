<?php

namespace studioespresso\molliepayments\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use studioespresso\molliepayments\records\SubscriptionRecord;

class SubscriptionQuery extends ElementQuery
{
    public $formId;

    public $interval;

    public $subscriptionStatus;

    public $subscriptionId;

    public $customerId;

    public $email;

    public function email($value): self
    {
        $this->email = $value;
        return $this;
    }

    public function customerId($value): self
    {
        $this->customerId = $value;
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
        $this->subscriptionStatus = $value;
        return $this;
    }

    public function subscriptionStatus($value): self
    {
        $this->subscriptionStatus = $value;
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
                return ['subscriptionStatus' => 'cart'];
            case 'free':
                return ['subscriptionStatus' => 'free'];
            case 'ongoing':
                return ['subscriptionStatus' => 'ongoing'];
            case 'pending':
                return ['subscriptionStatus' => 'pending'];
            case 'paid':
                return ['subscriptionStatus' => 'paid'];
            case 'expired':
                return ['subscriptionStatus' => 'expired'];
            case 'refunded':
                return ['subscriptionStatus' => 'refunded'];
            default:
                return parent::statusCondition($status);
        }
    }


    protected function beforePrepare(): bool
    {
        $this->joinElementTable(SubscriptionRecord::tableName());
        // select the columns
        $this->query->select([
            'mollie_subscriptions.email',
            'mollie_subscriptions.amount',
            'mollie_subscriptions.formId',
            'mollie_subscriptions.interval',
            'mollie_subscriptions.customerId',
            'mollie_subscriptions.subscriptionId',
            'mollie_subscriptions.subscriptionStatus',
        ]);

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.email', $this->email));
        }

        if ($this->formId) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.formId', $this->formId));
        }

        if ($this->customerId()) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.customerId', $this->customerId));
        }

        return parent::beforePrepare();
    }
}
