<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid\actions;

use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\helpers\Json;
use ktree\grid\models\UserListPreference;

class ValidateSaveFilters extends Action
{
    public function init()
    {
        parent::init();
    }

    /**
     * Updates an existing UserListPreference model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function run()
    {
        $id = Yii::$app->request->queryParams['id'];
        $model = ($id) ?  $this->findUserListPreferenceModel($id) : new UserListPreference();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return \yii\helpers\Json::encode([]);
        }
        return \yii\helpers\Json::encode($model->errors);
    }

    /**
     * Finds the UserListPreference model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserListPreference the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findUserListPreferenceModel($id)
    {
        $model = UserListPreference::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }
}
