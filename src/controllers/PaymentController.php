<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\web\Controller;
use studioespresso\molliepayments\elements\db\PaymentQuery;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\MolliePayments;
use yii\web\HttpException;

class PaymentController extends Controller
{
    protected $allowAnonymous = true;

    public function actionPay()
    {
        
        $amount = Craft::$app->request->getRequiredBodyParam('amount');
        $email = Craft::$app->request->getRequiredBodyParam('email');
        $form = Craft::$app->request->getRequiredBodyParam('form');
        $amount = Craft::$app->security->validateData($amount);

        if ($amount == false) {
            throw new HttpException(400);
        }

        $payment = new Payment();

        $payment->amount = $amount;
        $payment->email = $email;
        $payment->formId = $form;
        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($form);
        $payment->fieldLayoutId = $paymentForm->fieldLayout;
        $payment->setFieldValuesFromRequest('fields');
        Craft::$app->getElements()->saveElement($payment);

    }

    public function actionEdit($uid)
    {
        $query = Payment::find();
        $query->uid = $uid;

        $this->renderTemplate('mollie-payments/_payment/_edit', ['element' => $query->one()]);
    }
}