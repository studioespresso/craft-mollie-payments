<?php
/**
 * Mollie Payments plugin for Craft CMS 3.x
 *
 * Easily accept payment with Mollie Payments
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2019 Studio Espresso
 */

namespace studioespresso\molliepayments\events;

use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use yii\base\Event;

/**
 * @author    Studio Espresso
 * @package   MolliePayments
 * @since     1.0.0
 */
class SubscriptionCreatedEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var PaymentTransactionModel the transaction associated being updates.
     */
    public $transaction;

    /**
     * @var Subscription the payment element associated with the transations.
     */
    public $element;

    /**
     * @string the updated status of the transaction
     */
    public $status;
}
