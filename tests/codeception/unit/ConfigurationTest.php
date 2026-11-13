<?php

/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace tests\codeception\unit\modules\altNotification;

use humhub\modules\altNotification\Events;
use humhub\modules\altNotification\Module;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\space\MemberEvent;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Default fixtures: User2 (id 3) is a member of Spaces 1, 3 and 4; User1 (id 2) of Spaces 2, 3, 4 and 5.
 */
class ConfigurationTest extends HumHubDbTestCase
{
    public function testSpaceSelectionWithoutQueryWhenAllSpacesAreSelected(): void
    {
        $module = Module::getInstance();
        $configuration = $module->getConfiguration();
        $space1 = Space::findOne(['id' => 1]);
        $space2 = Space::findOne(['id' => 2]);

        $configuration->notifyForAllSpaces = false;
        $configuration->newContentNotifSpaceGuids = [$space1->guid];
        $this->assertTrue($configuration->isSpaceSelectedForNewContentNotif($space1));
        $this->assertFalse($configuration->isSpaceSelectedForNewContentNotif($space2));

        $configuration->notifyForAllSpaces = true;
        $this->assertTrue($configuration->isSpaceSelectedForNewContentNotif($space2));
        $space2->status = Space::STATUS_ARCHIVED;
        $this->assertFalse($configuration->isSpaceSelectedForNewContentNotif($space2));
    }

    public function testSavingTheConfigurationUpdatesTheMembersNotificationSettingsByBatches(): void
    {
        $module = Module::getInstance();
        $configuration = $module->getConfiguration();
        $space1 = Space::findOne(['id' => 1]);
        $space4 = Space::findOne(['id' => 4]);
        $user2 = User::findOne(['id' => 3]);

        $this->assertFalse(NotificationManager::isTouchedSettings($user2));

        $configuration->notifyForAllSpaces = false;
        $configuration->newContentNotifSpaceGuids = [$space1->guid, $space4->guid];
        $this->assertTrue($configuration->save());

        // User2 is a member of both selected spaces
        $this->assertSame(1, (int)Membership::findOne(['space_id' => 1, 'user_id' => 3])->send_notifications);
        $this->assertSame(1, (int)Membership::findOne(['space_id' => 4, 'user_id' => 3])->send_notifications);
        $this->assertSame(0, (int)Membership::findOne(['space_id' => 3, 'user_id' => 3])->send_notifications);
        // User1 is only a member of Space 4 among the selected spaces
        $this->assertSame(1, (int)Membership::findOne(['space_id' => 4, 'user_id' => 2])->send_notifications);
        $this->assertSame(0, (int)Membership::findOne(['space_id' => 2, 'user_id' => 2])->send_notifications);
    }

    public function testMemberAddedToASelectedSpaceGetsTheNotificationSetting(): void
    {
        $module = Module::getInstance();
        $configuration = $module->getConfiguration();
        $space2 = Space::findOne(['id' => 2]);
        $user2 = User::findOne(['id' => 3]);

        $configuration->notifyForAllSpaces = true;
        $space2->addMember($user2->id);
        Events::onSpaceMemberAdded(new MemberEvent(['space' => $space2, 'user' => $user2]));

        $this->assertSame(1, (int)Membership::findOne(['space_id' => 2, 'user_id' => 3])->send_notifications);
    }

    public function testCreatorOfANewSpaceGetsTheNotificationSettingWhenAllSpacesAreSelected(): void
    {
        $configuration = Module::getInstance()->getConfiguration();
        $configuration->notifyForAllSpaces = true;

        // The creator is added as a member in Space::afterSave(), before the status (column default) is set on the object
        $space = new Space(['name' => 'New space ' . uniqid('', true)]);
        $this->assertNull($space->status);
        $this->assertTrue($configuration->isSpaceSelectedForNewContentNotif($space));

        $space->status = (string)Space::STATUS_ENABLED;
        $this->assertTrue($configuration->isSpaceSelectedForNewContentNotif($space), 'Status read as a string');
    }
}
