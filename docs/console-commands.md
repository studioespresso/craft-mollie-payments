# Console commands

The plugin ships a few maintenance commands, run from your project root with `./craft`. They exist to repair data that a bug left in a bad state — Mollie does not resend webhooks for old payments, so those cases have to be fixed manually.

Every command supports `--dry-run`, which reports what it would do without writing anything or contacting Mollie to create anything. Always run that first.

## Recover stuck subscriptions <Badge type="info" text="5.4.5" />

If a subscription's first payment was paid but the subscription was never created in Mollie, it stays stuck: its status remains `pending` and no `subscriptionId` is stored.

A subscription is considered stuck when **all** of the following are true:
- `subscriptionStatus` is `pending`
- it has no `subscriptionId`
- it has a linked transaction with status `paid`

```sh
# List what would be recovered, without contacting Mollie to create anything
./craft mollie-payments/subscriptions/recover --dry-run

# Recover the stuck subscriptions
./craft mollie-payments/subscriptions/recover
```

The `--dry-run` is read-only and prints, per subscription, the date the first charge would be scheduled on (or whether it would be linked to an existing Mollie subscription).

### First charge date
The first charge is anchored to the original billing cadence: it's set to the first occurrence of `paidAt + N × interval` that falls today or later. This keeps the customer's billing day consistent and never charges for an already-paid period.

> [!Warning]
> Mollie does not allow a `startDate` in the past, so any billing cycles that elapsed while the subscription was stuck cannot be reclaimed. The first recovered charge is the next upcoming cycle.

### Avoiding duplicates
The command guards against duplicates on several levels:

- **Customer already subscribed** — if the customer already has a non-canceled subscription (with a `subscriptionId`) for the same form + amount + interval, the stuck records are failed duplicates of it. They are marked `canceled` and nothing is created. This is checked against the local database, which stays reliable even when the Mollie API lookup misses the existing subscription.
- **Repeated signup attempts** — a stuck first payment often led customers to try again, leaving several `pending` subscriptions for the same person. The command groups stuck subscriptions by form + email + amount + interval and only recovers the one with the most recent payment; the rest are marked `canceled`. This way a customer ends up with exactly one active subscription and is never billed twice.
- **Already created in Mollie** — as a final net before creating, it checks Mollie for an existing, non-canceled subscription for that customer (amount + currency + interval). If one is found, the command links to it instead of creating a duplicate.

### Excluding addresses
Use `--exclude` to skip specific email addresses entirely — handy for test accounts:

```sh
./craft mollie-payments/subscriptions/recover --exclude="test@example.com,another@example.com"
```

The command is safe to run multiple times: once a subscription has a `subscriptionId` it is no longer selected.

## Backfill missing payment dates <Badge type="info" text="5.4.9" />

Recurring charges are created by Mollie's own subscription engine, so the plugin has no local transaction for them when their webhook comes in. Before 5.4.9 that path stored the charge with `status = paid` but never filled in `paidAt` or `method`, so anything that treats a truthy `paidAt` as "this was paid" (exports, reports) silently skipped those charges.

The fix only applies to charges from 5.4.9 onwards. To repair the ones already stored:

```sh
# List what would be backfilled, without writing anything
./craft mollie-payments/transactions/backfill-paid-at --dry-run

# Backfill paidAt and method
./craft mollie-payments/transactions/backfill-paid-at
```

It selects every transaction with `status = paid` and no `paidAt`, re-fetches the payment from Mollie and stores the `paidAt` and `method` Mollie reports.

Transactions Mollie no longer reports as paid are left untouched and listed separately, so the command is safe to run multiple times. Transactions whose element can no longer be found are reported as failures and the command exits with a non-zero status; everything else is still processed.
