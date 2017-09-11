<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
use yii\bootstrap\Modal;

Modal::begin(
    [
        'options' => [
            'id' => 'advance-search_form',
            'tabindex' => false
        ],
        'clientOptions' => [
            'backdrop' => 'static',
            'keyboard' => false,
        ],
        'header' => '<h4 style="margin:0; padding:0">' . Yii::t('app', 'Advance Search') . '</h4>',
        'toggleButton' => [
            'label' =>
                '<span class="glyphicon glyphicon-filter"></span>',
            'class' => 'btn btn-primary',
            'id' => 'advance-search_form-button',
            'title' => Yii::t('app', 'Advance Search')
        ],
    ]
);
echo $this->render(
    '_globalSearch',
    [
        'fields' => $fields,
        'searchModel' => $searchModel,
        'grid' => $grid,
    ]
);
?>
<?php Modal::end(); ?>
