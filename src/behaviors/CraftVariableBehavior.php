<?php

namespace studioespresso\molliepayments\behaviors;

use craft\elements\db\EntryQuery;
use studioespresso\molliepayments\elements\db\PaymentQuery;
use studioespresso\molliepayments\elements\Payment;
use yii\base\Behavior;

/**
 * Class EntryQueryBehavior
 *
 * @property EntryQuery $owner
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
