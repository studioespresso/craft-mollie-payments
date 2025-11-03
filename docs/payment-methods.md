---
title: Payment Methods - Mollie payments
prev: false
next: false
---

# Available payment methods
Sometimes you want to have the customer choose their payment method on the payment form, instead of on Mollie's page. 
You can get the available payment methods using the function bellow, and render them as you see fit.
To pass the selected method to the form, make sure you call the input ``paymentMethod`` with the selected method's id.
 
 ```html
<form method="post">
    {{ csrfInput() }}
    {{ actionInput("mollie-payments/payment/pay") }}
    {{ redirectInput("confirmation-page") }}
    <input type="hidden" name="amount" value="{{ 9.95|hash }}">
    <input type="hidden" name="form" value="{{ 'formHandle'|hash }}">
    
    <select name="paymentMethod">
        {% for method in craft.molliePayments.getPaymentMethods() %}
            <option value="{{ method.id }}">{{ method.description }}</option>
        {% endfor %}
    </select>
    
    <input type="email" name="email">
    <input type="text" name="fields[firstName]">
    <input type="text" name="fields[lastName]">
    <input type="submit" class="btn " value="Pay">
</form>
```