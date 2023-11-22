<?php

namespace studioespresso\molliepayments\migrations;

use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentTransactionRecord;

/**
 * m210608_181336_addRedirectToTransationTable migration.
 */
class m210608_181336_addRedirectToTransationTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(PaymentTransactionRecord::tableName(),
            'redirect',
            $this->string(255)->after('status')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210608_181336_addRedirectToTransationTable cannot be reverted.\n";
        return false;
    }
}
