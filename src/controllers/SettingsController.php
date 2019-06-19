<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\web\Controller;

class SettingsController extends Controller
{
    public function actionIndex() {
        return  $this->renderTemplate('mollie-payments/_settings.twig');
    }
}