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

class SaveGridEdit extends Action
{
    public function init()
    {
        parent::init();
    }

    /**
     * Saves the grid edit inline
     * @return \Yii\app\web\Response
     */
    public function run()
    {
        $return = [];
        try {
            if (Yii::$app->request->isAjax) {
                $postData = Yii::$app->request->post();
                $model = $postData['model'];
                $editGrid = $model::findOne($postData['id']);
                foreach ($editGrid->attributes as $attributeKey => $eachAttribute) {
                    $editGrid->$attributeKey = (isset($postData[$attributeKey])) ? $postData[$attributeKey]
                      : $eachAttribute;
                }
                $return['status'] = true;
                if (!($editGrid->validate() && $editGrid->save())) {
                    $return['status'] = false;
                    $return['message'] = $editGrid->errors;
                }
            }
        } catch (Exception $e) {
            $return['status'] = false;
            $return['message'] = $e->getMessage();
        }

        return $this->controller->asJson($return);
    }
}
