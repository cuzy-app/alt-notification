<?php
/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\altNotification\Events;
use humhub\modules\space\models\Membership;

return [
    'id' => 'alt-notification',
    'class' => humhub\modules\altNotification\Module::class,
    'namespace' => 'humhub\modules\altNotification',
    'events' => [
        [
            'class' => Membership::class,
            'event' => Membership::EVENT_MEMBER_ADDED,
            'callback' => [Events::class, 'onSpaceMemberAdded'],
        ],
    ],
];
