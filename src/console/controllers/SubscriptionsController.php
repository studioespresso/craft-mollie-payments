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
     * Duplicate protection works on two levels:
     *  - Stuck subscriptions are grouped by customer + amount + interval. Only the record with the
     *    most recent payment is recovered per group; the others are marked "canceled" so the
     *    customer ends up with exactly one active subscription and is never billed twice.
     *  - Before creating anything, the customer is checked against Mollie: if a matching, non-canceled
     *    subscription already exists (e.g. one that was created but whose id was never stored locally),
     *    it is linked instead of re-created.
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

        // Group the candidates by customer + amount + interval so we recover only one subscription
        // per group. Within a group the record with the most recent payment wins; the rest are
        // duplicates (repeated signup attempts) that should not become extra Mollie subscriptions.
        $missing = 0;
        $groups = [];
        foreach ($ids as $id) {
            $element = Subscription::find()->id($id)->one();
            if (!$element) {
                $this->stdout("  ! could not load subscription #$id, skipping" . PHP_EOL, Console::FG_RED);
                $missing++;
                continue;
            }
            $groups[$this->dedupeKey($element)][] = [
                'element' => $element,
                'paidAt' => $this->getPaidAt($id),
            ];
        }

        // Most recent payment first, so $group[0] is the record we recover.
        foreach ($groups as &$group) {
            usort($group, fn($a, $b) => ($b['paidAt']?->getTimestamp() ?? 0) <=> ($a['paidAt']?->getTimestamp() ?? 0));
        }
        unset($group);

        $this->stdout(sprintf('Found %d stuck subscription(s) in %d group(s).%s', count($ids), count($groups), PHP_EOL), Console::FG_YELLOW);

        if ($this->dryRun) {
            foreach ($groups as $group) {
                $winner = $group[0]['element'];
                $existing = MolliePayments::getInstance()->mollie->getExistingSubscription($winner);
                if ($existing) {
                    $this->stdout("  [dry-run] #{$winner->id} ({$winner->email}) already exists in Mollie ({$existing->id}) — would link" . PHP_EOL);
                } else {
                    $startDate = $this->resolveStartDate($winner->id, $winner->interval);
                    $when = $startDate ? $startDate->format('Y-m-d') : 'now + interval (no paid date found)';
                    $this->stdout("  [dry-run] would recover #{$winner->id} ({$winner->email}) — first charge: $when" . PHP_EOL);
                }
                for ($i = 1, $n = count($group); $i < $n; $i++) {
                    $dup = $group[$i]['element'];
                    $this->stdout("  [dry-run]   ↳ duplicate #{$dup->id} ({$dup->email}) — would be marked canceled" . PHP_EOL, Console::FG_YELLOW);
                }
            }
            $this->stdout('Dry run complete — nothing was created in Mollie.' . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $recovered = 0;
        $adopted = 0;
        $duplicates = 0;
        $failed = $missing;
        foreach ($groups as $group) {
            $winner = $group[0]['element'];

            // Reuse an existing Mollie subscription if there is one, otherwise create it.
            $existing = MolliePayments::getInstance()->mollie->getExistingSubscription($winner);
            if ($existing) {
                $winner->subscriptionId = $existing->id;
                $winner->subscriptionStatus = 'active';
                \Craft::$app->getElements()->saveElement($winner);
                $this->stdout("  • #{$winner->id} ({$winner->email}) already exists in Mollie ({$existing->id}) — linked" . PHP_EOL, Console::FG_YELLOW);
                $adopted++;
            } else {
                $startDate = $this->resolveStartDate($winner->id, $winner->interval);
                $when = $startDate ? $startDate->format('Y-m-d') : 'now + interval';
                if (MolliePayments::getInstance()->mollie->createSubscription($winner, $startDate)) {
                    $this->stdout("  ✓ recovered #{$winner->id} ({$winner->email}) → {$winner->subscriptionId}, first charge $when" . PHP_EOL, Console::FG_GREEN);
                    $recovered++;
                } else {
                    $this->stdout("  ✗ failed to recover #{$winner->id} ({$winner->email}) — check the logs" . PHP_EOL, Console::FG_RED);
                    $failed++;
                    // Leave the duplicates untouched so the group can be retried on a later run.
                    continue;
                }
            }

            // Mark the remaining records in the group as canceled so they don't become extra
            // subscriptions and drop out of future runs.
            for ($i = 1, $n = count($group); $i < $n; $i++) {
                $dup = $group[$i]['element'];
                $dup->subscriptionStatus = 'canceled';
                \Craft::$app->getElements()->saveElement($dup);
                $this->stdout("      ↳ marked duplicate #{$dup->id} ({$dup->email}) as canceled" . PHP_EOL, Console::FG_YELLOW);
                $duplicates++;
            }
        }

        $this->stdout(sprintf('Done. %d created, %d linked to existing, %d duplicates canceled, %d failed.%s', $recovered, $adopted, $duplicates, $failed, PHP_EOL), $failed ? Console::FG_YELLOW : Console::FG_GREEN);
        return $failed ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * Builds the grouping key used to detect duplicate stuck subscriptions: a subscription is
     * considered the same when it's for the same customer (form + email), amount and interval.
     */
    private function dedupeKey(Subscription $element): string
    {
        return implode('|', [
            (string)$element->formId,
            mb_strtolower(trim((string)$element->email)),
            number_format((float)$element->amount, 2, '.', ''),
            (string)$element->interval,
        ]);
    }

    /**
     * Returns the date of the subscription's earliest paid transaction, or null when there isn't one.
     *
     * @return \DateTimeInterface|null
     */
    private function getPaidAt(int $subscriptionId): ?\DateTimeInterface
    {
        /** @var PaymentTransactionRecord|null $transaction */
        $transaction = PaymentTransactionRecord::find()
            ->where(['payment' => $subscriptionId, 'status' => PaymentStatus::STATUS_PAID])
            ->orderBy(['dateCreated' => SORT_ASC])
            ->one();

        if (!$transaction) {
            return null;
        }

        return DateTimeHelper::toDateTime($transaction->paidAt ?: $transaction->dateCreated) ?: null;
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

        $paidAt = $this->getPaidAt($subscriptionId);
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
