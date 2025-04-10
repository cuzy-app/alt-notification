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
            'event' => Membership::EVENT_BEFORE_INSERT,
            'callback' => [Events::class, 'onSpaceMembershipBeforeInsert'],
        ],
    ],
    // 'urlManagerRules' => [
    //     'custom-url' => 'alt-notification/index', // when creating an URL, use Url::to(['/alt-notification/index']) and nether Url::to(['custom-url']) as it doesn't work without pretty URLs
    // ]
];
