<?php

namespace studioespresso\molliepayments\migrations;

use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentRecord;

/**
 * m260309_000000_addRefundAmountToPayments migration.
 */
class m260309_000000_addRefundAmountToPayments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn(PaymentRecord::tableName(),
            'refundAmount',
            $this->decimal("10,2")->null()->after('amount')
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn(PaymentRecord::tableName(), 'refundAmount');
        return true;
    }
}
