<?php

namespace studioespresso\molliepayments\models;

use Craft;
use craft\base\Model;
use craft\db\Query;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\models\AssetTransform;
use studioespresso\seofields\SeoFields;

class PaymentTransactionModel extends Model
{
    public $id;

    public $payment;

    public $status;

    public $data;

}