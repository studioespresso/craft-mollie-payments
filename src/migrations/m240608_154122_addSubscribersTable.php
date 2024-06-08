<?php

namespace studioespresso\molliepayments\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliepayments\records\SubscriberRecord;

/**
 * m240608_154122_addSubscribersTable migration.
 */
class m240608_154122_addSubscribersTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable(
            SubscriberRecord::tableName(),
            [
                'id' => $this->primaryKey(),
                'email' => $this->string()->notNull(),
                'customerId' => $this->string(30),
                'userId' => $this->integer(),
                'locale' => $this->string(5),
                'metadata' => $this->text(),
                'links' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240608_154122_addSubscribersTable cannot be reverted.\n";
        return false;
    }
}
