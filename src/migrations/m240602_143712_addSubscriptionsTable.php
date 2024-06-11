<?php

namespace studioespresso\molliepayments\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliepayments\records\SubscriptionRecord;

/**
 * m240602_143712_addSubscriptionsTable migration.
 */
class m240602_143712_addSubscriptionsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable(
            SubscriptionRecord::tableName(),
            [
                'id' => $this->integer()->notNull(),
                'email' => $this->string()->notNull(),
                'formId' => $this->integer()->notNull(),
                'interval' => $this->string()->notNull(),
                'subscriptionStatus' => $this->string()->notNull(),
                'customerId' => $this->string(),
                'subscriptionId' => $this->string(),
                'amount' => $this->decimal("10,2")->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240602_143712_addSubscriptionsTable cannot be reverted.\n";
        return false;
    }
}
