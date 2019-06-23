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
        
        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($form);
        if ($paymentForm) {
            throw new HttpException(404);
        }

        $payment = new Payment();

        $payment->email = $email;
        $payment->amount = $amount;
        $payment->formId = $form;
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