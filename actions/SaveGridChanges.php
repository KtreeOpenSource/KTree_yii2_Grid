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

class SaveGridChanges extends Action
{
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        try {
            if (Yii::$app->request->isAjax) {
                $postData = Yii::$app->request->post();
                $userId = Yii::$app->user->id;
                $return = [];
                $userGridModel = UserGridPreferences::find()->where(
                ['user_id' => $userId, 'entity' => $postData['entity'], 'grid_id' => $postData['gridId']]
            )->one();
                $userGridModel = ($userGridModel) ? $userGridModel : new UserGridPreferences();
                $userGridModel->user_id = $userId;
                $userGridModel->entity = $postData['entity'];
                $userGridModel->grid_id = $postData['gridId'];
                $userPreference = Json::decode($userGridModel->columns, true);
                $userColumns = isset($userPreference['columns']) ?
                json_decode($userPreference['columns']) : [];


                $columns = [];
                $columns['display-mode'] = ($postData['displayMode']) ? $postData['displayMode']
                : (($userPreference) ? $userPreference['display-mode'] : '');
                $columns['per-page'] = ($postData['perPage']) ? $postData['perPage']
                : (($userPreference) ? $userPreference['per-page'] : '');
                $columns['group-by'] = (isset($postData['groupBy'])) ? $postData['groupBy']
                : (($userPreference) ? $userPreference['group-by'] : '');
                if ($userColumns) {
                    $columns['columns'] = ($userColumns) ? Json::encode($userColumns) : '';
                }
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
