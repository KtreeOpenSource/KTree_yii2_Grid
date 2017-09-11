<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use ktree\grid\models\UserListPreference;

$queryParams = Yii::$app->request->queryParams;
$userPreferenceListId = isset($queryParams['user_list_preference']) ?
    $queryParams['user_list_preference'] :
    (isset($queryParams['user_list_preference_id']) ? $queryParams['user_list_preference_id'] : '');



$filterPreferenceModel = ($userPreferenceListId)
    ? UserListPreference::findOne($userPreferenceListId)
    : new UserListPreference();
if (!$filterPreferenceModel) {
    $filterPreferenceModel = new \ktree\grid\models\UserListPreference();
}
$userId = ($filterPreferenceModel->user_id != null) ? $filterPreferenceModel->user_id : Yii::$app->user->id;
Modal::begin(
    [
        'options' => [
            'id' => 'save_filters_form',
            'tabindex' => false
        ],
        'clientOptions' => [
            'backdrop' => 'static',
            'keyboard' => false,
        ],
        'header' => '<h4 style="margin:0; padding:0">' . Yii::t('app', 'Save Filters') . '</h4>',
        'toggleButton' => [
            'label' =>
                '<span class="glyphicon"></span>  ' . Yii::t('app', 'Save Filters'),
            'class' => 'pull-right btn btn-primary',
            'id' => 'save_filters_form-button'
        ],
    ]
);
?>
<div class="save-filter-form">
    <div class="box">
        <div class="box-body">
            <div class="form">

                <?php $form = ActiveForm::begin(
                    [
                        'id' => 'save-filter-form',
                        'action' => \yii\helpers\Url::toRoute(
                                [
                                    '/' . Yii::$app->controller->id .'/save-filters',
                                    'id' => $userPreferenceListId
                                ]
                            ),
                        'enableAjaxValidation' => true,
                        'validationUrl' => \yii\helpers\Url::toRoute(
                                [
                                    '/'. Yii::$app->controller->id .'/validate-save-filters',
                                    'id' => $userPreferenceListId,
                                ]
                            ),
                    ]
                ); ?>
                <?= $form->field($filterPreferenceModel, 'title')->textInput(['class' => 'form-control','maxlength' => 225]) ?>

                <?=
                $form->field($filterPreferenceModel, 'model')
                    ->textInput(['value' => $searchModelClass, 'type' => 'hidden'])
                    ->label(false);
                ?>

                <?=
                $form->field($filterPreferenceModel, 'user_id')
                    ->dropDownList(
                        [0 => Yii::t('app', 'Public'), Yii::$app->user->id => Yii::t('app', 'Private')],
                        ['prompt' => Yii::t('app', '-- Please Select --')]
                    );
                ?>
                <?=
                $form->field($filterPreferenceModel, 'grid_id')
                    ->textInput(['value' => $grid, 'type' => 'hidden'])
                    ->label(false);
                ?>
                <?=
                $form->field($filterPreferenceModel, 'filters')
                    ->textInput(['value' => json_encode($searchParams), 'type' => 'hidden'])
                    ->label(false);
                ?>

                <div class="form-group">
                    <?=
                    Html::submitButton(
                        $filterPreferenceModel->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
                        [
                            'class' => 'btn btn-primary',
                            'id' => 'submit-savefilters-form'
                        ]
                    ) ?>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
            <!-- form -->
        </div>
    </div>
</div>
<?php Modal::end(); ?>
