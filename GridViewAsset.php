<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */

namespace ktree\grid;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for the [[GridView]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridViewAsset extends AssetBundle
{
    public $sourcePath = '@vendor/ktree/grid/assets';
    public $css = [
        'css/gridView.css'
    ];
    public $js = [
        'js/ktree.js',
        'js/ktreeGridView.js'
    ];

    public $depends = [
        'yii\grid\GridViewAsset',
        'ktree\grid\SelectAsset'
    ];
}
