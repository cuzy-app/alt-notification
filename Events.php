<?php

/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\altNotification;

use humhub\modules\notification\widgets\NotificationSettingsForm;
use humhub\modules\space\MemberEvent;
use Yii;
use yii\base\Event;

class Events
{
    public static function onSpaceMemberAdded(MemberEvent $event)
    {
        $space = $event->space;
        $user = $event->user;

        if (!$space || !$user) {
            return;
        }

        $module = Module::getInstance();

        // If the Space is in **Module Settings**, auto-add it to their **User Settings**.
        if (in_array($space->guid, $module->configuration->getNewContentNotifSpaceGuids(), true)) {
            Yii::$app->notification->setSpaceSetting($user, $space, true);
        }
    }

    public static function onNotificationSettingsFormBeforeRun(Event $event): void
    {
        /** @var NotificationSettingsForm $form */
        $form = $event->sender;

        if ($form->model->user) {
            $module = Module::getInstance();
            if (!$module->configuration->notifyForAllSpaces) {
                // Only hide for admin settings, not for user settings
                return;
            }
        }

        // Hide the Spaces picker default notification on new content created in the notification settings form, as it is replaced by the module configuration.
        $form->showSpaces = false;
    }
}
