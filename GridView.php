<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use ktree\grid\models\UserGridPreferences;
use ktree\grid\widgets\editable\Editable;
use Yii;
use yii\helpers\Json;
use ReflectionClass;
use yii\web\view;

/**
 * The GridView widget is used to display data in a grid.
 *
 * It provides features like [[sorter|sorting]], [[pager|paging]] and also [[filterModel|filtering]] the data.
 *
 * A basic usage looks like the following:
 *
 * ```php
 * <?= GridView::widget([
 *     'dataProvider' => $dataProvider,
 *     'columns' => [
 *         'id',
 *         'name',
 *         'created_at:datetime',
 *         // ...
 *     ],
 * ]) ?>
 * ```
 *
 * The columns of the grid table are configured in terms of [[Column]] classes,
 * which are configured via [[columns]].
 *
 * The look and feel of a grid view can be customized using the large amount of properties.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class GridView extends \yii\grid\GridView
{
    const MODE_GRID = 'grid';
    const MODE_CARD = 'card';
    public $layout = "{header}\n{items}";
    public $title = null;
    public $pageSize = false;
    public $advanceSearch = false;
    // global Search output will pass for grid.
    public $globalSearch = false;
    // Template for Grid Header which contains
    public $headerLayout = '{globalSearch}{saveSearch}{switchmode}{advanceSearch}{groupby}{manageColumns}{pagination}<div class="clear"></div>';

    public $blockContainer = 'grid-header-blk';
    /* User Prefered Active Columns */
    public $activeColumns = [];
    public $advanceSearchFields = null;

    /* User Prefered hidden Columns */
    public $hiddenColumns = [];

    public $editableColumns = [];
    /*User inline edit permission*/
    public $inlineEdit = false;
    // to manage Managecolumns
    public $manageColumns = false;
    public $leftColumns
        = [
            'CheckboxColumn',
            'SerialColumn'
        ];
    public $rightColumns
        = [
            'ActionColumn'
        ];
    /**
     * @var string the HTML code to be displayed between any two consecutive items.
     */
    public $separator = "\n";
    // Grid Preference Action

    // If $enableCardView required displayMode card/grid & cardView

    public $displayMode = 'grid';

    public $cardView = null; // ['template'=>{renderFile},groupBy=>'']
    public $groupByAttribute = false;
    public $gridPreferAction = 'save-grid-preference';
    public $primaryKey;
    public $gridEditAction = 'save-grid-edit';
    public $model;
    public $cardColumnCount = 4;
    public $saveGridChanges = 'save-grid-changes';

    /**
     * Initializes the grid view.
     * This method will initialize required property values and instantiate [[columns]] objects.
     */
    public function init()
    {
        $this->setProperties();
        parent::init();

        $class = isset($this->options['class']) ? $this->options['class'] . ' grid-view panel panel-primary' : 'grid-view panel panel-primary';
        $options = ['class' => $class];
        $this->options = ArrayHelper::merge($options, $this->options);

        $filterSelector
            = '#' . $this->options['id'] . ' input[name="page"],#' . $this->options['id'] . ' select[name="per-page"]';

        if ($this->advanceSearch != null) {
            $filterSelector = $filterSelector . ',#' . $this->options['id'] . ' select[name="user_list_preference"]';
        }

        if ($this->cardView != 'null') {
            $filterSelector .= ', #' . $this->options['id'] . ' .grid-mode-switch a, #'
                . $this->options['id'] . ' select[name="group-by-attribute"]';
        }

        if (isset($this->filterSelector)) {
            $filterSelector .= ', ' . $this->filterSelector;
        }

        $this->filterSelector = $filterSelector;
    }


    /**
     * Assigning object properties based on passed or get paramers
     */
    public function setProperties()
    {
        $this->saveGridChanges = Url::to([Yii::$app->controller->id.'/'.$this->saveGridChanges]);
        $this->gridEditAction = Url::to([Yii::$app->controller->id.'/'.$this->gridEditAction]);
        $perPage = '';
        if ($userColumns = $this->getUserColumns()) {
            $this->displayMode = json_decode($userColumns['columns'], true)['display-mode'];
            $perPage = (json_decode($userColumns['columns'], true)['per-page'])
                ? json_decode(
                    $userColumns['columns'],
                    true
                )['per-page']
                : 10;
            $perPage = (ArrayHelper::getValue(Yii::$app->getRequest()->getQueryParams(), 'per-page'))
                ? ArrayHelper::getValue(Yii::$app->getRequest()->getQueryParams(), 'per-page')
                : (json_decode(
                    $userColumns['columns'],
                    true
                )['per-page']);
            $groupBy = (ArrayHelper::getValue(
                Yii::$app->getRequest()->getQueryParams(),
                'group-by-attribute',
                $this->groupByAttribute
            )) ? ArrayHelper::getValue(
                Yii::$app->getRequest()->getQueryParams(),
                'group-by-attribute',
                $this->groupByAttribute
            ) : json_decode($userColumns['columns'], true)['group-by'];
            $this->groupByAttribute = $groupBy;
        } else {
            $perPage = ArrayHelper::getValue(
                Yii::$app->getRequest()->getQueryParams(),
                'per-page',
                10
            );
            $groupBy = ArrayHelper::getValue(
                Yii::$app->getRequest()->getQueryParams(),
                'group-by-attribute',
                $this->groupByAttribute
            );
            $this->groupByAttribute = $groupBy;
        }
        if ($this->dataProvider->pagination) {
            $this->dataProvider->pagination->pageSize = $perPage;
        }

        $this->displayMode = ArrayHelper::getValue(
            Yii::$app->getRequest()->getQueryParams(),
            'display-mode',
            $this->displayMode
        );

        $this->groupByAttribute = (empty(Yii::$app->getRequest()->getQueryParams())
            || count(Yii::$app->getRequest()->getQueryParams()) == 2)
            ? $groupBy
            : ArrayHelper::getValue(
                Yii::$app->getRequest()->getQueryParams(),
                'group-by-attribute',
                $this->groupByAttribute
            );
    }

    /**
     * Renders the data models for the grid view.
     */
    public function renderItems()
    {
        $content = '';
        $options = ['class' => 'grid-table panel-body'];
        if ($this->cardView != null && $this->displayMode == $this::MODE_CARD) {
            $content = $this->renderCards();
            $options['class'] = ['grid-table card-view panel-body'];
        } else {
            $caption = $this->renderCaption();
            $columnGroup = $this->renderColumnGroup();
            $tableHeader = $this->showHeader ? $this->renderTableHeader() : false;
            $tableBody = $this->renderTableBody();
            $tableFooter = $this->showFooter ? $this->renderTableFooter() : false;
            $content = array_filter(
                [
                    $caption,
                    $columnGroup,
                    $tableHeader,
                    $tableFooter,
                    $tableBody,
                ]
            );
            $content = Html::tag('table', implode("\n", $content), $this->tableOptions);
        }

        return Html::tag('div', $content, $options);
    }

    /**
     * Renders the table body.
     *
     * @return string the rendering result.
     */
    public function renderTableBody()
    {
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        $groupByAttribute = $this->groupByAttribute;
        $colspan = count($this->columns);
        if (count($models) == 0) {
            return "<tbody>\n<tr><td colspan=\"$colspan\">" . $this->renderEmpty() . "</td></tr>\n</tbody>";
        }
        foreach ($models as $index => $model) {
            $key = $keys[$index];
            if ($this->beforeRow !== null) {
                $row = call_user_func($this->beforeRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
            if ($groupByAttribute) {
                $attributes=(array)array_values($this->getGroupByColumn($groupByAttribute));

                $attributes=array_shift($attributes);

                $groupKey = $attributes->getDataCellValue(
                    $model,
                    $keys[$index],
                    $index
                );
                $groupKey=(string)$groupKey;
                $rows[$groupKey][] = $this->renderTableRow($model, $key, $index);
            } else {
                $rows[] = $this->renderTableRow($model, $key, $index);
            }

            if ($this->afterRow !== null) {
                $row = call_user_func($this->afterRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
        }
        if (!$groupByAttribute) {
            return "<tbody>\n" . implode("\n", $rows) . "\n</tbody>";
        }
        $tbody = '';
        foreach ($rows as $key => $row) {
            $headText = '<span class="glyphicon glyphicon-plus"></span>' . $key . ' (' . count($row) . ')';
            $tbody
                    .= "<tbody><tr class='group-header'><td colspan=\"$colspan\">" . $headText . "</td></tr></tbody>";
            $tbody .= "<tbody style='display:none'>\n" . implode("\n", $row) . "\n</tbody>";
        }

        return $tbody;
    }

    /**
     * Renders all data models.
     *
     * @return string the rendering result
     */
    public function renderCards()
    {
        $models = $this->dataProvider->getModels();
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        $groupByAttribute = $this->groupByAttribute;
        if (!empty($models)) {
            foreach (array_values($models) as $index => $model) {
                if ($groupByAttribute) {
                    $groupByAttributeColumns=array_values($this->getGroupByColumn($groupByAttribute));
                    $key = array_shift($groupByAttributeColumns)->getDataCellValue(
                      $model,
                      $keys[$index],
                      $index
                  );
                    $rows[trim($key)][] = $this->renderCard($model, $keys[$index], $index);
                } else {
                    $rows[] = $this->renderCard($model, $keys[$index], $index);
                }
            }
            if ($groupByAttribute) {
                $content = '';
                foreach ($rows as $key => $row) {
                    $headText =  $key . ' (' . count($row) . ')';
                    $header = Html::tag('h5', $headText, ['class' => 'card-group-header', 'data-widget' => 'collapse']);
                    $cards = Html::tag('ul', implode($this->separator, $row), ['class' => 'box-body list-group']);
                    $group = Html::tag(
                      'div',
                      $header . $cards,
                      ['class' => 'card-group-container list-group-item box clearfix collapsed-box']
                  );
                    $content .= $group;
                }
                return $content;
            }
            return Html::tag('ul', implode($this->separator, $rows), ['class'=>'list-group']);
        }
        return Html::tag('p', Yii::t('app', 'No results found'));
    }

    /**
     * Renders a single data model.
     *
     * @param mixed   $model the data model to be rendered
     * @param mixed   $key   the key value associated with the data model
     * @param integer $index the zero-based index of the data model in the model array returned by [[dataProvider]].
     *
     * @return string the rendering result
     */
    public function renderCard($model, $key, $index)
    {
        $itemView = ArrayHelper::getValue($this->cardView, 'template');
        if ($itemView === null) {
            $content = $key;
        } elseif (is_string($itemView)) {
            $viewParams = ArrayHelper::getValue($this->cardView, 'viewParams', []);
            $content = Html::tag('div', $this->getView()->render(
                $itemView,
                array_merge(
                    [
                        'model' => $model,
                        'key' => $key,
                        'index' => $index,
                        'widget' => $this,
                    ],
                    $viewParams
                )
            ), ['class'=>'page-card']);
        } else {
            $content = Html::tag('div', call_user_func($itemView, $model, $key, $index, $this), ['class'=>'page-card']);
        }

        $actionColumns = array_filter(
            $this->columns,
            function ($column) {
                return (new ReflectionClass($column))->getShortName() == 'ActionColumn';
            }
        );

        if (count($actionColumns) > 0) {
            $actions = '';
            foreach ($actionColumns as $actionColumn) {
                $actions .= $actionColumn->getCellData($model, $key, $index);
            }
            $content .= Html::tag('div', $actions, ['class' => 'card-actions action-column']);
        }
        $checkboxColumns = array_filter(
            $this->columns,
            function ($column) {
                return (new ReflectionClass($column))->getShortName() == 'CheckboxColumn';
            }
        );
        if (count($checkboxColumns) > 0) {
            $checkbox = '';
            foreach ($checkboxColumns as $checkboxColumn) {
                $checkbox .= $checkboxColumn->getCellData($model, $key, $index);
            }
            $content .= Html::tag('div', $checkbox, ['class' => 'card-checkbox']);
        }
        $options = [];
        $tag = ArrayHelper::remove($options, 'tag', 'li');
        $options['data-key'] = is_array($key) ? json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : (string)$key;
        $options['class'] = 'grid-card col-md-' . (12 / $this->cardColumnCount);
        $content = Html::tag('div', $content, ['class'=>'list-group-item']);
        return Html::tag($tag, $content, $options);
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        $view = $this->getView();
        GridViewAsset::register($view);
        parent::run();
        Editable::register($this);
    }

    /**
     * Returns the options for the grid view JS widget.
     *
     * @return array the options
     */
    public function getClientOptions()
    {
        $filterUrl = isset($this->filterUrl) ? $this->filterUrl : Yii::$app->request->url;
        $id = $this->filterRowOptions['id'];
        $filterSelector = "#$id input, #$id select";
        if (isset($this->filterSelector)) {
            $filterSelector .= ', ' . $this->filterSelector;
        }

        return [
            'filterUrl' => Url::to($filterUrl),
            'filterSelector' => $filterSelector,
            'saveGridChange' => $this->saveGridChanges,
            'entity' => Yii::$app->controller->route
        ];
    }

    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        /* @var $class LinkPager */
        $pager = $this->pager;
        $class = \yii\helpers\ArrayHelper::remove($pager, 'class', ListPager::className());
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();
        $pager['pageSize'] = $this->pageSize;
        $pager['totalCount'] = $this->dataProvider->getTotalCount();
        $content = $class::widget($pager);
        return Html::tag('div', $content, ['class'=>'btn-group grid-pagination pull-right col-md-5 col-sm-12 col-xs-12']);
    }

    public function renderSection($name)
    {
        $renderfunctions=[
        '{header}'=>'renderGridHeader',
        '{items}'=>'renderItems',
        '{pagination}'=>'renderPager',
        '{sorter}'=>'renderSorter',
        '{groupby}'=>'gridGroupBy',
        '{switchmode}'=>'modeSwitches',
        '{saveSearch}'=>'saveSearch',
        '{advanceSearch}'=>'advanceSearch',
        '{globalSearch}'=>'globalSearch',
        '{manageColumns}'=>'manageColumns'
      ];
        if (isset($renderfunctions[$name])) {
            $functionName=$renderfunctions[$name];
            return $this->$functionName();
        }
        return parent::renderSection($name);
    }

    /*
    * Render Grid Header block it will use  $this->headerLayout template
    */
    public function renderGridHeader()
    {
        $contentHeader = Html::beginTag('div', ['class'=>'panel-heading']);
        $contentHeader .= Html::tag('h3', $this->title, ['class'=>'panel-title']);
        $contentHeader .= Html::endTag('div');
        $content = preg_replace_callback(
            "/{\\w+}/",
            function ($matches) {
                $content = $this->renderSection($matches[0]);

                return $content === false ? $matches[0] : $content;
            },
            $this->headerLayout
        );
        $content = Html::tag('div', $content, ['class'=>'btn-toolbar']);
        return $contentHeader.Html::tag('div', Html::tag('div', $content), ['class' => 'grid-main-header panel-body']);
    }

    /**
     * handles to switching between card and grid view
     */
    public function modeSwitches()
    {
        if ($this->cardView == null) {
            return '';
        }
        $gmOptions = [
            'title' => Yii::t('app', 'List View'),
            'class' => 'btn btn-default'
        ];
        $cmOptions = [
            'title' => Yii::t('app', 'Card View'),
            'class' => 'btn btn-default'
        ];
        if ($this->displayMode == $this::MODE_GRID) {
            $gmOptions = ['class' => 'active btn btn-default'];
        }
        if ($this->displayMode == $this::MODE_CARD) {
            $cmOptions = ['class' => 'active btn btn-default'];
        }
        $content = [
            Html::a(
                '<span class="glyphicon glyphicon-th-list"></span>',
                $this->createGridUrl(['display-mode' => $this::MODE_GRID]),
                $gmOptions
            ),
            Html::a(
                '<span class="glyphicon glyphicon-th-large"></span>',
                $this->createGridUrl(['display-mode' => $this::MODE_CARD]),
                $cmOptions
            )
        ];
        return Html::tag(
            'div',
            implode("\n", $content),
            ['class' => 'grid-mode-switch btn-group col-md-1 col-sm-2 col-xs-4 ' . $this->blockContainer]
        );
    }

    /**
     * To display the manage columns option
     */
    public function manageColumns()
    {
        if (!Yii::$app->user->isGuest) {
            if ($this->manageColumns == null || $this->displayMode == self::MODE_CARD) {
                return '';
            }
            return Html::tag('div', Html::a(Yii::t('app', 'Manage Columns'), 'javascript:void(0);', [
          'onclick' => "$('#".$this->options['id']."').trigger('manage-column','open');",'class'=>'btn btn-primary'
        ]), ['class'=>'grid-header-blk btn-group col-md-1 col-sm-3 col-xs-6']);
        }
        return '';
    }

    /**
     * Group By DropDown for card view
     */
    public function gridGroupBy()
    {
        $groupBy = ArrayHelper::map($this->getGroupByColumn(), 'attribute', 'headerLabel');
        $groupElements = array_map(
            function ($key) {
                return html_entity_decode('&nbsp; -' . $key);
            },
            $groupBy
        );
        if (!$groupElements) {
            return '';
        }
        $content[] = Html::dropDownList(
            'group-by-attribute',
            $this->groupByAttribute,
            $groupElements,
            ['prompt' => 'Group by', 'class' => 'form-control card-group-by']
        );
        return Html::tag(
            'div',
            implode("\n", $content),
            ['class' => 'grid-mode-group btn-group col-md-2 col-sm-3 col-xs-6 ' . $this->blockContainer]
        );
    }

    /* create Grid Mode Url */
    public function createGridUrl($params, $absolute = false)
    {
        $request = Yii::$app->getRequest();
        $rParams = $request->getQueryParams();
        $params = ArrayHelper::merge($rParams, $params);
        $params[0] = Yii::$app->controller->getRoute();
        $urlManager = Yii::$app->getUrlManager();
        if ($absolute) {
            return $urlManager->createAbsoluteUrl($params);
        }
        return $urlManager->createUrl($params);
    }

    /*
    * Save Search Block
    */
    public function saveSearch()
    {
        if ($this->advanceSearch) {
            $userListPreference = \ktree\grid\models\UserListPreference::find()
                ->where(
                    [
                        'user_id' => [0, Yii::$app->user->id],
                        'model' => (new ReflectionClass($this->filterModel))->getShortName(),
                        'grid_id' => $this->options['id']
                    ]
                )->asArray()->all();
            return Html::tag(
                'div',
                $this->render(
                    'saveSearch',
                    [
                        'userListPreference' => $userListPreference,
                        'grid' => $this->options['id']
                    ]
                ),
                ['class' => 'search-list btn-group col-md-2 col-sm-3 col-xs-6 ' . $this->blockContainer]
            );
        }
        return '';
    }

    /*
     * advance Search Block
     * For Displaying this block need to set an array to grid with advanceSearch
     * Filtermodel and advanceSearch -> searchForm for grid is mandatory
     * return advance search form Html
    */
    public function advanceSearch()
    {
        if ($this->advanceSearch && $this->filterModel) {
            $deleteUrl = Url::toRoute([Yii::$app->controller->id .'/delete-filters']);
            Yii::$app->view->registerJs("var deleteUrl = '$deleteUrl';", View::POS_HEAD);
            return Html::tag('div', $this->getView()->render('@vendor/ktree/grid/views/advanceSearch.php', [
              'fields' => $this->advanceSearchFields,
              'searchModel' => $this->filterModel,
              'grid' => $this->options['id'],
            ]), ['class'=>'grid-advance-search grid-header-blk col-md-1 col-sm-1 col-xs-2']);
        }
        return '';
    }

    /*
        Get User prefered Active Columns.
    */
    public function getUserColumns()
    {
        return UserGridPreferences::find()->where(
            [
                'entity' => Yii::$app->controller->route,
                'user_id' => Yii::$app->user->id,
                'grid_id' => isset($this->options['id'])?$this->options['id']:''
            ]
        )->asArray()->one();
    }

    /**
     * Creates column objects and initializes them.
     */
    protected function initColumns()
    {
        if (empty($this->columns)) {
            $this->guessColumns();
        }
        foreach ($this->columns as $i => $column) {
            $index = $i;
            if (is_string($column)) {
                $column = $this->createDataColumn($column);
            }

            $column = Yii::createObject(
                array_merge(
                    [
                        'class' => (isset($column['isEditable']))
                                ?
                                EditableColumn::className()
                                :
                                ($this->dataColumnClass ? : DataColumn::className()),
                        'grid' => $this,
                    ],
                    $column
                )
            );

            if (isset($column->isEditable) && $column->isEditable == true && $this->inlineEdit) {
                $this->editableColumns[] = ($column->source)
                    ? [
                        $index,
                        'attribute' => $column->attribute,
                        'source' => json_encode($column->source),
                        'pk' => $column->pk
                    ]
                    : [
                        $index,
                        'attribute' => $column->attribute,
                        'pk' => $column->pk
                    ];
            }
            if (property_exists($column, 'visibleInAdvanceSearch') && $column->visibleInAdvanceSearch) {
                $this->advanceSearchFields[$i] = $column;
            }

            if ($this->cardView != null && $this->displayMode == $this::MODE_CARD
                && property_exists(
                    $column,
                    'group'
                )
            ) {
                if (!$column->group) {
                    unset($this->columns[$i]);
                    continue;
                }
            }
            if (!$column->visible) {
                unset($this->columns[$i]);
                continue;
            }
            $this->columns[$i] = $column;
        }
        if ($this->manageColumns) {
            $this->sortColumns();
            $this->initManageColumns();
        }
        if($this->inlineEdit){
			$id = $this->options['id'];
			$this->getView()->registerJs("jQuery('#$id').find('table td:last-child').attr('style','border-right:1px solid transparent;');");
			$this->getView()->registerJs("jQuery('#$id').find('table th:last-child').attr('style','border-right:1px solid transparent;');");
		}
    }

    public function getGroupByColumn($attribute = null)
    {
        return array_filter(
            $this->columns,
            function ($column) use ($attribute) {
                if ($attribute) {
                    return property_exists($column, 'group') && $column->attribute == $attribute;
                }
                return property_exists($column, 'group') && $column->group;
            }
        );
    }

    public function sortColumns()
    {
        $fixedLeft = false;
        $fixedRight = false;
        $columns = [];
        $userColumns = $this->getUserColumns();
        if (isset(json_decode($userColumns['columns'], true)['columns'])) {
            $userColumns = json_decode(json_decode($userColumns['columns'], true)['columns']);

            $userColumns = ($userColumns == null) ? [] : $userColumns;
            foreach ($this->columns as $i => $column) {
                $className = (new ReflectionClass($column))->getShortName();
                if (ArrayHelper::isIn($className, $this->leftColumns)) {
                    $fixedLeft = $column;
                    continue;
                }
                if (ArrayHelper::isIn($className, $this->rightColumns)) {
                    $fixedRight = $column;
                    continue;
                }
                $key = array_search($column->attribute, $userColumns);
                if ($key !== false) {
                    $columns[$key] = $column;
                    $this->activeColumns[$key] = $this->getSortColumn($column);
                } else {
                    $this->hiddenColumns[] = $this->getSortColumn($column);
                }
            }
            ksort($columns);
            ksort($this->activeColumns);
        } else {
            foreach ($this->columns as $i => $column) {
                $className = (new ReflectionClass($column))->getShortName();
                if (ArrayHelper::isIn($className, $this->leftColumns)) {
                    $fixedLeft = $column;
                    continue;
                }
                if (ArrayHelper::isIn($className, $this->rightColumns)) {
                    $fixedRight = $column;
                    continue;
                }
                $columns[] = $column;
                $this->activeColumns[] = $this->getSortColumn($column);
            }
        }
        if ($fixedLeft) {
            array_unshift($this->activeColumns, $this->getSortColumn($fixedLeft));
            array_unshift($columns, $fixedLeft);
        }
        if ($fixedRight) {
            $this->activeColumns[] = $this->getSortColumn($fixedRight);
            $columns[] = $fixedRight;
        }
        $this->columns = $columns;
    }

    /**
     * To get column array from grid column object for sort widget.
     *
     * @param object $column the column object
     */
    public function getSortColumn($column)
    {
        $className = (new ReflectionClass($column))->getShortName();
        $fixedColumns = array_merge($this->leftColumns, $this->rightColumns);
        return [
            'attribute' => ArrayHelper::isIn($className, $fixedColumns) ? $className : $column->attribute,
            'label' => ArrayHelper::isIn($className, $fixedColumns) ? $className : $column->headerLabel,
            'fixed' => ArrayHelper::isIn($className, $fixedColumns) ? true : false
        ];
    }

    public function initManageColumns()
    {
        $id = $this->options['id'];
        $gridUrl = str_replace(
            '/' . Yii::$app->controller->action->id,
            '/' . $this->gridPreferAction,
            Yii::$app->controller->route
        );
        $options = [
            'hiddenColumns' => $this->hiddenColumns,
            'activeColumns' => $this->activeColumns,
            'gridurl' => Yii::$app->getUrlManager()->createUrl($gridUrl),
            'entity' => Yii::$app->controller->route
        ];
        $options = Json::htmlEncode($options);
        $view = $this->getView();
        \ktree\grid\GridAsset::register($view);
        $view->registerJs("jQuery('#$id').gridManageColumn($options);");
        echo $this->getView()->renderFile('@vendor/ktree/grid/views/_manageColumnDialog.php', ['id' => $id]);
    }

    /**
     * Creates a [[DataColumn]] object based on a string in the format of "attribute:format:label".
     *
     * @param string $text the column specification string
     *
     * @return DataColumn the column instance
     * @throws InvalidConfigException if the column specification is invalid
     */
    protected function createDataColumn($text)
    {
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new InvalidConfigException('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
        }

        return [
            'attribute' => $matches[1],
            'format' => isset($matches[3]) ? $matches[3] : 'text',
            'label' => isset($matches[5]) ? $matches[5] : null,
        ];
    }

    /*
     * global Search Block
     * For Displaying this block need to set an array to grid with globalSearch
     * Filtermodel and globalSearch -> searchForm for grid is mandatory
     * return global search form Html
    */
    public function globalSearch()
    {
        if ($this->globalSearch != false) {
            $filterModel = $this->filterModel;
            $searchField = $this->globalSearch['searchField'];
            $filterModel->$searchField = $this->globalSearch['value'];
            $result = Html::beginTag('div', ['class' => 'search-list grid-header-blk btn-group col-md-2 col-sm-2 col-xs-3']);
            $result .= '<i class="fa fa-search"></i>';
            $result .= Html::activeTextInput(
                $filterModel,
                $searchField,
                ['placeholder' => 'Search', 'class' => 'form-control ' . $this->options['id'] . '_search']
            );
            $result .= Html::endTag('div');
            return $result;
        }
    }
}
