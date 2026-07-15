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
     * @var bool Whether to only list what would happen, without creating or canceling anything.
     */
    public bool $dryRun = false;

    /**
     * @var string Comma-separated email addresses to skip entirely (e.g. test accounts).
     */
    public string $exclude = '';

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        if ($actionID === 'recover') {
            $options[] = 'dryRun';
            $options[] = 'exclude';
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
     * Duplicate protection works on several levels:
     *  - If the customer already has a non-canceled subscription (with a subscriptionId) for the same
     *    form + amount + interval, the stuck records are failed duplicates of it: they are marked
     *    "canceled" and nothing is created. This is checked against the local database, which stays
     *    reliable even when the Mollie API lookup misses the existing subscription.
     *  - Otherwise stuck subscriptions are grouped by form + email + amount + interval; only the record
     *    with the most recent payment is recovered per group and the rest are marked "canceled", so a
     *    customer never ends up with multiple recurring subscriptions.
     *  - As a final net before creating, the customer is checked against Mollie and an existing matching
     *    subscription is linked instead of re-created.
     *
     * The first charge is anchored to the original billing cadence: it's set to the first
     * occurrence of (paidAt + N×interval) that is today or later. This keeps the customer's
     * billing day consistent and never charges for an already-paid period. Mollie does not allow
     * a startDate in the past, so any cycles that elapsed while the subscription was stuck cannot
     * be reclaimed.
     *
     * Usage:
     *   ./craft mollie-payments/subscriptions/recover                      # recover stuck subscriptions
     *   ./craft mollie-payments/subscriptions/recover --dry-run            # list what would happen, no changes
     *   ./craft mollie-payments/subscriptions/recover --exclude="a@x,b@y"  # skip specific emails (e.g. tests)
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

        // Emails to skip entirely (e.g. test accounts).
        $excluded = array_filter(array_map(
            fn($e) => mb_strtolower(trim($e)),
            $this->exclude !== '' ? explode(',', $this->exclude) : []
        ));

        if ($this->dryRun) {
            foreach ($groups as $group) {
                $winner = $group[0]['element'];
                $count = count($group);

                if (in_array(mb_strtolower(trim((string)$winner->email)), $excluded, true)) {
                    $this->stdout("  [dry-run] ⊘ {$winner->email} — excluded, would skip $count record(s)" . PHP_EOL, Console::FG_GREY);
                    continue;
                }

                if ($activeId = $this->findActiveSubscriptionId($winner)) {
                    $this->stdout("  [dry-run] #{$winner->id} ({$winner->email}) already has an active subscription ($activeId) — would cancel $count stuck duplicate(s), create nothing" . PHP_EOL, Console::FG_YELLOW);
                    continue;
                }

                $startDate = $this->resolveStartDate($winner->id, $winner->interval);
                $when = $startDate ? $startDate->format('Y-m-d') : 'now + interval (no paid date found)';
                $this->stdout("  [dry-run] would recover #{$winner->id} ({$winner->email}) — first charge: $when" . PHP_EOL);
                for ($i = 1; $i < $count; $i++) {
                    $dup = $group[$i]['element'];
                    $this->stdout("  [dry-run]   ↳ duplicate #{$dup->id} ({$dup->email}) — would be marked canceled" . PHP_EOL, Console::FG_YELLOW);
                }
            }
            $this->stdout('Dry run complete — nothing was changed.' . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $recovered = 0;
        $adopted = 0;
        $duplicates = 0;
        $excludedCount = 0;
        $failed = $missing;
        foreach ($groups as $group) {
            $winner = $group[0]['element'];
            $count = count($group);

            // Skip excluded accounts (e.g. tests) entirely.
            if (in_array(mb_strtolower(trim((string)$winner->email)), $excluded, true)) {
                $this->stdout("  ⊘ skipped {$winner->email} — excluded ($count record(s))" . PHP_EOL, Console::FG_GREY);
                $excludedCount += $count;
                continue;
            }

            // If the customer already has an active subscription for this form + amount + interval, the
            // stuck records are failed duplicates of it: cancel them all and create nothing.
            if ($activeId = $this->findActiveSubscriptionId($winner)) {
                foreach ($group as $entry) {
                    $entry['element']->subscriptionStatus = 'canceled';
                    \Craft::$app->getElements()->saveElement($entry['element']);
                    $duplicates++;
                }
                $this->stdout("  • #{$winner->id} ({$winner->email}) already has an active subscription ($activeId) — canceled $count stuck duplicate(s)" . PHP_EOL, Console::FG_YELLOW);
                continue;
            }

            // Final net: reuse an existing Mollie subscription if the API reports one, otherwise create.
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

            // Cancel the remaining within-group duplicates.
            for ($i = 1; $i < $count; $i++) {
                $dup = $group[$i]['element'];
                $dup->subscriptionStatus = 'canceled';
                \Craft::$app->getElements()->saveElement($dup);
                $this->stdout("      ↳ marked duplicate #{$dup->id} ({$dup->email}) as canceled" . PHP_EOL, Console::FG_YELLOW);
                $duplicates++;
            }
        }

        $this->stdout(sprintf('Done. %d created, %d linked, %d duplicates canceled, %d excluded, %d failed.%s', $recovered, $adopted, $duplicates, $excludedCount, $failed, PHP_EOL), $failed ? Console::FG_YELLOW : Console::FG_GREEN);
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
     * Returns the subscriptionId of an existing, non-canceled subscription for the same customer
     * (form + email), amount and interval — proof the customer is already subscribed, so any stuck
     * records are failed duplicates. Checked against the local database, which stays reliable even
     * when the Mollie API lookup misses the subscription (e.g. it lives under a different customer).
     */
    private function findActiveSubscriptionId(Subscription $element): ?string
    {
        return (new Query())
            ->select(['subscriptionId'])
            ->from(SubscriptionRecord::tableName())
            ->where([
                'formId' => $element->formId,
                'amount' => $element->amount,
                'interval' => $element->interval,
            ])
            ->andWhere(['not', ['subscriptionId' => null]])
            ->andWhere(['<>', 'subscriptionStatus', 'canceled'])
            ->andWhere('LOWER([[email]]) = :email', [':email' => mb_strtolower(trim((string)$element->email))])
            ->scalar() ?: null;
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
