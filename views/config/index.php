<?php
/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\altNotification\models\Configuration;
use humhub\modules\altNotification\Module;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;

/**
 * @var $this View
 * @var $model Configuration
 */

/** @var Module $module */
$module = Yii::$app->getModule('alt-notification');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= $module->getName() ?></strong>

        <div class="help-block">
            <?= $module->getDescription() ?>
        </div>
    </div>

    <div class="panel-body">

        <div class="alert alert-info">
            <p><?= Yii::t('AltNotificationModule.config', 'This module replaces the "{fieldName}" Notification settings, which means that you can leave it empty.', [
                'fieldName' => Button::asLink(Yii::t('NotificationModule.base', 'Receive \'New Content\' Notifications for the following spaces'))->link(['/notification/admin/defaults']),
            ]) ?></p>
        </div>

        <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>
        <?= $form->field($model, 'newContentNotifSpaceGuids')->widget(SpacePickerField::class, [
            'maxSelection' => 50,
        ]) ?>

        <?= Button::save()->submit() ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>
