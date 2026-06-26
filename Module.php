<?php

/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\altNotification;

use humhub\modules\altNotification\models\Configuration;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 *
 * @property-read mixed $configUrl
 * @property-read Configuration $configuration
 * @property-read string[] $notifications
 */
class Module extends \humhub\components\Module
{
    /**
     * @var string defines the icon
     */
    public $icon = 'bell';

    private ?Configuration $_configuration = null;

    public function getConfiguration(): Configuration
    {
        if ($this->_configuration === null) {
            $this->_configuration = new Configuration(['settingsManager' => $this->settings]);
            $this->_configuration->loadBySettings();
        }
        return $this->_configuration;
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/alt-notification/config']);
    }

    /**
     * @inerhitdoc
     */
    public function getName()
    {
        return Yii::t('AltNotificationModule.base', 'Alternative Notifications');
    }

    /**
     * @inerhitdoc
     */
    public function getDescription()
    {
        return Yii::t('AltNotificationModule.base', 'Replaces the "{fieldName}" Notification Settings with a new behavior.', [
            'fieldName' => Yii::t('NotificationModule.base', 'Receive \'New Content\' Notifications for the following spaces'),
        ]);
    }


    public function enable()
    {
        if (parent::enable() === false) {
            return false;
        }

        // Copy all Spaces from **Admin Settings** to **Module Settings**.
        /** @var \humhub\modules\notification\Module $notificationModule */
        $notificationModule = Yii::$app->getModule('notification');
        $notificationSettings = $notificationModule->settings;
        $spaceGuis = (array)$notificationSettings->getSerialized('sendNotificationSpaces');
        $notificationSettings->setSerialized('sendNotificationSpaces', []);
        $this->configuration->newContentNotifSpaceGuids = $spaceGuis;
        $this->configuration->save();

        return true;
    }

    public function disable()
    {
        $spaceGuis = $this->configuration->newContentNotifSpaceGuids;

        if (parent::disable() === false) {
            return false;
        }

        // Copy all Spaces from **Module Settings** to **Admin Settings**.
        /** @var \humhub\modules\notification\Module $notificationModule */
        $notificationModule = Yii::$app->getModule('notification');
        $notificationModule->settings->setSerialized('sendNotificationSpaces', $spaceGuis);

        return true;
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function getInstance(): static
    {
        /** @var ?static $module */
        $module = Yii::$app->getModule('alt-notification');
        if (!$module?->isEnabled) {
            throw new NotFoundHttpException('Alternate Notification module not enabled');
        }
        return $module;
    }
}
