<?php

namespace studioespresso\molliepayments\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentRecord;

/**
 * m260309_182414_addRedunfAmount migration.
 */
class m260309_182414_addRedunfAmount extends Migration
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
