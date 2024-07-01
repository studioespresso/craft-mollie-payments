---
title: Getting started with subscriptions - Mollie payments
prev: false
next: false
---

# Subscriptions  <Badge type="info" text="5.1.0" />
Version 5.1.0 sees the introduction of support for Mollie's [Recurring Payments](https://docs.mollie.com/docs/recurring-payments), through the [Subscriptions API](https://docs.mollie.com/reference/subscriptions-api).

The plugin will handle the following:
- Creating a customer
- Creating an initial payment - which, if successfull, allows us to create a [mandate](https://docs.mollie.com/reference/create-mandate)
- Create a subscription.

Once the subscription is created, subsequent payments are handled automatically by Mollie - and will be reported back to the plugin, so you can keep track of all payments that happen within a subscription.

## Creating a subscriptions form
Since subscriptions come with a couple of extra requirements, payment forms now have a "Payment Type" option, with the following options:
- Single payment - *the classic one-off payment that was always available)*
- Subscription (recurring payment) - *the subscription option added in 5.1.0*


> [!Warning]
> Note that changing the type of a form is not possible once there are payments associated with that form.


## Form template

This is a basic example that contains all required input & actions to create a subscription.

### Required fields
<br>

#### ``form``
handle of the form the payment belongs to, **has to be hashed**

#### ``interval``

Interval to wait between payments, for example 1 month or 14 days.

The maximum interval is one year (`12 months`, `52 weeks`, or `365 days`).

Possible values: `... days` `... weeks` `...months`

### Not required:
<br>

#### ``times``
number of times x the above interval the subscription should run for. If not included, the subscription will run indefinitly.

 ```html
<form method="post" class="mb-8">
    {{ csrfInput() }}
    {{ actionInput("mollie-payments/subscription/subscribe") }}
    {{ hiddenInput('form', 'subscriptionForm'|hash) }}

    {{ hiddenInput('interval', '1 years') }}
    {{ hiddenInput('times', '1') }}

    {{ redirectInput("/confirmation-page") }}
 
    <div class="mt-4">
        <label for="amount">{{ "Amount"|t }}</label>
        <select name="amount" id="amount">
            <option value="{{ 25|hash }}">€25</option>
            <option value="{{ 50|hash }}">€50</option>
            <option value="{{ 75|hash }}">€75</option>
            <option value="{{ 100|hash }}">€100</option>
        </select>
    </div>
    <button type="submit">{{ "Subscribe now"|t }}</button>
</form>
```