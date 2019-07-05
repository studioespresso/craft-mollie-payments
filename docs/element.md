---
title: Getting Started - Mollie payments
prev: false
next: false
---
# Core concepts
## Payment elements

## Payment forms

Payment forms are where you define the field layout for the your payment and the currency it should processed in.
The form is passed through the payment form as follows
`````html
<input type="hidden" name="form" value="{{ 3|hash }}">
`````

__Note:__ the form ID has to passed using the [hash](https://docs.craftcms.com/v3/dev/filters.html#hash) function, otherwise the payment will not be submitted.

## Payment transactions

Once a payment is submitted to the ``mollie-payments/payment/pay`` controller and the Payment element is validated, a new Payment transaction will be created throught the Mollie api. 

The ID, amount and status are saved with the transaction, as well as a relation to the Payment element. The list of transactions related to a Payment element is visible on a tab on the element: 
 
 