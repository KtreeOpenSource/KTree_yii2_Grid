<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
use ktree\grid\FilterAsset;

FilterAsset::register($this);

$modelClass = (new ReflectionClass($searchModel))->getShortName();

$queryParams = Yii::$app->request->queryParams;
$searchParams = '';
if (isset(Yii::$app->controller->filterQueryParams(
    $modelClass,
    $queryParams)[$modelClass])) {
    $searchParams = Yii::$app->controller->filterQueryParams(
        $modelClass,
        $queryParams)[$modelClass];
}
?>

<div class="advance-serch-form-block">
<?php
if (isset($queryParams[$modelClass]) && !empty($searchParams)) {
    ?>
    <div class="form-group">
        <?= $this->render(
            'saveFilterForm',
            [
                'searchParams' => $searchParams,
                'searchModelClass' => $modelClass,
                'grid' => $grid
            ]
        ); ?>
    </div>
<?php
} ?>

<div class="admin-grid-search" id="dynamic-advance-search-form">
    <?= $this->render(
        '_advanceSearchForm',
        [
            'fields' => $fields,
            'searchModelClass' => $modelClass,
            'grid' => $grid,
            'searchParams' => $searchParams,
        ]
    ); ?>
</div>
</div>
<div class="clear"></div>
