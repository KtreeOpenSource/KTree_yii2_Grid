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
use yii\web\NotFoundHttpException;

class DeleteFilters extends Action
{
    public function init()
    {
        parent::init();
    }

    /**
    * Deletes an existing UserListPreference model.
    * If deletion is successful, the browser will be redirected to the 'index' page.
    * @return mixed
    */
    public function run()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findUserListPreferenceModel($id);
        if ($model->delete()) {
            return \yii\helpers\Json::encode(
              [
                  'status'=> 200,
                  'id' =>  $model->id,
                  'message'=> \Yii::t('app', 'Filter Record Deleted Successfully.')
              ]
          );
        }
        return \yii\helpers\Json::encode(
              [
                  'status'=> 500,
                  'message'=> \Yii::t('app', 'Delete error')
              ]
          );
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
