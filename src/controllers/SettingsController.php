<?php

namespace studioespresso\molliepayments\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use studioespresso\molliepayments\MolliePayments;

class SettingsController extends Controller
{
    public function actionIndex()
    {
        return $this->asCpScreen()
            ->crumbs([
                ['label' => Craft::t('mollie-payments', 'Payments'), 'url' => UrlHelper::cpUrl('mollie-payments')],
                ['label' => Craft::t("mollie-payments", 'Settings')],
            ])
            ->selectedSubnavItem('settings')
            ->title(Craft::t('mollie-payments', 'Settings'))
            ->action('mollie-payments/settings/save')
            ->redirectUrl('mollie-payments/settings')
            ->contentTemplate('mollie-payments/_settings.twig', [
                'settings' => MolliePayments::getInstance()->getSettings(),
            ]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $params = Craft::$app->getRequest()->getBodyParams();
        $data = $params['settings'];

        $settings = MolliePayments::getInstance()->getSettings();
        $settings->apiKey = $data['apiKey'];
        $settings->manageSubscriptionEmailPath = $data['manageSubscriptionEmailPath'];
        $settings->manageSubscriptionEmailSubject = $data['manageSubscriptionEmailSubject'];
        $settings->manageSubscriptionRoute = $data['manageSubscriptionRoute'];

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
