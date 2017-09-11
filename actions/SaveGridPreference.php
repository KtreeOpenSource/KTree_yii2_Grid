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
use ktree\grid\models\UserGridPreferences;

class SaveGridPreference extends Action
{
    public function init()
    {
        parent::init();
    }

    /**
     * Saves the grid preferences
     * @return \Yii\app\web\Response
     */
    public function run()
    {
        try {
            if (Yii::$app->request->isAjax) {
                $userId = Yii::$app->user->id;
                $request = Yii::$app->request;
                $return = [];
                $selectedGridColumns = $request->post('columns');
                $userGridModel = UserGridPreferences::find()->where(
                  ['user_id' => $userId, 'entity' => $request->post('entity'), 'grid_id' => $request->post('grid_id')]
              )->one();
                $userGridModel = ($userGridModel) ? $userGridModel : new UserGridPreferences();
                $userGridModel->user_id = $userId;
                $userGridModel->entity = $request->post('entity');
                $userGridModel->grid_id = $request->post('grid_id');
                $userPreference = Json::decode($userGridModel->columns, true);
                $columns = [];
                $columns['display-mode'] = ($userPreference['display-mode']) ? $userPreference['display-mode'] : '';
                $columns['per-page'] = ($userPreference['per-page']) ? $userPreference['per-page'] : '';
                $columns['group-by'] = ($userPreference['group-by']
                  && in_array(
                      $userPreference['group-by'],
                      $selectedGridColumns
                  )) ? $userPreference['group-by'] : '';
                $columns['columns'] = Json::encode($selectedGridColumns);
                $userGridModel->columns = Json::encode($columns);
                $return['status'] = true;
                if (!$userGridModel->save()) {
                    $return['status'] = false;
                    $return['message'] = $userGridModel->errors;
                }
            }
        } catch (Exception $e) {
            $return['status'] = false;
            $return['message'] = $e->getMessage();
        }

        $this->controller->asJson($return);
    }
}
