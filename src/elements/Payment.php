<?php
/**
 * Mollie Payments plugin for Craft CMS 3.x
 *
 * Easily accept payment with Mollie Payments
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2019 Studio Espresso
 */

namespace studioespresso\molliepayments\elements;

use craft\elements\actions\Restore;
use craft\helpers\UrlHelper;
use studioespresso\molliepayments\actions\DeletePaymentAction;
use studioespresso\molliepayments\actions\ExportAllPaymentsAction;
use studioespresso\molliepayments\actions\ExportPaymentAction;
use studioespresso\molliepayments\elements\db\PaymentQuery;
use studioespresso\molliepayments\MolliePayments;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use studioespresso\molliepayments\records\PaymentRecord;

/**
 * @author    Studio Espresso
 * @package   MolliePayments
 * @since     1.0.0
 */
class Payment extends Element
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $email;
    public $amount = 0;
    public $formId;
    public $paymentStatus;
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('mollie-payments', 'Payment');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * Retuns the elements first attribute in the CP, for pre 3.2 installs
     * See getUiLabel for > 3.2
     * @return string
     */
    public function __toString()
    {
        if ($this->email) {
            return (string)$this->email;
        }
        return (string)$this->id;
    }

    public function getUiLabel(): string
    {
        return $this->email;
    }


    public static function statuses(): array
    {
        return [
            'pending' => ['label' => Craft::t('mollie-payments', 'Pending'), 'color' => 'orange'],
            'free' => ['label' => Craft::t('mollie-payments', 'Free'), 'color' => 'green'],
            'paid' => ['label' => Craft::t('mollie-payments', 'Paid'), 'color' => 'green'],
            'expired' => ['label' => Craft::t('mollie-payments', 'Expired'), 'color' => 'red'],
        ];
    }

    public function getStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    public function getForm() {
        return MolliePayments::getInstance()->forms->getFormByid($this->formId);
    }

    /**
     *
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new PaymentQuery(static::class);
    }


    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources[] = [
            'key' => '*',
            'label' => 'All',
            'criteria' => ['id' => '*'],
        ];
        $forms = MolliePayments::getInstance()->forms->getAllForms();

        foreach ($forms as $form) {
            $sources[] = [
                'key' => 'form:' . $form['handle'],
                'label' => $form['title'],
                'criteria' => [
                    'formId' => $form['id'],
                ],
                'defaultSort' => ['dateCreated', 'desc'],
            ];
        }

        return $sources;
    }

    protected static function defineActions(string $source = null): array
    {
        return [
            DeletePaymentAction::class,
            ExportPaymentAction::class,
            ExportAllPaymentsAction::class
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'dateCreated' => \Craft::t('app', 'Date created'),
        ];
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('mollie-payments', 'Payments');
    }


    protected static function defineTableAttributes(): array
    {
        return [
            'email' => Craft::t('mollie-payments', 'Email'),
            'amount' => Craft::t('mollie-payments', 'Amount'),
            'status' => Craft::t('mollie-payments', 'Status'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['email'];
    }



    // Public Methods
    // =========================================================================
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl("mollie-payments/payments/" . $this->uid);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'string'],
            ['amount', 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return false;
    }


    // Events
    // -------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert(PaymentRecord::tableName(), [
                    'id' => $this->id,
                    'email' => $this->email,
                    'paymentStatus' => $this->paymentStatus,
                    'amount' => $this->amount,
                    'formId' => $this->formId,
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(PaymentRecord::tableName(), [
                    'email' => $this->email,
                    'paymentStatus' => $this->paymentStatus,
                    'amount' => $this->amount,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    public function afterDelete()
    {
        \Craft::$app->db->createCommand()
            ->delete(PaymentRecord::tableName(), ['id' => $this->id])
            ->execute();
    }


}
