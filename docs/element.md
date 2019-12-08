---
title: Getting Started - Mollie payments
prev: false
next: false
---
# Core concepts
## Payment elements
Each payment is a full Craft element, which means you can add custom fields to it (which you do through the [form](/craft-mollie-payments/general.html#_2-create-a-payment-form) it's linked to).
Apart from those custom fields, each payment has the following required properties
- email
- amount (the amount to me payed. This has to be passed on a hidden field with the [hash](https://docs.craftcms.com/v3/dev/filters.html#hash) filter)
- formId (the ID of the [payment form](#payment-forms) it is links to)

In the CP, payments are grouped by form and can be search on email, amount or any of the searchable custom fields you've added.

## Payment forms

Payment forms are where you define the field layout for the your payment, as well as the following properties:
- the title & handle of the form, currently only visible in the CP
- the currency the payment will be processes in
- the description of the payment in Mollie. This field works like the dynamic title field on an Entrytype, so you can use custom fields in it as well. Will default to ``Order #number``.

<img src="./images/paymentform.png">



Which form the payment is for is determined by passing the following hidden field along in your payment form template:
`````html
<input type="hidden" name="form" value="{{ 3|hash }}">
`````

__Note:__ the form ID has to passed using the [hash](https://docs.craftcms.com/v3/dev/filters.html#hash) function, otherwise the payment will not be submitted.

## Payment transactions

Once a payment is submitted to the ``mollie-payments/payment/pay`` controller and the Payment element is validated, a new Payment transaction will be created throught the Mollie api. 

The ID, amount and status are saved with the transaction, as well as a relation to the Payment element. The list of transactions related to a Payment element is visible on a tab on the element:

<img src="./images/transaction.png">
 
 
 