<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid;

use Yii;
use yii\helpers\Html;

/**
 * DataColumn is the default column type for the [[GridView]] widget.
 *
 * It is used to show data columns and allows [[enableSorting|sorting]] and [[filter|filtering]] them.
 *
 * A simple data column definition refers to an attribute in the data model of the
 * GridView's data provider. The name of the attribute is specified by [[attribute]].
 *
 * By setting [[value]] and [[label]], the header and cell content can be customized.
 *
 * A data column differentiates between the [[getDataCellValue|data cell value]] and the
 * [[renderDataCellContent|data cell content]]. The cell value is an un-formatted value that
 * may be used for calculation, while the actual cell content is a [[format|formatted]] version of that
 * value which may contain HTML markup.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class DataColumn extends \yii\grid\DataColumn
{
    public $visibleInAdvanceSearch = true;

    public $isAdvanceFilter = false;

    public $isUsedInFilters = false;

    public $headerLabel = '';

    public $group = false;

    public $isEditable = false;

    public $pk;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $label = $this->getHeaderCellLabel();
        $this->headerLabel = $label;
    }

    protected function renderFilterCellContent()
    {
        if (is_string($this->filter)) {
            return $this->filter;
        }

        if (is_array($this->filter) && isset($this->filter['useSelect2']) && $this->filter['useSelect2']) {
            $selectOptions = $this->filter;
            unset($selectOptions['useSelect2']);
            if (!isset($selectOptions['options']['placeholder'])) {
                $selectOptions['options']['placeholder'] = Yii::t('Product', '--Please Select--');
            }
            $selectOptions['options']['id'] =
                ($this->isAdvanceFilter) ? 'advance-filter-' . $this->attribute
                : (isset($selectOptions['options']['id']) ? $selectOptions['options']['id'] : '');

            $inputHtml = \kartik\widgets\Select2::widget($selectOptions);
            $filterHtml = '<div class="filter-div-class">';
            $filterHtml .= $inputHtml;
            $filterHtml .= '</div>';
            return $filterHtml;
        }

        $model = $this->grid->filterModel;

        if ($this->filter !== false && $model instanceof Model && $this->attribute !== null
            && $model->isAttributeActive($this->attribute)
        ) {
            if ($model->hasErrors($this->attribute)) {
                Html::addCssClass($this->filterOptions, 'has-error');
                $error = ' ' . Html::error($model, $this->attribute, $this->grid->filterErrorOptions);
            } else {
                $error = '';
            }

            if (is_array($this->filter)) {
                $options = array_merge(['prompt' => ''], $this->filterInputOptions);
                return Html::activeDropDownList($model, $this->attribute, $this->filter, $options) . $error;
            } else {
                return Html::activeTextInput($model, $this->attribute, $this->filterInputOptions) . $error;
            }
        } else {
            return parent::renderFilterCellContent();
        }
    }
}
