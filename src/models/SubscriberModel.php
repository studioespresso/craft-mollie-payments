<?php

namespace studioespresso\molliepayments\models;

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\db\Table;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\validators\HandleValidator;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\MolliePayments;

class SubscriberModel extends Model
{

    public $id;

    public $email;

    public $customerId;

    public $locale;

    public $metadata;

    public $links;

    public function rules(): array
    {
        return [
            [['email', ], 'required'],
            [['email', 'customerId', 'locale', 'metadata', 'links'], 'safe'],
        ];
    }
}
