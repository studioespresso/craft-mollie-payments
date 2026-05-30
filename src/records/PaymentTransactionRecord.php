<?php

namespace studioespresso\molliepayments\records;

use craft\db\ActiveRecord;
use craft\enums\Color;
use craft\helpers\Cp;
use Mollie\Api\Types\PaymentStatus;

/**
 * @property PaymentStatus $status
 */
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
            PaymentStatus::OPEN, PaymentStatus::PENDING, => Color::Gray,
            PaymentStatus::PAID, PaymentStatus::AUTHORIZED, 'free' => Color::Green,
            PaymentStatus::EXPIRED, PaymentStatus::CANCELED, PaymentStatus::FAILED => Color::Red,
            default => Color::Gray,
        };

        return Cp::statusLabelHtml([
            'color' => $color->value,
            'icon' => null,
            'label' => \Craft::t('mollie-payments', $this->status),
            'indicatorClass' => null,
        ]);
    }
}
