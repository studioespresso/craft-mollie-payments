<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\records\PaymentTransactionRecord;

class TransactionService extends Component
{

    public function save(PaymentTransactionModel $transactionModel)
    {
        $transactionRecord = new PaymentTransactionRecord();
        $transactionRecord->id = $transactionModel->id;
        $transactionRecord->payment = $transactionModel->payment;
        $transactionRecord->status = $transactionModel->status;
        return $transactionRecord->save();
    }

    public function getTransactionbyId($id)
    {
        $transactionRecord = PaymentTransactionRecord::findOne(['id' => $id]);
        return $transactionRecord;
    }

    public function getTransactionbyPayment($id)
    {
        $transactionRecord = PaymentTransactionRecord::findOne(['payment' => $id]);
        return $transactionRecord;
    }

}
