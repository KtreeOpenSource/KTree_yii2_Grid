<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="col-md-4">
  <?php $form = ActiveForm::begin(
      [
          'action' => $formUrl,
          'method' => 'post',
          'options' => ['data-pjax' => true,'id' => $grid.'-search','class' => 'grid-global-search'],
          'fieldConfig' => ['template' => "{label}\n{hint}\n{error}\n<div class='right-inner-addon'><i class='fa fa-search'></i>{input}</div>\n"]
      ]
  ); ?>

  <?=
  $form->field($searchModel, $fields)->textInput(
      ['placeholder' => 'Search', 'class' => 'form-control '.$grid.'_search']
  )->label(false); ?>

  <?php ActiveForm::end();?>
</div>
