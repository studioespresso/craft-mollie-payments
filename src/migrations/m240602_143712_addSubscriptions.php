<?php

namespace studioespresso\molliepayments\migrations;

use craft\db\Migration;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\records\PaymentTransactionRecord;
use studioespresso\molliepayments\records\SubscriberRecord;
use studioespresso\molliepayments\records\SubscriptionRecord;

/**
 * m240602_143712_addSubscriptionsTable migration.
 */
class m240602_143712_addSubscriptions extends Migration
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
                'times' => $this->string()->defaultValue(null),
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

        $this->addColumn(PaymentFormRecord::tableName(),
            'type',
            $this->string(32)->after('handle')
        );

        $this->alterColumn(PaymentTransactionRecord::tableName(), 'redirect', $this->string(255));

        $forms = PaymentFormRecord::find();
        foreach ($forms->all() as $record) {
            $record->setAttribute('type', PaymentFormModel::TYPE_PAYMENT);
            $record->save();
        }


        $this->dropForeignKeyIfExists(PaymentTransactionRecord::tableName(), 'payment');
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
