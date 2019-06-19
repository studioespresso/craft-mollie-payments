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
        $forms = MolliePayments::getInstance()->forms->getAllForms();
        return $this->renderTemplate('mollie-payments/_forms/_index.twig', ['forms' => $forms]);
    }

    public function actionEdit($formId = null)
    {
        if (!$formId) {
            return $this->renderTemplate('mollie-payments/_forms/_edit');
        } else {
            $form = MolliePayments::getInstance()->forms->getFormById($formId);
            $layout = Craft::$app->getFields()->getLayoutById($form->fieldLayout);
            return $this->renderTemplate('mollie-payments/_forms/_edit', ['form' => $form, 'layout' => $layout]);
            
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