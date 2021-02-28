<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\StringHelper;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;
use yii\base\InvalidConfigException;

class Form extends Component
{
    public function save(PaymentFormModel $paymentFormModel)
    {
        $record = false;
        if (isset($paymentFormModel->id)) {
            $record = PaymentFormRecord::findOne([
                'id' => $paymentFormModel->id
            ]);
        }

        if (!$record) {
            $record = new PaymentFormRecord();
            $record->uid = StringHelper::UUID();
        }


        $record->title = $paymentFormModel->title;
        $record->handle = $paymentFormModel->handle;
        $record->currency = $paymentFormModel->currency;
        $record->descriptionFormat = $paymentFormModel->descriptionFormat;
        $record->fieldLayout = $paymentFormModel->fieldLayout;

        if (!$record->validate()) {
            return false;
        }

        $path = "molliePayments.forms.{$record->uid}";
        Craft::$app->projectConfig->set($path, [
            'title' => $record->title,
            'handle' => $record->handle,
            'currency' => $record->currency,
            'descriptionFormat' => $record->descriptionFormat,
            'fieldLayout' => $record->fieldLayout,
        ]);
        return true;
    }

    public function handleAddForm(ConfigEvent $event)
    {
        $record = false;
        $record = PaymentFormRecord::findOne([
            'uid' => $event->tokenMatches[0]
        ]);
        if (!$record) {
            $record = new PaymentFormRecord();
        }

        $record->uid = $event->tokenMatches[0];
        $record->title = $event->newValue['title'];
        $record->handle = $event->newValue['handle'];
        $record->currency = $event->newValue['currency'];
        $record->descriptionFormat = $event->newValue['descriptionFormat'];
        $record->fieldLayout = $event->newValue['fieldLayout'];
        return $record->save();
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
                    'The form parameter now needs a hashed handle instead of a hashed id', __FILE__, 93);
                return $form;
            }
        }
        return $form;
    }

    public function delete($id)
    {
        $paymentFormRecord = PaymentFormRecord::findOne(['id' => $id]);
        if ($paymentFormRecord) {
            Craft::$app->projectConfig->remove("molliePayments.forms.{$paymentFormRecord->uid}");
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
            $data[$form->uid] = [
                'title' => $form->title,
                'handle' => $form->handle,
                'currency' => $form->currency,
                'descriptionFormat' => $form->descriptionFormat,
                'fieldLayout' => $form->fieldLayout,
            ];
        }
        return ['forms' => $data];
    }
}
