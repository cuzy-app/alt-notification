<?php
/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\altNotification;

use humhub\modules\altNotification\models\Configuration;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

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

        // Add all Spaces the User is a member of, from **Module Settings**, to their **User Settings**.
        foreach (User::find()->active()->all() as $user) {
            if ($notificationSettings->user($user)?->get(NotificationManager::IS_TOUCHED_SETTINGS)) {
                continue;
            }
            $userSpaceMembershipGuids = Membership::find()
                ->joinWith('space')
                ->where(['user_id' => $user->id, 'space_membership.status' => Membership::STATUS_MEMBER])
                ->select('space.guid')
                ->column();
            Yii::$app->notification->setSpaces(array_intersect($spaceGuis, $userSpaceMembershipGuids), $user);
        }

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
    }
}
