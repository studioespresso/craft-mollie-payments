<?php

namespace studioespresso\molliepayments\services;

use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\mail\Message;
use craft\web\View;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\SubscriberRecord;

class Mail extends Component
{
    public function sendSubscriptionAccessEmail(SubscriberRecord $record)
    {
        $message = new Message();
        $message->setTo($record->email);

        $params = [
            "customer" => $record,
            "link" => UrlHelper::siteUrl(MolliePayments::getInstance()->getSettings()->manageSubscriptionRoute, ['subscriber' => $record->uid])
        ];

        if(MolliePayments::getInstance()->getSettings()->manageSubscriptionEmailPath) {
            $template = \Craft::$app->getView()->renderTemplate(MolliePayments::getInstance()->getSettings()->manageSubscriptionEmailPath, $params, View::TEMPLATE_MODE_SITE);
        } else {
            $template = \Craft::$app->getView()->renderTemplate('mollie-payments/_subscription/_email', $params, View::TEMPLATE_MODE_CP);
        }
        $message->setHtmlBody($template);
        // TODO translate this
        $message->setSubject("Manage your subscription");
        return \Craft::$app->getMailer()->send($message);
    }
}
