---
title: Status updates - Mollie payments
prev: false
next: false
---

# Payment status updates

The plugin uses webhooks to update payment status and fires an event bases on those.

When the user completes a payment, he/she is redirect back to the site (to the redirect url you provided). That url has a query parameters with the payment's UID and the status we get back from Mollie. Use this status to determine which content should be displayed on the confirmation page.

However, the [EVENT_AFTER_TRANSACTION_UPDATE](events.html#event-after-transaction-update) is not fired upon this redirect. That happens when Mollie calls our webhook URL.

More information on this webhook and what it contains can be found [here](https://docs.mollie.com/guides/webhooks). Note that to test this locally you'll have to fake to post request, with the right content, for the event to fire and the status to be updated in the CP.