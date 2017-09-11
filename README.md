Ktree Grid module for Yii2
========================

<Grid extension> provides a set of commonly used features like advance search, inline edit, custom girdview and card view of data, sorting, manage columns, group by, paging and also filtering the data etc

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require ktree/grid "*"
```

or add

```json
"ktree/grid": "*"
```

to the require section of your composer.json.

run the command composer.install or composer.update


Configuration
-----------------------

**Database Migrations**

Before using ktree grid, we'll also need to prepare the database.
```php
"php yii migrate --migrationPath=@vendor/ktree/grid/migrations"
```

**Controller behaviour setup**

Configure the following for details regards to simple access control which will check a list of access rules to determine if the user is allowed to access the requested action.

```php
'access' => [
    'class' => AccessControl::className(),
    'only' => ['create','update','index','delete','save-grid-preference', 'save-grid-changes', 'save-grid-edit','delete-filters','save-filters','validate-save-filters'],
    'rules' => [
        [
            'actions' => ['create','update','index','delete','save-grid-preference', 'save-grid-changes', 'save-grid-edit','delete-filters','save-filters','validate-save-filters'],
            'allow' => true,
            'roles' => ['@'],
        ],
    ],
],
```

The below behavior defines how to access the filterqueryparams function, you need to add this to your controller behavior.

```php
'GridFilterQueryBehaviour' => [
    'class' => \ktree\grid\behaviours\GridFilterQueryBehaviour::className()
],
```

**Controller actions setup**

The below code defines how to access the grid actions like SaveGridChanges, SaveGridEdit, SaveGridPreference, DeleteFilters,SaveFilters you need to add this to your controller actions function:

```php
  'save-grid-changes' => [
      'class' => 'ktree\grid\actions\SaveGridChanges',
  ],
  'save-grid-edit' => [
      'class' => 'ktree\grid\actions\SaveGridEdit',
  ],
  'save-grid-preference' => [
      'class' => 'ktree\grid\actions\SaveGridPreference',
  ],
  'delete-filters' => [
      'class' => 'ktree\grid\actions\DeleteFilters',
  ],
  'save-filters' => [
      'class' => 'ktree\grid\actions\SaveFilters',
  ],
  'validate-save-filters' => [
      'class' => 'ktree\grid\actions\ValidateSaveFilters',
  ]
```

**Controller index action setup**

You can declare filters in a controller action by overriding its behavior like the following. You can configure the filterQueryParams to set in the index action.

```php
public function actionIndex()
{
    $searchModel = new PageSearch();
    $queryParams = $this->filterQueryParams('PageSearch', Yii::$app->request->queryParams);
    $dataProvider = $searchModel->search($queryParams);
    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}
```

## Usage
```php
use ktree\grid\GridView;
<?php Pjax::begin([
                'id' => 'pjax-page-filtering',
                'timeout' => false,
                'enablePushState' => false,
                'clientOptions' => [
                      'method' => 'GET'
                 ]
            ]);

$gridColumns = [
    ['class' => '\ktree\grid\CheckboxColumn'],
    [
        'attribute' => 'title',
        'isEditable' => true,
        'pk' => 'id',
        'contentOptions' => ['editable-key' => 'title'],
        'group' => true,
    ],
    [
        'attribute' => 'status',
        'filter' => Html::activeDropDownList(
                $searchModel,
                'status',
                [
                  Page::ACTIVE => Yii::t('app', 'Active'),
                  Page::INACTIVE => Yii::t('app', 'InActive')
                ],
                ['class' => 'form-control', 'prompt' => '']
            ),
        'isEditable' => true,
        'pk' => 'id',
        'contentOptions' => ['editable-key' => 'status'],
        'dataType' => 'select',
        'source' =>[
          Page::ACTIVE => Yii::t('app', 'Active'),
          Page::INACTIVE => Yii::t('app', 'InActive')
        ],
        'displayDbValue' => true,
        'group' => true,
    ],
    [
        'class' => '\ktree\grid\ActionColumn',
        'template' => '{update}{view}{delete}',
    ],
    ['class' => '\ktree\grid\CheckboxColumn']
];
echo GridView::widget([
  dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'options' => ['id' => 'pages_admin_grid'],
  'primaryKey' => 'id',
  'title' => Yii::t('app', 'Page'),
  'model' => $dataProvider->query->modelClass,
  'pageSize' => true,
  'manageColumns' => true,
  'inlineEdit' => Url::toRoute(['update']),
  'cardView' => [
      'template' => '_card'
  ],
  'advanceSearch' => true,
  'columns' => $gridColumns
]);

Pjax::end(); ?>
```
