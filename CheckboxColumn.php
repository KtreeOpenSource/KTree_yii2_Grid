<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid;

use yii\helpers\Html;

/**
 * CheckboxColumn displays a column of checkboxes in a grid view.
 *
 * To add a CheckboxColumn to the [[GridView]], add it to the [[GridView::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => 'yii\grid\CheckboxColumn',
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * Users may click on the checkboxes to select rows of the grid. The selected rows may be
 * obtained by calling the following JavaScript code:
 *
 * ```javascript
 * var keys = $('#grid').yiiGridView('getSelectedRows');
 * // keys is an array consisting of the keys associated with the selected rows
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CheckboxColumn extends \yii\grid\CheckboxColumn
{
    public $filtercellcontent;

    /**
     * Renders the filter cell content.
     * The default implementation simply renders a space.
     * This method may be overridden to customize the rendering of the filter cell (if any).
     *
     * @return string the rendering result
     */
    protected function renderFilterCellContent()
    {
        return $this->filtercellcontent;
    }

    public function getCellData($model, $key, $index)
    {
        $this->checkboxOptions =(!empty($this->checkboxOptions)) ? $this->checkboxOptions : ['class'=>'checkbox-column','id'=>'check_'.$key];
        $content = parent::renderDataCellContent($model, $key, $index);
        $content .= Html::tag('label', '<span class="glyphicon glyphicon-check"></span>', ['for'=>'check_'.$key,'class'=>'fa']);
        return $content;
    }
}
