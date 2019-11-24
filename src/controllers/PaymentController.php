<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\MolliePayments;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class PaymentController extends Controller
{
    protected $allowAnonymous = ['pay', 'redirect', 'webhook'];

    public function beforeAction($action)
    {
        if ($action->id === 'webhook') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }


    public function actionPay()
    {
        $email = Craft::$app->request->getRequiredBodyParam('email');

        $amount = Craft::$app->request->getRequiredBodyParam('amount');
        $form = Craft::$app->request->getRequiredBodyParam('form');
        $redirect = Craft::$app->request->getBodyParam('redirect');


        $amount = Craft::$app->security->validateData($amount);
        $form = Craft::$app->security->validateData($form);
        $redirect = Craft::$app->security->validateData($redirect);

        if ($amount == false) {
            throw new HttpException(400);
        }

        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($form);

        $payment = new Payment();

        $payment->email = $email;
        $payment->amount = $amount;
        $payment->formId = $form;
        $payment->paymentStatus = 'pending';
        $payment->fieldLayoutId = $paymentForm->fieldLayout;
        $payment->setFieldValuesFromRequest('fields');

        if (!$paymentForm) {
            throw new NotFoundHttpException("Form not found", 404);
        }

        MolliePayments::getInstance()->payment->save($payment);

        $url = MolliePayments::getInstance()->mollie->generatePayment($payment, UrlHelper::url($redirect));
        $this->redirect($url);

    }

    public function actionEdit($uid)
    {
        $query = Payment::find();
        $query->uid = $uid;
        $payment = $query->one();

        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($payment->formId);
        $transactions = MolliePayments::getInstance()->transaction->getAllByPayment($payment->id);

        $this->renderTemplate('mollie-payments/_payment/_edit', ['element' => $payment, 'transactions' => $transactions, 'form' => $paymentForm]);
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
        } catch (\Exception $e) {
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

    public function actionExport()
    {
        $ids = Craft::$app->request->post('ids');
        $payments = Payment::findAll(['id' => explode(',', $ids)]);
        return MolliePayments::getInstance()->export->run($payments);
    }

}