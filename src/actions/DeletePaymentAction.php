<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace studioespresso\molliepayments\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use yii\base\Exception;

class DeletePaymentAction extends ElementAction
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Delete');
    }

    public static function isDestructive(): bool
    {
        return true;
    }

    public function getConfirmationMessage()
    {
        return "Are you sure you want to remove these items?";
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $elementsService = Craft::$app->getElements();

        try {
            foreach ($query->all() as $project) {
                $elementsService->deleteElement($project);
            }
        } catch (Exception $exception) {
            $this->setMessage($exception->getMessage());

            return false;
        }

        $this->setMessage(Craft::t('app', 'Payments deleted.'));

        return true;
    }




}
