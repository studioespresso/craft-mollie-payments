---
title: Donation form - Mollie payments
prev: false
next: false
---

# Donation form
Sometimes you need to give the enduser the option to choose the price they're paying for themself. You can handle those payments with the ``donate`` action. 
This form uses a different action then the payment form and does not require the amount to be a hashed value. 
```html
<form method="post">
    {{ csrfInput() }}
    {{ actionInput("mollie-payments/payment/donate") }}
    {{ redirectInput("confirmation-page") }}
    <input type="text" name="amount" value="" placeholder="Select the amount you want tot donate">
    <input type="hidden" name="form" value="{{ 'formHandle'|hash }}">
    
    <input type="email" name="email">
    <input type="text" name="fields[firstName]">
    <input type="text" name="fields[lastName]">
    <input type="submit" class="btn " value="Pay">
</form>
```