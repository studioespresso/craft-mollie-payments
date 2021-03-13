<?php

namespace studioespresso\molliepayments\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

/**
 * m210313_101912_addFormsToProjectConfig migration.
 */
class m210313_101912_addFormsToProjectConfig extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {

        $forms = PaymentFormRecord::find();
        $data = [];
        foreach ($forms->all() as $form) {
            $model = new PaymentFormModel();
            $model->setAttributes($form->getAttributes());
            $fieldLayout = Craft::$app->getFields()->getLayoutById($form->fieldLayout);
            $fieldLayout->type = Payment::class;
            $model->setFieldLayout($fieldLayout);
            $data[$form->uid] = $model->getConfig();
        }
        Craft::$app->projectConfig->set('molliePayments', $data);
        } catch(\Exception $e) {
            Craft::error($e->getMessage(), __CLASS__);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210313_101912_addFormsToProjectConfig cannot be reverted.\n";
        return false;
    }
}
