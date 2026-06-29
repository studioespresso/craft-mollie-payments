<?php

namespace studioespresso\molliepayments\console\controllers;

use craft\console\Controller;
use craft\db\Query;
use craft\helpers\Console;
use craft\helpers\DateTimeHelper;
use Mollie\Api\Types\PaymentStatus;
use studioespresso\molliepayments\elements\Subscription;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentTransactionRecord;
use studioespresso\molliepayments\records\SubscriptionRecord;
use yii\console\ExitCode;

/**
 * Maintenance commands for subscriptions.
 */
class SubscriptionsController extends Controller
{
    /**
     * @var bool Whether to only list the subscriptions that would be recovered, without calling Mollie.
     */
    public bool $dryRun = false;

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        if ($actionID === 'recover') {
            $options[] = 'dryRun';
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'd' => 'dryRun',
        ]);
    }

    /**
     * Retroactively creates Mollie subscriptions for subscriptions that got stuck because
     * of the `startDate` format bug (#83): their first payment was paid, but createSubscription()
     * failed silently so the subscription was never created in Mollie.
     *
     * A subscription is considered stuck when:
     *  - subscriptionStatus = "pending"
     *  - subscriptionId IS NULL
     *  - it has a linked transaction with status = "paid"
     *
     * Mollie does not resend webhooks for old payments, so these have to be recovered manually.
     *
     * Before creating anything, each subscription is checked against Mollie: if the customer
     * already has a matching, non-canceled subscription (e.g. one that was created but whose id was
     * never stored locally), it is linked instead of re-created, so no duplicates are made.
     *
     * The first charge is anchored to the original billing cadence: it's set to the first
     * occurrence of (paidAt + N×interval) that is today or later. This keeps the customer's
     * billing day consistent and never charges for an already-paid period. Mollie does not allow
     * a startDate in the past, so any cycles that elapsed while the subscription was stuck cannot
     * be reclaimed.
     *
     * Usage:
     *   ./craft mollie-payments/subscriptions/recover            # recover stuck subscriptions
     *   ./craft mollie-payments/subscriptions/recover --dry-run  # only list what would be recovered
     *
     * @return int
     */
    public function actionRecover(): int
    {
        $ids = (new Query())
            ->select(['s.id'])
            ->distinct()
            ->from(['s' => SubscriptionRecord::tableName()])
            ->innerJoin(['t' => PaymentTransactionRecord::tableName()], '[[t.payment]] = [[s.id]]')
            ->where([
                's.subscriptionStatus' => 'pending',
                's.subscriptionId' => null,
                't.status' => PaymentStatus::STATUS_PAID,
            ])
            ->column();

        if (!$ids) {
            $this->stdout('No stuck subscriptions found.' . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout(sprintf('Found %d stuck subscription(s).%s', count($ids), PHP_EOL), Console::FG_YELLOW);

        if ($this->dryRun) {
            foreach ($ids as $id) {
                $element = Subscription::find()->id($id)->one();
                $label = $element?->email ?? "#$id";
                if ($element && ($existing = MolliePayments::getInstance()->mollie->getExistingSubscription($element))) {
                    $this->stdout("  [dry-run] subscription #$id ($label) already exists in Mollie ({$existing->id}) — would link, not create" . PHP_EOL);
                    continue;
                }
                $startDate = $this->resolveStartDate($id, $element?->interval);
                $when = $startDate ? $startDate->format('Y-m-d') : 'now + interval (no paid date found)';
                $this->stdout("  [dry-run] would recover subscription #$id ($label) — first charge: $when" . PHP_EOL);
            }
            $this->stdout('Dry run complete — nothing was created in Mollie.' . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $recovered = 0;
        $adopted = 0;
        $failed = 0;
        foreach ($ids as $id) {
            $element = Subscription::find()->id($id)->one();
            if (!$element) {
                $this->stdout("  ! could not load subscription #$id, skipping" . PHP_EOL, Console::FG_RED);
                $failed++;
                continue;
            }

            // Avoid creating a duplicate: if Mollie already has a matching subscription (e.g. it was
            // created but the local subscriptionId never got stored), link to it instead of re-creating.
            $existing = MolliePayments::getInstance()->mollie->getExistingSubscription($element);
            if ($existing) {
                $element->subscriptionId = $existing->id;
                $element->subscriptionStatus = 'active';
                \Craft::$app->getElements()->saveElement($element);
                $this->stdout("  • subscription #$id ({$element->email}) already exists in Mollie ({$existing->id}) — linked, not re-created" . PHP_EOL, Console::FG_YELLOW);
                $adopted++;
                continue;
            }

            $startDate = $this->resolveStartDate($id, $element->interval);
            $when = $startDate ? $startDate->format('Y-m-d') : 'now + interval';
            if (MolliePayments::getInstance()->mollie->createSubscription($element, $startDate)) {
                $this->stdout("  ✓ recovered subscription #$id ({$element->email}) → {$element->subscriptionId}, first charge $when" . PHP_EOL, Console::FG_GREEN);
                $recovered++;
            } else {
                $this->stdout("  ✗ failed to recover subscription #$id ({$element->email}) — check the logs" . PHP_EOL, Console::FG_RED);
                $failed++;
            }
        }

        $this->stdout(sprintf('Done. %d created, %d linked to existing, %d failed.%s', $recovered, $adopted, $failed, PHP_EOL), $failed ? Console::FG_YELLOW : Console::FG_GREEN);
        return $failed ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * Anchors the first charge to the original billing cadence: the first occurrence of
     * (paidAt + N×interval) that falls today or later. Returns null when the paid date or the
     * interval can't be determined, so the caller falls back to the default "now + interval".
     *
     * @param int $subscriptionId
     * @param string|null $interval
     * @return \DateTimeInterface|null
     */
    private function resolveStartDate(int $subscriptionId, ?string $interval): ?\DateTimeInterface
    {
        if (!$interval) {
            return null;
        }

        /** @var PaymentTransactionRecord|null $transaction */
        $transaction = PaymentTransactionRecord::find()
            ->where(['payment' => $subscriptionId, 'status' => PaymentStatus::STATUS_PAID])
            ->orderBy(['dateCreated' => SORT_ASC])
            ->one();

        if (!$transaction) {
            return null;
        }

        $paidAt = DateTimeHelper::toDateTime($transaction->paidAt ?: $transaction->dateCreated);
        if (!$paidAt) {
            return null;
        }

        $now = DateTimeHelper::now();
        $start = \DateTime::createFromInterface($paidAt);

        // Advance by the configured interval (at least once, never reusing the original payment date)
        // until the charge lands today or later — Mollie rejects a startDate in the past.
        do {
            $start->modify("+ {$interval}");
        } while ($start->format('Y-m-d') < $now->format('Y-m-d'));

        return $start;
    }
}
