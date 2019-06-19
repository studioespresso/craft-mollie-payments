<?php

namespace studioespresso\molliepayments\models;

use Craft;
use craft\base\Model;
use craft\db\Query;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\models\AssetTransform;
use studioespresso\seofields\SeoFields;

class PaymentFormModel extends Model
{
    public $title;

    public $id;

    public $currency;

    public $fieldLayout;

}