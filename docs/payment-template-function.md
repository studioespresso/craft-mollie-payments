---
title: Template functions - Mollie payments
prev: false
next: false
---
# Template functions

## craft.payments
To display data from the payment on a confirmation page or on a profile page, you can `craft.payments` to query for payments in twig.

### Confirmation page
On the page to which the user gets redirected after payment, the url has the status and the `uid` of the payment.
```twig
{% set uid = craft.app.request.getParam('payment')%}
{% set payment = craft.payments.uid(uid).one() %}
{{ payment.email }}
{{ payment.firstName }} {# or any other custom field by handle #}
```

### Profile page
If you want to show an overview of a user's payments, on a profiel page for example, you can search the payments by email.

```twig
{% for payment in craft.payments.email(currentUser.email).status('paid').all() %}
    {{ payment.amount %}
    {{ payment.firstName }} {# or any other custom field by handle #}
    {{ payment.getForm().currency }}
    {{ payment.getForm().title }}     
{% endfor %}
```