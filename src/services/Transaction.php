<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\events\TransactionUpdateEvent;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentTransactionRecord;

class Transaction extends Component
{
    public function save(PaymentTransactionModel $transactionModel)
    {
        $transactionRecord = new PaymentTransactionRecord();
        $transactionRecord->id = $transactionModel->id;
        $transactionRecord->payment = $transactionModel->payment;
        $transactionRecord->amount = $transactionModel->amount;
        $transactionRecord->redirect = $transactionModel->redirect;
        $transactionRecord->currency = $transactionModel->currency;
        $transactionRecord->status = $transactionModel->status;
        return $transactionRecord->save();
    }

    public function updateTransaction(PaymentTransactionRecord $transaction, \Mollie\Api\Resources\Payment $molliePayment)
    {
        $transaction->status = $molliePayment->status;
        $transaction->method = $molliePayment->method;

        if ($molliePayment->refunds()->count > 0) {
            if ($molliePayment->getAmountRefunded() === $molliePayment->getSettlementAmount()) {
                $transaction->status = "refunded";
            }
        }
        if ($molliePayment->isPaid()) {
            $transaction->paidAt = $molliePayment->paidAt;
        } elseif ($molliePayment->isFailed()) {
            $transaction->failedAt = $molliePayment->failedAt;
        } elseif ($molliePayment->isCancelable) {
            $transaction->canceledAt = $molliePayment->canceledAt;
        } elseif ($molliePayment->isExpired()) {
            $transaction->expiresAt = $molliePayment->expiredAt;
        }

        if ($transaction->validate() && $transaction->save()) {
            if (property_exists($molliePayment->metadata,'elementType') && $molliePayment->metadata->elementType === \studioespresso\molliepayments\elements\Subscription::class) {
                $element = \studioespresso\molliepayments\elements\Subscription::findOne(['id' => $transaction->payment]);
                $element->subscriptionStatus = $transaction->status;
            } else {
                $element = Payment::findOne(['id' => $transaction->payment]);
                $element->paymentStatus = $transaction->status;
                Craft::$app->getElements()->saveElement($element);
            }
            $this->fireEventAfterTransactionUpdate($transaction, $element, $molliePayment->status);
        }
    }

    public function fireEventAfterTransactionUpdate($transaction, $element, $status)
    {
        if (get_class($element) === \studioespresso\molliepayments\elements\Subscription::class) {
            $this->trigger(MolliePayments::EVENT_AFTER_TRANSACTION_UPDATE,
                new TransactionUpdateEvent([
                    'transaction' => $transaction,
                    'element' => $element,
                    'status' => $status,
                ])
            );
        } else {
            $this->trigger(MolliePayments::EVENT_AFTER_TRANSACTION_UPDATE,
                new TransactionUpdateEvent([
                    'transaction' => $transaction,
                    'payment' => $element,
                    'element' => $element,
                    'status' => $status,
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
