<?php
/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\altNotification;

use humhub\modules\space\models\Membership;
use Yii;
use yii\base\ModelEvent;

class Events
{

    public static function onSpaceMembershipBeforeInsert(ModelEvent $event)
    {
        /** @var Membership $membership */
        $membership = $event->sender;
        $space = $membership?->space;

        if (!$space) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('alt-notification');
        $spaceGuids = $module->configuration->newContentNotifSpaceGuids;

        // If the Space is in **Module Settings**, auto-add it to their **User Settings**.
        if (in_array($space->guid, $spaceGuids, true)) {
            Yii::$app->notification->setSpaceSetting($membership->user, $space, true);
        }
    }
}
