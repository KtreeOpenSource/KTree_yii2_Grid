<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid;

use Yii;
use yii\helpers\Html;
use ktree\grid\widgets\editable\EditableAsset;
use ktree\grid\widgets\editable\EditableConfig;
use kartik\widgets\Select2;
use yii\jui\DatePicker;

/**
 * The EditableColumn converts the data to editable using the Editable widget [[\kartik\editable\Editable]]
 *
 * @author KTree
 *
 */
class EditableColumn extends DataColumn
{
    /**
     * @var array defaults for editable configuration
     */
    public $pluginOptions = [];
    public $dataType = 'text';
    public $pk = 'id';
    public $dataTitle = '';
    public $isEditable = '';
    public $dataValue;
    public $editable = [];
    private $view = null;
    public $url = null;
    public $source;
    public $displayDbValue = false;

    public function init()
    {
        parent::init();
        $this->registerAssets();
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = parent::renderDataCellContent($model, $key, $index);
        $cellContent = $value;

        if ($this->isEditable) {
            $cellContent = '<span class="tabledit-span">' . $value . '</span>';
            $editableInput = '';
            $errorContent = '';
            $primaryKey = is_object($model) ? $model->{$this->pk} : $model[$this->pk];
            switch ($this->dataType) {
                case 'select':
                    $defaultValue = array_search($value, $this->source);
                    if ($this->displayDbValue) {
                        $defaultValue = $value;
                    }

                    $editableInput = Html::dropDownList(
                        $this->attribute,
                        $defaultValue,
                        $this->source,
                        [
                            'class' => 'tabledit-input form-control edit-select',
                            'disabled' => 'disabled',
                            'style' => 'display:none;',
                            'data-pk' => $primaryKey
                        ]
                    );
                    break;
                case 'select2':
                    $editableInput = Select2::widget(
                        [
                            'name' => $this->attribute,
                            'value' => $value,
                            'data' => $this->source,
                            'options' => [
                                'id' => 'multiselect_' . $primaryKey,
                                'class' => 'tabledit-input form-control edit-select-multi',
                                'disabled' => 'disabled',
                                'style' => 'display:none;',
                                'data-pk' => $primaryKey,
                                'multiple' => true
                            ],
                        ]
                    );
                    break;
                case 'datePicker':
                    $editableInput = DatePicker::widget(
                        [
                            'name' => $this->attribute,
                            'dateFormat' => Yii::$app->formatter->dateFormat,
                            'value' => $value,
                            'clientOptions' => ['disabled' => true],
                            'options' => [
                                'id' => 'datePicker_' . $primaryKey,
                                'class' => 'tabledit-input form-control editDatePicker',
                                'disabled' => 'disabled',
                                'style' => 'display:none;',
                                'data-pk' => $primaryKey
                            ],

                        ]
                    );
                    break;
                default:
                    $editableInput = Html::textInput(
                        $this->attribute,
                        $value,
                        [
                            'class' => 'tabledit-input form-control',
                            'disabled' => 'disabled',
                            'style' => 'display:none;',
                            'data-pk' => $primaryKey
                        ]
                    );
            }
            $errorContent = '<span class="tabledit-error help-block ' . $this->attribute
                . '" style="display: none;" disabled></span>';
            $cellContent
                = $cellContent . '<span class="tabledit-input-span" style="display:none">' . $editableInput . '</span>'
                . $errorContent;
        }

        return $cellContent;
    }

    /**
     * @inheritdoc
     */
    public function registerAssets()
    {
        $config = new EditableConfig();
        if (isset($this->pluginOptions['mode']) && is_array($this->pluginOptions)) {
            $config->mode = $this->pluginOptions['mode'];
        }
        if (isset($this->pluginOptions['form']) && is_array($this->pluginOptions)) {
            $config->form = $this->pluginOptions['form'];
        }
        $config->registerDefaultAssets();
        $this->view = Yii::$app->getView();
        EditableAsset::register($this->view);

        if ($this->dataType == 'select2' || $this->dataType == 'select') {
            $newSource = [];
            $key = ($this->dataType == 'select2') ? 'id' : 'value';
            if (isset($this->editable['source']) && is_array($this->editable['source'])) {
                foreach ($this->editable['source'] as $sourceKey => $sourceValue) {
                    $newSource[] = [$key => $sourceKey, 'text' => $sourceValue];
                }
                $this->editable['source'] = $newSource;
            }
        }

        $this->editable = \yii\helpers\Json::encode($this->editable);
        $this->view->registerJs(
            '$(".editable[data-name=' . $this->attribute . ']").editable(' . $this->editable . ');'
        );
    }
}
