<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\web\Controller;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;

class FormsController extends Controller
{
    public function actionIndex()
    {
        return $this->renderTemplate('mollie-payments/_forms/_index.twig', ['forms' => []]);
    }

    public function actionEdit($formId = null)
    {
        if (!$formId) {
            return $this->renderTemplate('mollie-payments/_forms/_edit');
        }
    }

    public function actionSave()
    {
        $data = Craft::$app->getRequest()->getBodyParam('data');
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = Payment::class;
        Craft::$app->getFields()->saveLayout($fieldLayout);

        if(!isset($data['formId'])) {
            $paymentFormModel = new PaymentFormModel();
        }
        
        
        $paymentFormModel->title = $data['title'];
        $paymentFormModel->handle = $data['handle'];
        $paymentFormModel->fieldLayout = $fieldLayout->id;

        $saved = MolliePayments::getInstance()->forms->save($paymentFormModel);
        dd($saved);

    }
}