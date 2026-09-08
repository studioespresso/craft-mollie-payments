<?php

namespace studioespresso\molliepayments\console\controllers;

use craft\console\Controller;
use craft\helpers\Console;
use craft\helpers\Json;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentTransactionRecord;
use yii\console\ExitCode;

/**
 * Maintenance commands for transactions.
 */
class TransactionsController extends Controller
{
    /**
     * @var bool Whether to only report what would change, without writing anything.
     */
    public bool $dryRun = false;

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        if ($actionID === 'backfill-paid-at') {
            $options[] = 'dryRun';
        }
        return $options;
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'd' => 'dryRun',
        ]);
    }

    /**
     * Backfills paidAt/method on transactions that are marked status=paid but have no paidAt.
     *
     * This happens when actionWebhook() creates a transaction for a subscription's recurring
     * charge it has no local record for yet: that path only ever wrote id/payment/amount/
     * currency/status, never paidAt/method, because it never called updateTransaction(). Once
     * that root cause is fixed, this recovers any transactions already stuck in that state.
     *
     * Transactions belonging to a payment element are handled the same way, so any other
     * transaction that ended up without a paidAt is picked up as well.
     *
     * Usage:
     *   ./craft mollie-payments/transactions/backfill-paid-at              # backfill
     *   ./craft mollie-payments/transactions/backfill-paid-at --dry-run    # list only, no writes
     *
     * @return int
     */
    public function actionBackfillPaidAt(): int
    {
        $rows = PaymentTransactionRecord::find()
            ->where(['status' => 'paid'])
            ->andWhere(['paidAt' => null])
            ->all();

        $this->stdout('Found ' . count($rows) . " paid transaction(s) with a missing paidAt.\n\n");

        if ($this->dryRun) {
            $this->stdout("*** DRY RUN — no writes will be made ***\n\n", Console::FG_YELLOW);
        }

        $stats = ['fixed' => 0, 'stillUnpaid' => 0, 'failed' => 0];

        foreach ($rows as $row) {
            $element = Subscription::findOne(['id' => $row->payment]) ?? Payment::findOne(['id' => $row->payment]);
            if (!$element) {
                $this->stderr("  x transaction {$row->id}: no subscription or payment element #{$row->payment} found — skipping.\n", Console::FG_RED);
                $stats['failed']++;
                continue;
            }

            $label = $element instanceof Subscription ? 'subscription' : 'payment';

            try {
                $form = $element->getForm();
                $molliePayment = MolliePayments::getInstance()->mollie->getStatus($row->id, $form->handle);
            } catch (\Throwable $e) {
                $this->stderr("  x transaction {$row->id} ({$label} #{$element->id}, {$element->email}): could not fetch from Mollie — {$e->getMessage()}\n", Console::FG_RED);
                $stats['failed']++;
                continue;
            }

            if (!$molliePayment->isPaid()) {
                $this->stdout("  ! transaction {$row->id} ({$label} #{$element->id}, {$element->email}): Mollie now reports status={$molliePayment->status}, not paid — skipping.\n", Console::FG_YELLOW);
                $stats['stillUnpaid']++;
                continue;
            }

            if ($this->dryRun) {
                $this->stdout("  [dry-run] transaction {$row->id} ({$label} #{$element->id}, {$element->email}): would set paidAt={$molliePayment->paidAt} method={$molliePayment->method}\n");
                $stats['fixed']++;
                continue;
            }

            // Saving through the record runs the values through Db::prepareValueForDb(), which is
            // what turns Mollie's ISO-8601 timestamp into the UTC datetime the column expects.
            $row->paidAt = $molliePayment->paidAt;
            $row->method = $molliePayment->method;

            if (!$row->save()) {
                $this->stderr("  x transaction {$row->id} ({$label} #{$element->id}, {$element->email}): could not save — " . Json::encode($row->getErrors()) . "\n", Console::FG_RED);
                $stats['failed']++;
                continue;
            }

            $this->stdout("  \u{2713} transaction {$row->id} ({$label} #{$element->id}, {$element->email}): set paidAt={$row->paidAt} method={$row->method}\n", Console::FG_GREEN);
            $stats['fixed']++;
        }

        $this->stdout("\n--- Summary ---\n");
        $this->stdout("fixed={$stats['fixed']} no longer paid={$stats['stillUnpaid']} failed={$stats['failed']}\n");

        return $stats['failed'] ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }
}
