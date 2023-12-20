<?php

namespace studioespresso\molliepayments\migrations;

use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;
use studioespresso\molliepayments\MolliePayments;

/**
 * m231220_114740_craft5migration migration.
 */
class m231220_114740_craft5migration extends BaseContentRefactorMigration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        foreach (MolliePayments::getInstance()->forms->getAllForms() as $form) {
            $layout = \Craft::$app->getFields()->getLayoutById($form->fieldLayout);
            $this->updateElements(
                (new Query())
                    ->from('{{%mollie_payments}}')
                    ->where(['formId' => $form->id]),
                $layout,
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m231220_114740_craft5migration cannot be reverted.\n";
        return false;
    }
}
