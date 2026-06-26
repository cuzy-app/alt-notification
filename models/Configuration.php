<?php

/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\altNotification\models;

use humhub\components\SettingsManager;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

class Configuration extends Model
{
    public SettingsManager $settingsManager;

    public array $newContentNotifSpaceGuids = [];
    public bool $notifyForAllSpaces = false;


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['newContentNotifSpaceGuids'], 'safe'],
            [['notifyForAllSpaces'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'newContentNotifSpaceGuids' => Yii::t('AltNotificationModule.config', 'Select Spaces for which Users should be notified about new content upon becoming a member.'),
            'notifyForAllSpaces' => Yii::t('AltNotificationModule.config', 'Notify Users for all Spaces'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints(): array
    {
        return [
            'newContentNotifSpaceGuids'
            => Yii::t('AltNotificationModule.config', 'When a user joins a Space, if it is in this list, it is added to their "{fieldName}" Notification settings.', [
                'fieldName' => Yii::t('NotificationModule.base', 'By default, receive \'New Content\' Notifications for the following Spaces'),
            ]),
            'notifyForAllSpaces' => Yii::t('AltNotificationModule.config', 'By default, all Spaces receive \'New Content\' Notifications.'),
        ];
    }

    public function loadBySettings(): void
    {
        $this->newContentNotifSpaceGuids = (array)$this->settingsManager->getSerialized('newContentNotifSpaceGuids', $this->newContentNotifSpaceGuids);
        $this->notifyForAllSpaces = (bool)$this->settingsManager->get('notifyForAllSpaces', $this->notifyForAllSpaces);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->setSerialized('newContentNotifSpaceGuids', $this->newContentNotifSpaceGuids);
        $this->settingsManager->set('notifyForAllSpaces', $this->notifyForAllSpaces);

        $this->updateAllUsersNotificationSettings();

        return true;
    }

    public function getNewContentNotifSpaceGuids()
    {
        return $this->notifyForAllSpaces
            ? Space::find()
                ->select('guid')
                ->where(['status' => Space::STATUS_ENABLED])
                ->column()
            : $this->newContentNotifSpaceGuids;
    }

    private function updateAllUsersNotificationSettings()
    {
        // Add all Spaces the User is a member of, from **Module Settings**, to their **User Settings**.
        /** @var User $user */
        foreach (User::find()->active()->all() as $user) {
            if (NotificationManager::isTouchedSettings($user)) {
                continue;
            }
            $userSpaceMembershipGuids = Membership::find()
                ->joinWith('space')
                ->where(['user_id' => $user->id, 'space_membership.status' => Membership::STATUS_MEMBER])
                ->select('space.guid')
                ->column();
            Yii::$app->notification->setSpaces(array_intersect($this->getNewContentNotifSpaceGuids(), $userSpaceMembershipGuids), $user);
        }
    }
}
