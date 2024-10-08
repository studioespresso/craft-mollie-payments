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

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\services\ProjectConfig;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use Mollie\Api\Types\PaymentStatus;
use studioespresso\molliepayments\behaviours\CraftVariableBehavior;
use studioespresso\molliepayments\elements\Payment;

use studioespresso\molliepayments\events\TransactionUpdateEvent;
use studioespresso\molliepayments\models\Settings;
use studioespresso\molliepayments\services\Currency;
use studioespresso\molliepayments\services\Export;
use studioespresso\molliepayments\services\Form;
use studioespresso\molliepayments\services\Mail;
use studioespresso\molliepayments\services\Mollie;
use studioespresso\molliepayments\services\Payment as PaymentServivce;
use studioespresso\molliepayments\services\Subscriber;
use studioespresso\molliepayments\services\Subscription;
use studioespresso\molliepayments\services\Transaction;

use studioespresso\molliepayments\variables\MollieVariable;
use yii\base\Event;

/**
 * Class MolliePayments
 *
 * @author    Studio Espresso
 * @package   MolliePayments
 * @since     1.0.0
 *
 * @property Form $forms
 * @property Mollie $mollie
 * @property Transaction $transaction
 * @property PaymentServivce $payment
 * @property Currency $currency
 * @property Export $export
 * @property Subscription $subscription
 * @property Subscriber $subscriber
 * @property Mail $mail
 */
class MolliePayments extends Plugin
{
    // Constants
    // =========================================================================

    /**
     * @event TransactionUpdateEvent The event that is triggered after a payment transaction is updates.
     */
    public const EVENT_AFTER_TRANSACTION_UPDATE = 'afterTransactionUpdate';

    /**
     * @event beforePaymentSave The event that is triggered before saving a payment element for the first time.
     */
    public const EVENT_BEFORE_PAYMENT_SAVE = 'beforePaymentSave';


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
    public string $schemaVersion = '5.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;


        $this->setComponents([
            'forms' => Form::class,
            'mollie' => Mollie::class,
            'transaction' => Transaction::class,
            'payment' => PaymentServivce::class,
            'subscription' => Subscription::class,
            'subscriber' => Subscriber::class,
            'currency' => Currency::class,
            'export' => Export::class,
            'mail' => Mail::class,
        ]);

        Craft::$app->projectConfig
            ->onAdd('molliePayments.{uid}', [$this->forms, 'handleAddForm'])
            ->onUpdate('molliePayments.{uid}', [$this->forms, 'handleAddForm'])
            ->onRemove('molliePayments.{uid}', [$this->forms, 'handleDeleteForm']);

        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $event) {
            $event->config['molliePayments'] = $this->forms->rebuildProjectConfig();
        });


        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['mollie-payments'] = ['template' => 'mollie-payments/_payment/_index.twig'];
                $event->rules['mollie-payments/payments/<uid:{uid}>'] = 'mollie-payments/payment/edit';
                $event->rules['mollie-payments/subscriptions'] = ['template' => 'mollie-payments/_subscription/_index.twig'];
                $event->rules['mollie-payments/subscribers'] = 'mollie-payments/subscription/subscriber-overview';
                $event->rules['mollie-payments/subscriptions/<uid:{uid}>'] = 'mollie-payments/subscription/edit';
                $event->rules['mollie-payments/forms'] = 'mollie-payments/forms/index';
                $event->rules['mollie-payments/forms/add'] = 'mollie-payments/forms/edit';
                $event->rules['mollie-payments/forms/<formId:\d+>'] = 'mollie-payments/forms/edit';
                $event->rules['mollie-payments/settings'] = 'mollie-payments/settings/index';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['mollie-payments/payment/redirect'] = 'mollie-payments/payment/redirect';
                $event->rules['mollie-payments/subscription/redirect'] = 'mollie-payments/subscription/redirect';
                $event->rules['mollie-payments/payment/webhook'] = 'mollie-payments/payment/webhook';
                $event->rules['mollie-payments/subscription/webhook'] = 'mollie-payments/subscription/webhook';
            }
        );

        Event::on(
            Transaction::class,
            MolliePayments::EVENT_AFTER_TRANSACTION_UPDATE,
            function(TransactionUpdateEvent $event) {
                $transaction = $event->transaction;
                $element = $event->element;
                try {
                    if (get_class($element) === \studioespresso\molliepayments\elements\Subscription::class) {
                        if ($transaction->status === PaymentStatus::STATUS_EXPIRED) {
                            $element->subscriptionStatus = "expired";
                            Craft::$app->getElements()->saveElement($element);
                        }
                    }
                } catch (\Throwable $e) {
                    Craft::error($e->getMessage(), __CLASS__);
                }
            }
        );

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
            /** @var CraftVariable $variable */
            $variable = $e->sender;

            // Attach a behavior:
            $variable->attachBehaviors([
                CraftVariableBehavior::class,
            ]);
        });

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('mollie', MollieVariable::class);
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = Payment::class;
            }
        );

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
            /** @var CraftVariable $variable */
            $variable = $e->sender;
            $variable->attachBehaviors([
                CraftVariableBehavior::class,
            ]);
        });
    }

    public function getCpNavItem(): array
    {
        $subNavs = [];
        $navItem = parent::getCpNavItem();
        $navItem['label'] = self::getInstance()->getSettings()->pluginLabel;

        $subNavs['payments'] = [
            'label' => Craft::t('mollie-payments', 'Payments'),
            'url' => 'mollie-payments',
        ];

        $subNavs['subscriptions'] = [
            'label' => Craft::t('mollie-payments', 'Subscriptions'),
            'url' => 'mollie-payments/subscriptions',
        ];

        $subNavs['subscribers'] = [
            'label' => Craft::t('mollie-payments', 'Subscribers'),
            'url' => 'mollie-payments/subscribers',
        ];

        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $subNavs['forms'] = [
                'label' => Craft::t('mollie-payments', 'Forms'),
                'url' => 'mollie-payments/forms',
            ];
        }

        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $subNavs['settings'] = [
                'label' => Craft::t('mollie-payments', 'Settings'),
                'url' => 'mollie-payments/settings',
            ];
        }

        $navItem = array_merge($navItem, [
            'subnav' => $subNavs,
        ]);
        return $navItem;
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('mollie-payments/settings'));
    }

    // Protected Methods
    // =========================================================================
    protected function createSettingsModel(): Model
    {
        return new Settings();
    }
}
