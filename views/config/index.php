<?php
/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\widgets\IncompleteSetupWarning;
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
        <?= Yii::$app->user->can(ManageSettings::class) ? Button::defaultType(Yii::t('AltNotificationModule.config', 'Administration'))
            ->link(['/alt-notification/admin/index'])
            ->style('margin-left: 6px;')
            ->right()
            ->sm() : '' ?>

        <strong><?= $module->getName() ?></strong>

        <div class="help-block">
            <?= $module->getDescription() ?>
        </div>
    </div>

    <div class="panel-body">

        <?= IncompleteSetupWarning::widget() ?>

        <div class="alert alert-info">
            <p><?= Yii::t('AltNotificationModule.config', '') ?></p>
        </div>

        <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>
        <?= $form->field($model, 'newContentNotifSpaceGuids')->widget(SpacePickerField::class, [
            'maxSelection' => 50,
        ]) ?>

        <?= Button::save()->submit() ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>
