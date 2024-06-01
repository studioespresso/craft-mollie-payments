<?php

namespace studioespresso\molliepayments\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

/**
 * m240601_145110_addTypeFieldToPaymenyForm migration.
 */
class m240601_145110_addTypeFieldToPaymenyForm extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn(PaymentFormRecord::tableName(),
            'type',
            $this->string(32)->after('handle')
        );

        $forms = PaymentFormRecord::find();
        foreach($forms->all() as $record) {
            $record->setAttribute('type', PaymentFormModel::FORM_TYPE_SINGLE);
            $record->save();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240601_145110_addTypeFieldToPaymenyForm cannot be reverted.\n";
        return false;
    }
}
