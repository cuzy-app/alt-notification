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
    public const USERS_BATCH_SIZE = 500;

    public SettingsManager $settingsManager;

    public array|string $newContentNotifSpaceGuids = [];
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

        $this->settingsManager->setSerialized('newContentNotifSpaceGuids', (array)$this->newContentNotifSpaceGuids);
        $this->settingsManager->set('notifyForAllSpaces', $this->notifyForAllSpaces);

        $this->updateAllUsersNotificationSettings();

        return true;
    }

    public function getNewContentNotifSpaceGuids(): array
    {
        return $this->notifyForAllSpaces
            ? Space::find()
                ->select('guid')
                ->where(['status' => Space::STATUS_ENABLED])
                ->column()
            : (array)$this->newContentNotifSpaceGuids;
    }

    /**
     * Whether the users becoming members of the space should be notified about its new content
     * (no query when all the spaces are selected: the space only has to be enabled)
     */
    public function isSpaceSelectedForNewContentNotif(Space $space): bool
    {
        // A space being created has no status yet on the object when its creator is added as a member:
        // it gets the column default (enabled) in the database
        return $this->notifyForAllSpaces
            ? (int)($space->status ?? Space::STATUS_ENABLED) === Space::STATUS_ENABLED
            : in_array($space->guid, (array)$this->newContentNotifSpaceGuids, true);
    }

    /**
     * Add all Spaces the User is a member of, from **Module Settings**, to their **User Settings**.
     * Users are processed by batches: one membership query per batch instead of one per user,
     * and the selected spaces are resolved once.
     */
    private function updateAllUsersNotificationSettings(): void
    {
        $selectedSpaceGuids = $this->getNewContentNotifSpaceGuids();

        /** @var User[] $users */
        foreach (User::find()->active()->batch(self::USERS_BATCH_SIZE) as $users) {
            $userIds = array_map(static fn(User $user) => $user->id, $users);

            // [user id => guids of the spaces the user is a member of]
            $membershipRows = Membership::find()
                ->select(['space_membership.user_id', 'space.guid'])
                ->innerJoin('space', 'space.id = space_membership.space_id')
                ->where(['space_membership.user_id' => $userIds, 'space_membership.status' => Membership::STATUS_MEMBER])
                ->asArray()
                ->all();
            $spaceGuidsByUserId = [];
            foreach ($membershipRows as $row) {
                $spaceGuidsByUserId[(int)$row['user_id']][] = $row['guid'];
            }

            foreach ($users as $user) {
                if (NotificationManager::isTouchedSettings($user)) {
                    continue;
                }
                Yii::$app->notification->setSpaces(
                    array_values(array_intersect($selectedSpaceGuids, $spaceGuidsByUserId[$user->id] ?? [])),
                    $user,
                );
            }
        }
    }
}
