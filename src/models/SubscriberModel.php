<?php

namespace studioespresso\molliepayments\models;

use craft\base\Model;

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
