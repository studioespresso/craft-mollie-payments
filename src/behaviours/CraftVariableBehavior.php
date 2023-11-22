<?php

namespace studioespresso\molliepayments\behaviours;

use Craft;
use studioespresso\molliepayments\elements\db\PaymentQuery;
use studioespresso\molliepayments\elements\Payment;
use yii\base\Behavior;

/**
 * Adds a `craft.products()` function to the templates (like `craft.entries()`)
 */
class CraftVariableBehavior extends Behavior
{
    public function payments($criteria = null): PaymentQuery
    {
        $query = Payment::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
}
