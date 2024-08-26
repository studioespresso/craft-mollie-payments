<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;
use studioespresso\molliepayments\MolliePayments;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class Export extends Component
{
    private $forms = [];

    public function run($query, $format = 'csv')
    {
        $results = [];
        $header = ['form', 'email', 'amount', 'transaction', 'currency', 'status'];
        $customFields = [];

        foreach ($query as $payment) {
            if (!isset($this->forms[$payment->formId])) {
                $form = MolliePayments::getInstance()->forms->getFormByid($payment->formId);
                $this->forms[$form->id] = $form;
                $customFields = array_merge($customFields, array_keys($payment->getFieldValues()));
            }
        }

        foreach ($query as $payment) {
            /** @var \studioespresso\molliepayments\elements\Payment $payment */
            $transaction = MolliePayments::$plugin->transaction->getTransactionbyPayment($payment->id);

            $results[$payment->id] = array_merge([
                'form' => $this->forms[$payment->formId]->title,
                'email' => $payment->email,
                'amount' => $payment->amount,
                'transaction' => $transaction ? $transaction->id : '',
                'currency' => $this->forms[$payment->formId]->currency,
                'status' => $payment->status,
            ]);
            $values = $payment->getFieldValues();
            foreach ($customFields as $field) {
                if (isset($values[$field])) {
                    $results[$payment->id][$field] = $values[$field];
                } else {
                    $results[$payment->id][$field] = '';
                }
            }
        }
        $header = array_merge($header, $customFields);
        array_unshift($results, $header);
        switch ($format) {
            case 'csv':
                $file = tempnam(sys_get_temp_dir(), 'export');
                $fp = fopen($file, 'wb');
                foreach ($results as $result) {
                    fputcsv($fp, $result, ',');
                }
                fclose($fp);
                $contents = file_get_contents($file);
                unlink($file);
                break;
            default:
                throw new BadRequestHttpException('Invalid export format: ' . $format);
        }

        $filename = mb_strtolower(\studioespresso\molliepayments\elements\Payment::pluralDisplayName()) . '.' . $format;
        $path = Craft::$app->getPath()->getTempPath() . '/' . $filename;
        $mimeType = FileHelper::getMimeTypeByExtension($filename);

        $response = Craft::$app->getResponse();
        $response->content = $contents;
        $response->format = Response::FORMAT_RAW;
        $response->setDownloadHeaders($filename, $mimeType);
        return $response;
    }
}
