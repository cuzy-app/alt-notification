<?php
/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\altNotification\models;

use humhub\components\SettingsManager;
use Yii;
use yii\base\Model;

class Configuration extends Model
{
    public SettingsManager $settingsManager;

    public array $newContentNotifSpaceGuids = [];


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['newContentNotifSpaceGuids'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'newContentNotifSpaceGuids' => Yii::t('AltNotificationModule.config', 'Select Spaces for which Users should be notified about new content upon becoming a member.'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints(): array
    {
        return [
            'newContentNotifSpaceGuids' =>
                Yii::t('AltNotificationModule.config', 'When a user joins a Space, if it is in this list, it is added to their "{fieldName}" Notification settings.', [
                    'fieldName' => Yii::t('NotificationModule.base', 'Receive \'New Content\' Notifications for the following spaces'),
                ]),
        ];
    }

    public function loadBySettings(): void
    {
        $this->newContentNotifSpaceGuids = (array)$this->settingsManager->getSerialized('newContentNotifSpaceGuids', $this->newContentNotifSpaceGuids);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->setSerialized('newContentNotifSpaceGuids', $this->newContentNotifSpaceGuids);

        return true;
    }
}
