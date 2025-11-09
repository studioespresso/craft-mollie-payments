---
title: Payment Methods - Mollie payments
prev: false
next: false
---

# Available payment methods
Sometimes you want to have the customer choose their payment method on the payment form, instead of on Mollie's page. 
You can get the available payment methods using the function bellow, and render them as you see fit.
To pass the selected method to the form, make sure you call the input ``paymentMethod`` with the selected method's id.

The only required parameter is the form handle.
 
 ```html
<form method="post">
    {{ csrfInput() }}
    {{ actionInput("mollie-payments/payment/pay") }}
    {{ redirectInput("confirmation-page") }}
    <input type="hidden" name="amount" value="{{ 9.95|hash }}">
    <input type="hidden" name="form" value="{{ 'formHandle'|hash }}">
    
    <select name="paymentMethod">
        {% for method in craft.molliePayments.getPaymentMethods('formHandle') %}
            <option value="{{ method.id }}">{{ method.description }}</option>
        {% endfor %}
    </select>
    
    <input type="email" name="email">
    <input type="text" name="fields[firstName]">
    <input type="text" name="fields[lastName]">
    <input type="submit" class="btn " value="Pay">
</form>
```

## Parameters
Mollie supports a number of parameters to filter the available payment methods. You can pass these parameters as an associative array to the `getPaymentMethods` function.
```html
{% set methods = craft.mollie.getPaymentMethods('formHandle', {
    amount: {
        value: 1500.00,
        currency: 'EUR',
    },
    locale: 'en_US',
}) %}
```

Mollie will then only request payment methods that are available for the given amount. If you can't specific the mount, just ommit that option entirely.