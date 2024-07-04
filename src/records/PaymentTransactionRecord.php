<?php

namespace studioespresso\molliepayments\records;

use craft\db\ActiveRecord;
use craft\enums\Color;
use craft\helpers\Cp;
use Mollie\Api\Types\PaymentStatus;

class PaymentTransactionRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%mollie_transactions}}';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public function getStatusComponent()
    {
        $color = match ($this->status) {
            PaymentStatus::STATUS_OPEN, PaymentStatus::STATUS_PENDING, => Color::Gray,
            PaymentStatus::STATUS_PAID, PaymentStatus::STATUS_AUTHORIZED, 'free' => Color::Green,
            PaymentStatus::STATUS_EXPIRED, PaymentStatus::STATUS_CANCELED, PaymentStatus::STATUS_FAILED => Color::Red,
            default => Color::Gray,
        };

        return Cp::statusLabelHtml([
            'color' => $color->value,
            'icon' => null,
            'label' => $this->status,
            'indicatorClass' => null,
        ]);
    }
}
