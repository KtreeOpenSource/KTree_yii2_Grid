<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\helpers\Html;
use ktree\grid\SelectAsset;

SelectAsset::register($this);
?>

<?php
$userPreferenceList = ArrayHelper::map($userListPreference, 'id', 'title');
$deleteUrl = \yii\helpers\Url::toRoute(
    [
        '/'. Yii::$app->controller->id .'/delete-filters',
    ]
);
$userPreference=isset(Yii::$app->request->queryParams['user_list_preference'])?Yii::$app->request->queryParams['user_list_preference']:'';
$userPreferenceID=isset(Yii::$app->request->queryParams['user_list_preference_id'])?Yii::$app->request->queryParams['user_list_preference_id']:'';

echo Html::dropDownList(
    'user_list_preference',
    ($userPreference) ? $userPreference : $userPreferenceID,
    $userPreferenceList,
    [
      'prompt' => Yii::t('app', 'Select ListView'),
      'class' => 'col-xs-4 form-control user_list_preference user_list_preference_search',
      'id' => $grid . '_user_list_preference',
    ]
  );
?>
