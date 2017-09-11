<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */

namespace ktree\grid;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class GridAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/ktree/grid/assets';
    /**
     * @inheritdoc
     */
    public $js = [
      'js/yiiGridManageColumn.js'
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => \yii\web\View::POS_END];
    /**
     * @inheritdoc
     */
    public $depends = [
      'yii\web\JqueryAsset',
      'yii\bootstrap\BootstrapAsset',
      'yii\bootstrap\BootstrapPluginAsset',
      'yii\jui\JuiAsset'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}
