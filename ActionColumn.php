<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid;

use Yii;

/**
 * @inherit
 */
class ActionColumn extends \yii\grid\ActionColumn
{
    public $headerOptions = ['class' => 'action-column'];
    public $contentOptions = ['class' => 'action-column'];

    /**
     * @inheritdoc
     */
    public function getCellData($model, $key, $index)
    {
        return parent::renderDataCellContent($model, $key, $index);
    }
}
