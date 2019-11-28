<?php

namespace studioespresso\molliepayments\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentRecord;
use studioespresso\molliepayments\records\PaymentTransactionRecord;

/**
 * m191128_202559_changeAmountType migration.
 */
class m191128_202559_changeAmountType extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn(
            PaymentRecord::tableName(),
            'amount',
            $this->decimal("10,2")
        );

        $this->alterColumn(
            PaymentTransactionRecord::tableName(),
            'amount',
            $this->decimal("10,2")
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191128_202559_changeAmountType cannot be reverted.\n";
        return false;
    }
}
