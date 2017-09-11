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

class SaveFilters extends Action
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
        $postData = Yii::$app->request->post();
        $filters = json_decode($postData['UserListPreference']['filters'], true);
        $filters = array_filter($filters, function ($dataFilters) {
            return ($dataFilters !== null && $dataFilters !== false && $dataFilters !== '');
        });
        foreach ($filters as $key=>$value) {
            if (is_array($value) && array_key_exists('start', $value) && !$value['start'] && !$value['end']) {
                unset($filters[$key]);
            }
        }
        $postData['UserListPreference']['filters'] = json_encode($filters);

        if ($model->load($postData) && $model->save()) {
            return \yii\helpers\Json::encode(
                  [
                      'status'=> 200,
                      'id' =>  $model->id,
                      'text' => $model->title,
                      'message'=> \Yii::t('app', 'Search Filter Saved Successfully.')
                  ]
                );
        }
        return \yii\helpers\Json::encode(
        [
            'status'=> 500,
            'message'=> \Yii::t('app', 'creation failed.')
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
