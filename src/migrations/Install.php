<?php

namespace studioespresso\molliepayments\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\records\PaymentRecord;
use studioespresso\molliepayments\records\PaymentTransactionRecord;
use studioespresso\navigate\records\NodeRecord;

/***
 * @author    Studio Espresso
 * @package   MolliePayments
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================
    public $driver;


    // Public Methods
    // =========================================================================
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();
        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $tablesCreated = false;
        $tableSchema = Craft::$app->db->schema->getTableSchema(PaymentFormRecord::tableName());
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                PaymentFormRecord::tableName(),
                [
                    'id' => $this->primaryKey(),
                    'title' => $this->string(255)->notNull()->defaultValue(''),
                    'handle' => $this->string(255)->notNull()->defaultValue(''),
                    'fieldLayout' => $this->integer(10),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        $this->createTable(PaymentRecord::tableName(), [
            'id' => $this->integer()->notNull(),
            'email' => $this->string()->notNull(),
            'amount' => $this->integer()->notNull(),
            'formId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createTable(PaymentTransactionRecord::tableName(), [
            'id' => $this->string()->notNull(),
            'payment' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'data' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        return $tablesCreated;
    }

    protected function addForeignKeys()
    {
        // $name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
        $this->addForeignKey(
            $this->db->getForeignKeyName("{{%mollie_payments}}", 'formId'),
            "{{%mollie_payments}}",
            'formId',
            PaymentFormRecord::tableName(),
            'id',
            'CASCADE',
            NULL
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(PaymentTransactionRecord::tableName(), 'payment'),
            PaymentTransactionRecord::tableName(),
            'payment',
            "{{%mollie_payments}}",
            'id',
            'CASCADE',
            NULL
        );

    }

    protected function removeTables()
    {
        $this->dropTableIfExists(PaymentFormRecord::tableName());
        $this->dropTableIfExists(PaymentRecord::tableName());
        $this->dropTableIfExists(PaymentTransactionRecord::tableName());
    }
}
