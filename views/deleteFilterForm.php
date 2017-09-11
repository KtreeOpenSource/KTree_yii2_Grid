<?php
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use ktree\grid\FilterAsset;

FilterAsset::register($this);

$userPreferenceListId = (Yii::$app->request->queryParams['user_list_preference'])
    ? Yii::$app->request->queryParams['user_list_preference']
    :
    Yii::$app->request->queryParams['user_list_preference_id'];

$filterPreferenceModel = new \ktree\grid\models\UserListPreference();
$userId = ($filterPreferenceModel->user_id != null) ? $filterPreferenceModel->user_id : Yii::$app->user->id;
Modal::begin(
    [
        'options' => [
            'id' => 'delete_filters_form',
            'tabindex' => false 
        ],
        'clientOptions' => [
            'backdrop' => 'static',
            'keyboard' => false,
        ],
        'header' => '<h4 style="margin:0; padding:0">' . Yii::t('app', 'Delete Filters') . '</h4>',
        'toggleButton' => [
            'label' =>
                '<span class="glyphicon"></span>  ' . Yii::t('app', 'Delete Filters') . ' </i>',
            'class' => 'btn btn-primary',
            'id' => 'delete_filters_form-button'
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
                        'id' => 'delete-filter-form',
                        'action' => \yii\helpers\Url::toRoute(
                                [
                                    '/' . Yii::$app->controller->id . '/delete-filters',
                                    'id' => $userPreferenceListId
                                ]
                            ),
                        'enableAjaxValidation' => true,
                        'validationUrl' => \yii\helpers\Url::toRoute(
                                [
                                    '/' . Yii::$app->controller->id . '/validate-delete-filters',
                                    'id' => $userPreferenceListId,
                                ]
                            ),
                    ]
                ); ?>

                <?=
                $form->field($filterPreferenceModel, 'title')
                    ->dropDownList(
                        $availableLists,
                        ['prompt' => Yii::t('app', '-- Please Select --')] // options
                    );
                ?>


                <div class="form-group">
                    <?=
                    Html::Button(
                        Yii::t('app', 'Delete'),
                        [
                            'class' => 'btn btn-primary',
                            'id' => 'submit-deletefilters-form'
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
