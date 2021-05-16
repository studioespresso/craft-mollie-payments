<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\base\Element;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\MolliePayments;
use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class PaymentController extends Controller
{
    protected $allowAnonymous = ['pay', 'donate', 'redirect', 'webhook'];

    public function beforeAction($action)
    {
        if ($action->id === 'webhook') {
            $this->enableCsrfValidation = false;
        }

        if (!ConfigHelper::localizedValue(MolliePayments::$plugin->getSettings()->apiKey)) {
            throw new InvalidConfigException("No Mollie API key set");
        }
        return parent::beforeAction($action);
    }


    /**
     * @return \yii\web\Response
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     * @since 1.0.0
     */
    public function actionPay()
    {

        $redirect = Craft::$app->request->getBodyParam('redirect');
        $redirect = Craft::$app->security->validateData($redirect);

        if (Craft::$app->getRequest()->getBodyParam('payment') && Craft::$app->getRequest()->getValidatedBodyParam('payment')) {
            $payment = Payment::findOne(['uid' => Craft::$app->getRequest()->getValidatedBodyParam('payment')]);
            if (!$payment) {
                throw new NotFoundHttpException("Payment not found", 404);
            }
            if (Craft::$app->getRequest()->getBodyParam('email')) {
                $payment->email = Craft::$app->getRequest()->getBodyParam('email');
            }
        } else {
            $email = Craft::$app->request->getRequiredBodyParam('email');
            $amount = Craft::$app->request->getValidatedBodyParam('amount');
            $form = Craft::$app->request->getValidatedBodyParam('form');
            if ($amount === false || $form === false) {
                throw new HttpException(400, "Incorrent payment submitted");
            }

            $paymentForm = MolliePayments::getInstance()->forms->getFormByHandle($form);
            $payment = new Payment();

            if (!$paymentForm) {
                throw new NotFoundHttpException("Form not found", 404);
            }

            $payment->email = $email;
            $payment->amount = $amount;
            $payment->formId = $paymentForm->id;
            $payment->fieldLayoutId = $paymentForm->fieldLayout;
        }

        $payment->paymentStatus = 'pending';

        $fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');
        $payment->setFieldValuesFromRequest($fieldsLocation);

        $payment->setScenario(Element::SCENARIO_LIVE);
        if (!$payment->validate()) {
            // Send the payment back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'payment' => $payment,
            ]);
            return null;
        }

        if (MolliePayments::getInstance()->payment->save($payment)) {
            if ($payment->amount === "0") {
                $url = MolliePayments::getInstance()->payment->handleFreePayment($payment, $paymentForm, UrlHelper::url($redirect));
                return $this->redirect($url);
            }
            $url = MolliePayments::getInstance()->mollie->generatePayment($payment, UrlHelper::url($redirect));
            $this->redirect($url);
        };

    }

    /**
     * @return \yii\web\Response
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     * @since 1.5.0
     */
    public function actionDonate()
    {
        $redirect = Craft::$app->request->getBodyParam('redirect');
        $redirect = Craft::$app->security->validateData($redirect);

        $form = Craft::$app->request->getValidatedBodyParam('form');
        $paymentForm = MolliePayments::getInstance()->forms->getFormByHandle($form);
        if (!$paymentForm) {
            throw new NotFoundHttpException("Form not found", 404);
        }

        if (Craft::$app->getRequest()->getBodyParam('payment') && Craft::$app->getRequest()->getValidatedBodyParam('payment')) {
            $payment = Payment::findOne(['uid' => Craft::$app->getRequest()->getValidatedBodyParam('payment')]);
            if (!$payment) {
                throw new NotFoundHttpException("Payment not found", 404);
            }
            if (Craft::$app->getRequest()->getBodyParam('email')) {
                $payment->email = Craft::$app->getRequest()->getBodyParam('email');
            }
        } else {
            $email = Craft::$app->request->getRequiredBodyParam('email');
            $amount = Craft::$app->request->getRequiredBodyParam('amount');

            if ($amount === false) {
                throw new HttpException(400);
            }

            $payment = new Payment();

            $payment->email = $email;
            $payment->amount = $amount;
            $payment->formId = $paymentForm->id;
            $payment->fieldLayoutId = $paymentForm->fieldLayout;

        }

        $payment->paymentStatus = 'pending';
        $payment->setFieldValuesFromRequest('fields');

        if (MolliePayments::getInstance()->payment->save($payment)) {
            if ($payment->amount == "0") {
                $url = MolliePayments::getInstance()->payment->handleFreePayment($payment, $paymentForm, UrlHelper::url($redirect));
                return $this->redirect($url);
            }
            $url = MolliePayments::getInstance()->mollie->generatePayment($payment, UrlHelper::url($redirect));
        };
        $this->redirect($url);

    }

    /**
     * @param $uid
     * @since 1.0.0
     */
    public function actionEdit($uid)
    {
        $query = Payment::find();
        $query->uid = $uid;
        $payment = $query->one();

        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($payment->formId);
        $transactions = MolliePayments::getInstance()->transaction->getAllByPayment($payment->id);

        $this->renderTemplate('mollie-payments/_payment/_edit', ['element' => $payment, 'transactions' => $transactions, 'form' => $paymentForm]);
    }

    public function actionRefund()
    {
            dd('hier');
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

    /**
     * @throws \yii\web\BadRequestHttpException
     * @since 1.0.0
     */
    public function actionWebhook()
    {
        $id = Craft::$app->getRequest()->getRequiredParam('id');
        $transaction = MolliePayments::getInstance()->transaction->getTransactionbyId($id);
        $molliePayment = MolliePayments::getInstance()->mollie->getStatus($id);
        MolliePayments::getInstance()->transaction->updateTransaction($transaction, $molliePayment);
        return;
    }

    /**
     * @return \craft\web\Response|\yii\console\Response
     * @throws \yii\web\BadRequestHttpException
     * @since 1.0.0
     */
    public function actionExport()
    {
        $ids = Craft::$app->request->post('ids');
        $payments = Payment::findAll(['id' => explode(',', $ids)]);
        return MolliePayments::getInstance()->export->run($payments);
    }

    /**
     * @return \craft\web\Response|\yii\console\Response
     * @throws \yii\web\BadRequestHttpException
     * @since 1.0.0
     */
    public function actionExportAll()
    {
        $payments = Payment::findAll();
        return MolliePayments::getInstance()->export->run($payments);
    }


}
