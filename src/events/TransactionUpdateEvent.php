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


use Craft;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use yii\base\Event;


/**
 * @author    Studio Espresso
 * @package   MolliePayments
 * @since     1.0.0
 */
class TransactionUpdateEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var PaymentTransactionModel the transaction associated being updates.
     */
    public $transaction;

    /**
     * @var Payment the payment element associated with the transations.
     */
    public $payment;

    /**
     * @string the updated status of the transaction
     */
    public $status;
}