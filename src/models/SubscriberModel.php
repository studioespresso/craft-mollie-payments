<?php

namespace studioespresso\molliepayments\models;

use craft\base\Model;

class SubscriberModel extends Model
{
    public $id;

    public $email;

    public $formId;

    public $customerId;

    public $userId;

    public $locale;

    public $metadata;

    public $links;

    public function rules(): array
    {
        return [
            [['email', ], 'required'],
            [['email', 'formId', 'customerId', 'userId', 'locale', 'metadata', 'links'], 'safe'],
        ];
    }
}
