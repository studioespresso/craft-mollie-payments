<?php
/**
 * Mollie Payments plugin for Craft CMS 3.x
 *
 * Easily accept payment with Mollie Payments
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2019 Studio Espresso
 */

namespace studioespresso\molliepayments;

use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\services\FormService;
use studioespresso\molliepayments\variables\MolliePaymentsVariable;
use studioespresso\molliepayments\twigextensions\MolliePaymentsTwigExtension;
use studioespresso\molliepayments\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class MolliePayments
 *
 * @author    Studio Espresso
 * @package   MolliePayments
 * @since     1.0.0
 *
 * @property FormService $forms
 *
 */
class MolliePayments extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var MolliePayments
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'forms' => FormService::class
        ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['mollie-payments'] = 'mollie-payments/default/index';
                $event->rules['mollie-payments/forms'] = 'mollie-payments/forms/index';
                $event->rules['mollie-payments/forms/add'] = 'mollie-payments/forms/edit';
                $event->rules['mollie-payments/forms/edit/<formId:\d+>'] = 'mollie-payments/forms/edit';
                $event->rules['mollie-payments/settings'] = 'mollie-payments/settings/edit';

            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Payment::class;
            }
        );

    }

    public function getCpNavItem()
    {
        $subNavs = [];
        $navItem = parent::getCpNavItem();
        $navItem['label'] = Craft::t("mollie-payments", "Payments");

        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $subNavs['forms'] = [
                'label' => 'Forms',
                'url' => 'mollie-payments/forms',
            ];
        }

        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $subNavs['settings'] = [
                'label' => 'Settings',
                'url' => 'mollie-payments/settings',
            ];
        }

        $navItem = array_merge($navItem, [
            'subnav' => $subNavs,
        ]);
        return $navItem;
    }

    // Protected Methods
    // =========================================================================
    protected function createSettingsModel()
    {
        return new Settings();
    }
}
