<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>


<?php $form = ActiveForm::begin(
    [
        'action' => [\Yii::$app->controller->action->id],
        'method' => 'get',
        'options' => ['data-pjax' => true, 'id' => $grid . '-search','class'=>'advance-search-form']
    ]
); ?>
<?php

$availableFilters
    = '
        <div class="box box-info panel-one-column">
         <div class="box-header-border">
                 <h3 class="box-title">Available Filters</h3>
            </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
    ';

foreach ($fields as $key => $field) {
    if ($field->attribute != '') {
        $field->isAdvanceFilter = true;
        $data = $field->renderFilterCell();
        $matches = array();

        $showIn = ((!empty($searchParams[$field->attribute]) || is_numeric($searchParams[$field->attribute])) ||
            (property_exists($field, 'isUsedInFilters') && $field->isUsedInFilters))
            ? 'appliedFilters' : 'availableFilters';
        $$showIn
            .= '
        <div class="form-group">
        <label class="control-label" id="advance_filter_label">' . $field->renderHeaderCell() . '</label>
        ' . $data . '
        </div>
        ';
    }
}
if ($appliedFilters) {
    echo '
      <div class="box box-info panel-one-column">
       <div class="box-header-border">
               <h3 class="box-title">Applied Filters</h3>
          </div>
      <div class="box-body">
          <div class="row">
              <div class="col-md-12">
  '.$appliedFilters . '</div></div></div></div>';
}
echo  $availableFilters . '</div></div></div></div>';
?>

<?php $queryParams = Yii::$app->request->queryParams; ?>
<input type="hidden" name="user_list_preference"
       value="<?php echo isset($queryParams['user_list_preference']) ? $queryParams['user_list_preference'] : ''; ?>"/>

<input type="hidden" name="is_user_list_preference" value="0"/>
<div class="form-group">
    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Reset', [\Yii::$app->controller->action->id], ['class' => 'btn btn-default grid-advance-search-form-reset']) ?>

</div>
<?php ActiveForm::end(); ?>

<div class="clear"></div>
