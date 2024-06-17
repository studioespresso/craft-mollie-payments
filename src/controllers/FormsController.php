<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\assets\admintable\AdminTableAsset;
use craft\web\Controller;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

class FormsController extends Controller
{
    public function actionIndex()
    {
        Craft::$app->getView()->registerAssetBundle(AdminTableAsset::class);
        return $this->asCpScreen()
            ->crumbs([
                ['label' => Craft::t('mollie-payments', 'Payments'), 'url' => UrlHelper::cpUrl('mollie-payments')],
                ['label' => Craft::t("mollie-payments", 'Forms')],
            ])
            ->title(Craft::t('mollie-payments', 'Forms'))
            ->selectedSubnavItem('forms')
            ->additionalButtonsTemplate('mollie-payments/_forms/_actions')
            ->contentTemplate('mollie-payments/_forms/_index', [
                'forms' => MolliePayments::getInstance()->forms->getAllForms(),
            ]);
    }

    public function actionEdit($formId = null)
    {
        $data = [
            'currencies' => MolliePayments::getInstance()->currency->getCurrencies(),
        ];

        if ($formId) {
            $form = MolliePayments::getInstance()->forms->getFormById($formId);
            $data['form'] = $form;

            if ($form->fieldLayout) {
                $data['layout'] = Craft::$app->getFields()->getLayoutById($form->fieldLayout) ?? null;
            }
        }

        return $this->asCpScreen()
            ->title(isset($form) ? $form->title : Craft::t('mollie-payments', 'New form'))
            ->selectedSubnavItem('forms')
            ->crumbs([
                ['label' => Craft::t('mollie-payments', 'Payments'), 'url' => UrlHelper::cpUrl('mollie-payments')],
                ['label' => Craft::t("mollie-payments", 'Forms'), 'url' => UrlHelper::cpUrl('mollie-payments/forms')],
                ['label' => isset($form) ? $form->title : Craft::t('mollie-payments', 'New form')],
            ])
            ->action('mollie-payments/forms/save')
            ->contentTemplate('mollie-payments/_forms/_edit', $data);
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
        $paymentFormModel->type = $data['type'];
        $paymentFormModel->currency = $data['currency'];
        $paymentFormModel->descriptionFormat = $data['descriptionFormat'];

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

        if($data['type'] === PaymentFormModel::TYPE_PAYMENT) {
            $fieldLayout->type = Payment::class;
        } else {
            $fieldLayout->type = Subscription::class;
        }

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
