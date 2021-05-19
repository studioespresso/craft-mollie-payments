---
title: Templating - Mollie payments
prev: false
next: false
---

# Templating
 
## Basic payment form
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

## Donation form
Somethings you need to give the enduser the option to choose the price they're paying for themself. You can handle those payments with the ``donate`` action. 
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

## Multi-step form
Since version 1.4.0, you can have multi-step forms with your own prices logic & validation and in step 2 pass the payment on the Mollie. Let's have a look that the following example:

### Step 1: Cart
````html
<form method="post">
    {{ csrfInput() }}
    <!-- Post the form to your own controller, see step 2 --> 
    {{ actionInput("module/default/index") }}
    {{ redirectInput("/step2") }}
    <input type="hidden" name="form" value="{{ 'formHandle'|hash }}">

    <!-- Add your own cart/product and pricing logic here --> 
    {% for productEntry in craft.entries.section('products').orderBy('title ASC').all() %}
        <div>
            <strong>{{ productEntry.title }} (€{{ productEntry.price }})</strong>
        </div>
    {% endfor %}
    <input type="submit" value="Pay">
</form>
````

### Step 2: Save the payment in your own controller

````php
public function actionIndex()
{
    // Payment form is a required parameter here
    $form = Craft::$app->request->getRequiredBodyParam('form');
    $form = Craft::$app->getSecurity()->validateData($form);
    $paymentForm = MolliePayments::getInstance()->forms->getFormByHandle($form);

    // Your own pricing calculation & validation here
    $amount = $this->yourFunction();

    // Create a new payment element
    $payment = new Payment();
    $payment->email = '';
    $payment->amount = $amount;
    $payment->formId = $paymentForm->id;
    $payment->paymentStatus = 'cart';
    $payment->fieldLayoutId = $paymentForm->fieldLayout;
    $payment->setFieldValuesFromRequest('fields');
    
    // Set this if the e-mailaddress is empty on this step 
    $payment->setScenario(Element::SCENARIO_ESSENTIALS);

    if(MolliePayments::getInstance()->payment->save($payment)) {
        $redirect = Craft::$app->request->getValidatedBodyParam('redirect');
        // Make sure you hash the payment UID and pass it as a parameter
        $hash = Craft::$app->getSecurity()->hashData($payment->uid);
        $this->redirect(UrlHelper::url($redirect, ['payment' => $hash]));
    }
}
````

### Step 3: User data 

````html
<form method="post">
    {{ csrfInput() }}
    {{ actionInput("mollie-payments/payment/pay") }}
    {{ redirectInput("/") }}
     <!-- Here we pass the hashed payment UID back to the form -->
    <input type="hidden" name="payment" value="{{ craft.app.request.getParam('payment') }}">

    <input type="email" name="email" placeholder="email">
    <input type="text" name="fields[fullName]" placeholder="name">

    <input type="submit" value="Pay">
</form>
````

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