<?php

/**
 * Alt Notification
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\altNotification\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\altNotification\Module;
use Yii;

class ConfigController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        /** @var Module $module */
        $module = $this->module;
        $model = $module->getConfiguration();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->view->saved();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
