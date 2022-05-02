<?php

namespace studioespresso\molliepayments\models;

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\models\AssetTransform;
use craft\validators\HandleValidator;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\seofields\SeoFields;

class PaymentFormModel extends Model
{
    public $title;

    public $id;

    public $handle;

    public $currency;

    public $descriptionFormat;

    public $fieldLayout;

    public $fieldLayoutId;

    public $uid;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['fieldLayout'] = [
            'class' => FieldLayoutBehavior::class,
            'elementType' => Payment::class,
        ];
        return $behaviors;
    }

    public function rules(): array
    {
        return [
            [['title', 'handle', 'currency'], 'required'],
            [['title', 'handle', 'currency', 'descriptionFormat'], 'safe'],
            ['handle', 'validateHandle'],
        ];
    }

    public function validateHandle()
    {
        $validator = new HandleValidator();
        $validator->validateAttribute($this, 'handle');
        $data = MolliePayments::getInstance()->forms->validateFormHandle($this->handle);
        if ($data && $data->id != $this->id) {
            $this->addError('handle', Craft::t('mollie-payments', 'Handle "{handle}" is already in use', ['handle' => $this->handle]));

        }

    }

    public function getConfig()
    {
        $config = [
            'title' => $this->title,
            'handle' => $this->handle,
            'currency' => $this->currency,
            'descriptionFormat' => $this->descriptionFormat,
        ];

        $fieldLayout = $this->getFieldLayout();

        if ($fieldLayoutConfig = $fieldLayout->getConfig()) {
            if (!$fieldLayout->uid) {
                $fieldLayout->uid = $fieldLayout->id ? Db::uidById(Table::FIELDLAYOUTS, $fieldLayout->id) : StringHelper::UUID();
            }
            $config['fieldLayouts'] = [
                $fieldLayout->uid => $fieldLayoutConfig,
            ];
        }

        return $config;
    }


}