<?php

namespace studioespresso\molliepayments\controllers;

use craft\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return  $this->renderTemplate('mollie-payments/_payment/_index.twig');
    }
}
