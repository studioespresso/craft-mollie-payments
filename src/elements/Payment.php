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

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\enums\Color;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use studioespresso\molliepayments\actions\ExportAllPaymentsAction;
use studioespresso\molliepayments\actions\ExportPaymentAction;
use studioespresso\molliepayments\elements\db\PaymentQuery;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\MolliePayments;
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
    public static function pluralDisplayName(): string
    {
        return Craft::t('mollie-payments', 'Payments');
    }

    /**
     * @inheritDoc
     */
    public static function lowerDisplayName(): string
    {
        return Craft::t('mollie-payments', 'payment');
    }

    /**
     * @inheritDoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('mollie-payments', 'payments');
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

    public function getCpStatusItem()
    {
        return Cp::componentStatusLabelHtml($this);
    }

    /**
     * Retuns the elements first attribute in the CP, for pre 3.2 installs
     * See getUiLabel for > 3.2
     * @return string
     */
    public function __toString(): string
    {
        if ($this->email) {
            return (string)$this->email;
        }
        return (string)$this->id;
    }

    public function getUiLabel(): string
    {
        return $this->email ?: Craft::t("mollie-payments", 'Cart') . ' ' . $this->id;
    }


    public static function statuses(): array
    {
        return [
            'cart' => ['label' => Craft::t('mollie-payments', 'In Cart'), 'color' => Color::Gray],
            'pending' => ['label' => Craft::t('mollie-payments', 'Pending'), 'color' => Color::Orange],
            'free' => ['label' => Craft::t('mollie-payments', 'Free'), 'color' => Color::Blue],
            'paid' => ['label' => Craft::t('mollie-payments', 'Paid'), 'color' => Color::Green],
            'expired' => ['label' => Craft::t('mollie-payments', 'Expired'), 'color' => Color::Red],
            'refunded' => ['label' => Craft::t('mollie-payments', 'Refunded'), 'color' => Color::Gray],
        ];
    }

    public function getStatus(): ?string
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

    public function getForm()
    {
        return MolliePayments::getInstance()->forms->getFormByid($this->formId);
    }

    public function getTransaction()
    {
        return MolliePayments::getInstance()->transaction->getTransactionbyPayment($this->id);
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
            'label' => Craft::t('app', 'All'),
        ];
        $forms = MolliePayments::getInstance()->forms->getAllFormsByType(PaymentFormModel::TYPE_PAYMENT);

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
            ExportPaymentAction::class,
            ExportAllPaymentsAction::class,
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'dateCreated' => \Craft::t('app', 'Date created'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'email' => Craft::t('mollie-payments', 'Email'),
            'amount' => Craft::t('mollie-payments', 'Amount'),
            'status' => Craft::t('mollie-payments', 'Status'),
            'dateCreated' => Craft::t('mollie-payments', 'Date Created'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['email'];
    }



    // Public Methods
    // =========================================================================
    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl("mollie-payments/payments/" . $this->uid);
    }

    /**
     * @inheritDoc
     */
    public function canView(User $user): bool
    {
        if ($user->can("accessPlugin-mollie-payments")) {
            return true;
        }
        return false;
    }

    public function canDelete(User $user): bool
    {
        if ($user->can("accessPlugin-mollie-payments")) {
            return true;
        }
        return false;
    }


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['email'], 'string'];
        if ($this->getScenario() != Element::SCENARIO_ESSENTIALS) {
            $rules[] = [['email', 'amount'], 'required'];
        }
        return $rules;
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
    public function afterSave(bool $isNew): void
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

    public function afterDelete(): void
    {
        \Craft::$app->db->createCommand()
            ->delete(PaymentRecord::tableName(), ['id' => $this->id])
            ->execute();
    }
}
