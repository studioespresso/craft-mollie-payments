---
title: Events - Mollie payments
prev: false
next: false
---
# Events

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