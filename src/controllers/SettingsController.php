<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\web\Controller;
use studioespresso\molliepayments\MolliePayments;

class SettingsController extends Controller
{
    public function actionIndex()
    {
        $settings = MolliePayments::getInstance()->getSettings();
        return $this->renderTemplate('mollie-payments/_settings.twig', ['settings' => $settings]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $params = Craft::$app->getRequest()->getBodyParams();
        $data = $params['settings'];

        $settings = MolliePayments::getInstance()->getSettings();
        $settings->apiKey = $data['apiKey'] ?? $settings->orderReferenceFormat;

        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('mollie-payments', 'Couldn’t save settings.'));
            return $this->renderTemplate('mollie-payments/settings', compact('settings'));
        }

        $pluginSettingsSaved = Craft::$app->getPlugins()->savePluginSettings(MolliePayments::getInstance(), $settings->toArray());

        if (!$pluginSettingsSaved) {
            Craft::$app->getSession()->setError(Craft::t('mollie-payments', 'Couldn’t save settings.'));
            return $this->renderTemplate('mollie-payments/settings', compact('settings'));
        }

        Craft::$app->getSession()->setNotice(Craft::t('mollie-payments', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}