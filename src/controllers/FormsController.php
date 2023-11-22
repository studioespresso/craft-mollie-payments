<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\web\Controller;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

class FormsController extends Controller
{
    public function actionIndex()
    {
        $forms = MolliePayments::getInstance()->forms->getAllForms();
        return $this->renderTemplate('mollie-payments/_forms/_index.twig', ['forms' => $forms]);
    }

    public function actionEdit($formId = null)
    {
        $currencies = MolliePayments::getInstance()->currency->getCurrencies();
        if (!$formId) {
            return $this->renderTemplate('mollie-payments/_forms/_edit', ['currencies' => $currencies]);
        } else {
            $form = MolliePayments::getInstance()->forms->getFormById($formId);
            if ($form->fieldLayout) {
                $layout = Craft::$app->getFields()->getLayoutById($form->fieldLayout);
            }
            return $this->renderTemplate('mollie-payments/_forms/_edit', ['form' => $form, 'layout' => $layout ?? null, 'currencies' => $currencies]);
        }
    }

    public function actionSave()
    {
        $data = Craft::$app->getRequest()->getBodyParam('data');

        if (!isset($data['id']) or empty($data['id'])) {
            $paymentFormModel = new PaymentFormModel();
        } else {
            /** @var PaymentFormRecord $record */
            $record = MolliePayments::getInstance()->forms->getFormById($data['id']);
            $paymentFormModel = new PaymentFormModel();
            $paymentFormModel->setAttributes($record->getAttributes());
            $paymentFormModel->id = $record->id;
            $paymentFormModel->uid = $record->uid;
        }

        $paymentFormModel->title = $data['title'];
        $paymentFormModel->handle = $data['handle'];
        $paymentFormModel->currency = $data['currency'];
        $paymentFormModel->descriptionFormat = $data['descriptionFormat'];

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = Payment::class;
        $paymentFormModel->setFieldLayout($fieldLayout);

        if ($paymentFormModel->validate()) {
            $saved = MolliePayments::getInstance()->forms->save($paymentFormModel);
            $this->redirectToPostedUrl();
        } else {
            $layout = Craft::$app->getFields()->getLayoutById($fieldLayout->id);
            return $this->renderTemplate('mollie-payments/_forms/_edit',  [
                'form' => $paymentFormModel,
                'layout' => $layout,
                'errors' => $paymentFormModel->getErrors(),
                'currencies' => MolliePayments::getInstance()->currency->getCurrencies(),
            ]);
        }
    }

    public function actionDelete()
    {
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');
        if (MolliePayments::getInstance()->forms->delete($id)) {
            $returnData['success'] = true;
            return $this->asJson($returnData);
        };
    }
}
