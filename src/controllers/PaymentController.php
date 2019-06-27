<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use studioespresso\molliepayments\elements\db\PaymentQuery;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\MolliePayments;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class PaymentController extends Controller
{
    protected $allowAnonymous = ['pay', 'redirect', 'webhook'];

    public function beforeAction($action)
    {
        if($action->id === 'webhook') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }


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
        $payment->paymentStatus = 'pending';
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
        try {
            $molliePayment = MolliePayments::getInstance()->mollie->getStatus($transaction->id);
            $this->redirect(UrlHelper::url($redirect, ['payment' => $uid, 'status' => $molliePayment->status]));
        } catch(\Exception $e) {
            throw new NotFoundHttpException('Payment not found', '404');
        }

    }

    public function actionWebhook()
    {
        $id = Craft::$app->getRequest()->getRequiredParam('id');
        $transaction = MolliePayments::getInstance()->transaction->getTransactionbyId($id);
        $molliePayment = MolliePayments::getInstance()->mollie->getStatus($id);
        MolliePayments::getInstance()->transaction->updateTransaction($transaction, $molliePayment);
        return;
    }


}