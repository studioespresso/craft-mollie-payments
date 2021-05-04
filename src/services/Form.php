<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;
use yii\base\InvalidConfigException;

class Form extends Component
{

    const CONFIG_FORMS_PATH = 'molliePayments';

    public function save(PaymentFormModel $form)
    {
        $isNewForm = !$form->id;
        if ($isNewForm) {
            $form->uid = StringHelper::UUID();
        }

        $configPath = self::CONFIG_FORMS_PATH . '.' . $form->uid;
        $configData = $form->getConfig();
        Craft::$app->projectConfig->set($configPath, $configData);
        return true;
    }

    public function handleAddForm(ConfigEvent $event)
    {
        $formUid = $event->tokenMatches[0];
        $data = $event->newValue;

        $formRecord = $this->getFormRecord($formUid);
        if (!$formRecord) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        $formRecord->uid = $formUid;
        $formRecord->title = $data['title'];
        $formRecord->handle = $data['handle'];
        $formRecord->currency = $data['currency'];
        $formRecord->descriptionFormat = $data['descriptionFormat'];


        if (!empty($data['fieldLayouts'])) {
            // Save the field layout
            $layout = FieldLayout::createFromConfig(reset($data['fieldLayouts']));
            $layout->id = $formRecord->fieldLayout;
            $layout->type = \studioespresso\molliepayments\elements\Payment::class;
            $layout->uid = key($data['fieldLayouts']);
            Craft::$app->getFields()->saveLayout($layout);
            $formRecord->fieldLayout = $layout->id;
        } else if ($formRecord->fieldLayout) {
            // Delete the field layout
            Craft::$app->getFields()->deleteLayoutById($formRecord->fieldLayout);
            $formRecord->fieldLayout = null;
        }


        $formRecord->save();
        $transaction->commit();

    }

    public function getAllForms()
    {
        $forms = PaymentFormRecord::find()->all();
        return $forms;
    }

    public function getFormByid($id)
    {
        $form = PaymentFormRecord::findOne(['id' => $id]);
        if (!$form) {
            throw new InvalidConfigException("Form not found");
        };
        return $form;
    }

    public function validateFormHandle($handle)
    {
        $form = PaymentFormRecord::findOne(['handle' => $handle]);
        return $form;
    }

    public function getFormByHandle($handle)
    {
        $form = PaymentFormRecord::findOne(['handle' => $handle]);
        if (!$form) {
            $form = $this->getFormByid($handle);
            if ($form) {
                Craft::$app->deprecator->log('molliePayments.forms.handle',
                    'The form parameter now needs to be a hashed handle instead of a hashed id', __FILE__, 93);
                return $form;
            }
        }
        return $form;
    }

    public function delete($id)
    {
        $paymentFormRecord = PaymentFormRecord::findOne(['id' => $id]);
        if ($paymentFormRecord) {
            Craft::$app->projectConfig->remove(self::CONFIG_FORMS_PATH . '.' . $paymentFormRecord->uid, "Removing form '{$paymentFormRecord->formName()}'");
        }
        return true;
    }

    public function handleDeleteForm(ConfigEvent $event)
    {
        $record = PaymentFormRecord::findOne([
            'uid' => $event->tokenMatches[0]
        ]);
        if (!$record) {
            return false;
        }

        if ($record->delete()) {
            return 1;
        };
    }

    public function rebuildProjectConfig()
    {
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
        return $data;
    }

    private function getFormRecord(string $uid)
    {
        $query = PaymentFormRecord::find();
        $query->andWhere(['uid' => $uid]);
        return $query->one() ?? new PaymentFormRecord();
    }
}
