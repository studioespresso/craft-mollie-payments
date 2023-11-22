<?php

namespace studioespresso\molliepayments\models;

use craft\base\Model;

class PaymentTransactionModel extends Model
{
    public $id;

    public $payment;

    public $status;

    public $amount;

    public $redirect;

    public $currency;

    public $data;
}
