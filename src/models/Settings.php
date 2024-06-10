<?php

namespace studioespresso\molliepayments\models;

use craft\base\Model;

class Settings extends Model
{
    public string $apiKey = '';

    public string $manageSubscriptionEmailPath = '';

    public string $manageSubscriptionRoute = '';
}
