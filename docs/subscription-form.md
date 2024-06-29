---
title: Subscription form template - Mollie payments
prev: false
next: false
---

# Subscription form template
This is a basic example that contains all required input & actions to create a payment:
 
 ```html
<form method="post">
    {{ csrfInput() }}
    {{ actionInput("mollie-payments/payment/pay") }}
    {{ redirectInput("confirmation-page") }}
    <input type="hidden" name="amount" value="{{ 9.95|hash }}">
    <input type="hidden" name="form" value="{{ 'formHandle'|hash }}">
    
    <input type="email" name="email">
    <input type="text" name="fields[firstName]">
    <input type="text" name="fields[lastName]">
    <input type="submit" class="btn " value="Pay">
</form>
```