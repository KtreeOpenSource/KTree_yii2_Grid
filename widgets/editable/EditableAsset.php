<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */

namespace ktree\grid\widgets\editable;

use Yii;
use yii\web\AssetBundle;

/**
 * Asset bundle for Editable widget.
 *
 */
class EditableAsset extends AssetBundle
{
    public $sourcePath = '@vendor/ktree/grid/widgets/editable/assets';
    public $js = [
            'js/bootstrap-editable.js',
            'js/jquery.tabledit.js'
        ];
    public $css = [
            'css/bootstrap-editable.css',
        ];
    public $depends = [
            'yii\web\JqueryAsset',
            'yii\bootstrap\BootstrapAsset',
            'yii\bootstrap\BootstrapPluginAsset',
        ];
}
