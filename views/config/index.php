<?php
/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\altNotification\models\Configuration;
use humhub\modules\altNotification\Module;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/**
 * @var $this View
 * @var $model Configuration
 */

$module = Module::getInstance();
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= $module->getName() ?></strong>

        <div class="text-body-secondary">
            <?= $module->getDescription() ?>
        </div>
    </div>

    <div class="panel-body">

        <div class="alert alert-info" role="alert">
            <p><?= Yii::t('AltNotificationModule.config', 'This module replaces the "{fieldName}" Notification settings, which is hidden by this module.', [
                    'fieldName' => Button::asLink(Yii::t('NotificationModule.base', 'Receive \'New Content\' Notifications for the following spaces'))->link(['/notification/admin/defaults']),
                ]) ?></p>
        </div>

        <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>
        <?= $form->field($model, 'notifyForAllSpaces')->checkbox() ?>
        <?= $form->field($model, 'newContentNotifSpaceGuids')->widget(SpacePickerField::class, [
            'maxSelection' => 50,
        ]) ?>

        <?= Button::save()->submit() ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        const $notifyForAllSpacesCheckbox = $('#<?= Html::getInputId($model, 'notifyForAllSpaces') ?>');
        const $newContentNotifSpaceGuidsPicker = $('#<?= Html::getInputId($model, 'newContentNotifSpaceGuids') ?>');
        const toggleNewContentNotifSpaceGuidsPicker = function () {
            if ($notifyForAllSpacesCheckbox.prop('checked')) {
                $newContentNotifSpaceGuidsPicker.parent().hide();
            } else {
                $newContentNotifSpaceGuidsPicker.parent().show();
            }
        };
        toggleNewContentNotifSpaceGuidsPicker();
        $notifyForAllSpacesCheckbox.on('change', function () {
            toggleNewContentNotifSpaceGuidsPicker();
        });
    })

</script>
