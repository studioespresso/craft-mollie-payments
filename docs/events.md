---
title: Events - Mollie payments
prev: false
next: false
---
# Events


### EVENT_BEFORE_PAYMENT_SAVE

Before the payment element gets saved, you can listen this event:

```php
Event::on(
    Payment::class,
    MolliePayments::EVENT_BEFORE_PAYMENT_SAVE,
    function (PaymentUpdateEvent $event) {
        // handle the event here
    });
```

The event contains the following:
- The payment element
- `isNew`: to see if we're saving a new versus an excisting element (right now payment elements can only be saved once so this will always be set to true. In the future when we add updating payment this will change accordingly)

### EVENT_AFTER_TRANSACTION_UPDATE

When Mollie send back a post request to the plugin's webhook to update the status, the following event is fired:

```php
Event::on(
    Transaction::class,
    MolliePayments::EVENT_AFTER_TRANSACTION_UPDATE,
    function (TransactionUpdateEvent $event) {
        // handle transaction status update
    }
);
```
The event contains:
- The payment transaction record
- The payment element
- The payment status Mollie is sending is (string, [possible statuses](https://docs.mollie.com/payments/status-changes))

More information on this webhook and what it contains can be found [here](https://docs.mollie.com/guides/webhooks)