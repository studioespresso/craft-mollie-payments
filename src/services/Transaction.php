<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\events\TransactionUpdateEvent;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\records\PaymentTransactionRecord;

class Transaction extends Component
{

    public function save(PaymentTransactionModel $transactionModel)
    {
        $transactionRecord = new PaymentTransactionRecord();
        $transactionRecord->id = $transactionModel->id;
        $transactionRecord->payment = $transactionModel->payment;
        $transactionRecord->amount = $transactionModel->amount;
        $transactionRecord->currency = $transactionModel->currency;
        $transactionRecord->status = $transactionModel->status;
        return $transactionRecord->save();
    }

    public function updateTransaction(PaymentTransactionRecord $transaction, $molliePayment)
    {
        $transaction->status = $molliePayment->status;
        $transaction->method = $molliePayment->method;
        if ($molliePayment->status == 'paid') {
            $transaction->paidAt = $molliePayment->paidAt;
        } elseif ($molliePayment->status == 'failed') {
            $transaction->failedAt = $molliePayment->failedAt;
        } elseif ($molliePayment->status == 'canceled') {
            $transaction->canceledAt = $molliePayment->canceledAt;
        } elseif ($molliePayment->status == 'expired') {
            $transaction->expiresAt = $molliePayment->expiresAt;
        }

        if ($transaction->validate() && $transaction->save()) {

            $payment = Payment::findOne(['id' => $transaction->payment]);
            $payment->paymentStatus = $molliePayment->status;

            Craft::$app->getElements()->saveElement($payment);
            $this->trigger(MolliePayments::EVENT_AFTER_TRANSACTION_UPDATE,
                new TransactionUpdateEvent([
                    'transaction' => $transaction,
                    'payment' => $payment,
                    'status' => $molliePayment->status
                ])
            );
        }
    }

    public function getStatusForPayment($id)
    {
        $transaction = PaymentTransactionRecord::findOne(['payment' => $id]);
        if ($transaction) {
            return $transaction->status;
        } else {
            return false;
        }
    }

    public function getTransactionbyId($id)
    {
        $transactionRecord = PaymentTransactionRecord::findOne(['id' => $id]);
        return $transactionRecord;
    }

    public function getAllByPayment($id)
    {
        $transactionRecord = PaymentTransactionRecord::findAll(['payment' => $id]);
        return $transactionRecord;
    }

    public function getTransactionbyPayment($id)
    {
        $transactionRecord = PaymentTransactionRecord::findOne(['payment' => $id]);
        return $transactionRecord;
    }

}
