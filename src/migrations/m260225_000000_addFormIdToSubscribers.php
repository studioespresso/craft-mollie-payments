<?php

namespace studioespresso\molliepayments\migrations;

use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\records\SubscriberRecord;

/**
 * m260225_000000_addFormIdToSubscribers migration.
 */
class m260225_000000_addFormIdToSubscribers extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn(SubscriberRecord::tableName(),
            'formId',
            $this->integer()->null()->after('email')
        );

        $this->createIndex(
            'idx_mollie_subscribers_email_formId',
            SubscriberRecord::tableName(),
            ['email', 'formId']
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(SubscriberRecord::tableName(), 'formId'),
            SubscriberRecord::tableName(),
            'formId',
            PaymentFormRecord::tableName(),
            'id',
            'SET NULL',
            null
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m260225_000000_addFormIdToSubscribers cannot be reverted.\n";
        return false;
    }
}
