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

use craft\helpers\UrlHelper;
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

    public static function statuses(): array
    {
        return [
            'pending' => ['label' => Craft::t('mollie-payments', 'Pending'), 'color' => '27AE60'],
            'paid' => ['label' => Craft::t('mollie-payments', 'Paid'), 'color' => '055900'],
            'expired' => ['label' => Craft::t('mollie-payments', 'Expired'), 'color' => 'b30d0f'],
        ];
    }

    public function getStatus()
    {
        $status = MolliePayments::getInstance()->transaction->getStatusForPayment($this->id);
        if ($status == 'paid') {
            return 'paid';
        }

        return 'pending';
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
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
//                'badgeCount' => count(Payment::findAll(['formId' => $form['id']])),
                'criteria' => [
                    'formId' => $form['id'],
                ],
                'defaultSort' => ['dateCreated', 'desc'],
            ];
        }

        return $sources;
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
            'id' => Craft::t('mollie-payments', 'ID'),
            'email' => Craft::t('mollie-payments', 'Email'),
            'amount' => Craft::t('mollie-payments', 'Amount'),
            'status' => Craft::t('mollie-payments', 'Status'),
        ];
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
            ['amount', 'integer'],
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
                    'status' => $this->status,
                    'amount' => $this->amount,
                    'formId' => $this->formId,
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(PaymentRecord::tableName(), [
                    'email' => $this->email,
                    'status' => $this->status,
                    'amount' => $this->amount,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    



}
