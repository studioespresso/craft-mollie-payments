<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use studioespresso\molliepayments\elements\db\PaymentQuery;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\MolliePayments;
use yii\web\HttpException;

class PaymentController extends Controller
{
    protected $allowAnonymous = ['pay', 'redirect'];

    public function actionPay()
    {

        $amount = Craft::$app->request->getRequiredBodyParam('amount');
        $email = Craft::$app->request->getRequiredBodyParam('email');
        $form = Craft::$app->request->getRequiredBodyParam('form');
        $redirect = Craft::$app->request->getBodyParam('redirect');
        $amount = Craft::$app->security->validateData($amount);
        $redirect = Craft::$app->security->validateData($redirect);

        if ($amount == false) {
            throw new HttpException(400);
        }

        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($form);
        if (!$paymentForm) {
            throw new HttpException(404);
        }

        $payment = new Payment();

        $payment->email = $email;
        $payment->amount = $amount;
        $payment->formId = $form;
        $payment->fieldLayoutId = $paymentForm->fieldLayout;
        $payment->setFieldValuesFromRequest('fields');
        Craft::$app->getElements()->saveElement($payment);

        $url = MolliePayments::getInstance()->mollie->generatePayment($payment, UrlHelper::url($redirect));
        $this->redirect($url);

    }

    public function actionEdit($uid)
    {
        $query = Payment::find();
        $query->uid = $uid;

        $this->renderTemplate('mollie-payments/_payment/_edit', ['element' => $query->one()]);
    }

    public function actionRedirect()
    {
        $uid = Craft::$app->getRequest()->getRequiredParam('order_id');
        $redirect = Craft::$app->getRequest()->getParam('redirect');
        $payment = Payment::findOne(['uid' => $uid]);
        $transaction = MolliePayments::getInstance()->transaction->getTransactionbyPayment($payment->id);
        $molliePayment = MolliePayments::getInstance()->mollie->getStatus($transaction->id);

        $this->redirect(UrlHelper::url($redirect, ['payment' => $uid, 'status' => $molliePayment->status]));
    }
}