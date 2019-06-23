<?php

namespace studioespresso\molliepayments\services;

use craft\base\Component;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;

class MollieService extends Component
{
    private $mollie;

    public function init()
    {
        $this->mollie = new Mollie
    }


    public function generatePayment()
    {
        dd('hier');
    }

}
