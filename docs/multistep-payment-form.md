---
title: Multi-step payment form - Mollie payments
prev: false
next: false
---

# Multi-step payment form
From version 1.4.0 and onwards, you can have multi-step forms with your own prices logic & validation and in step 2 pass the payment on the Mollie. Let's have a look that the following example:

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