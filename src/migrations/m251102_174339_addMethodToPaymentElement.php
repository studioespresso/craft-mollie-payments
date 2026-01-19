<?php

namespace studioespresso\molliepayments\migrations;

use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentRecord;

/**
 * m251102_174339_addMethodToPaymentElement migration.
 */
class m251102_174339_addMethodToPaymentElement extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn(PaymentRecord::tableName(),
            'method',
            $this->string(255)->null()->after('amount')
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m251102_174339_addMethodToPaymentElement cannot be reverted.\n";
        return false;
    }
}
