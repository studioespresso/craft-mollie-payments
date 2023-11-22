<?php

namespace studioespresso\molliepayments\migrations;

use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentFormRecord;

/**
 * m191204_153850_addPaymentDescriptionField migration.
 */
class m191204_153850_addPaymentDescriptionField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(PaymentFormRecord::tableName(),
            'descriptionFormat',
            $this->string(255)->after('currency')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191204_153850_addPaymentDescriptionField cannot be reverted.\n";
        return false;
    }
}
