<?php

namespace studioespresso\molliepayments\models;

use Craft;
use craft\base\Model;
use craft\db\Query;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\models\AssetTransform;
use craft\validators\HandleValidator;
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

    public function rules()
    {
        return [
            [['title', 'handle', 'currency'], 'required'],
            [['title', 'handle', 'currency', 'descriptionFormat'], 'safe'],
            ['handle', 'validateHandle'],
        ];
    }

    public function validateHandle() {

        $validator = new HandleValidator();
        $validator->validateAttribute($this, 'handle');
        $data = MolliePayments::getInstance()->forms->getFormByHandle($this->handle);
        if($data && $data->id != $this->id) {
            $this->addError('handle', Craft::t('mollie-payments', 'Handle "{handle}" is already in use', ['handle' => $this->handle]));

        }

    }


}