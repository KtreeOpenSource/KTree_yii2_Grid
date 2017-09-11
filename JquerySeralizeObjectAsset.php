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
class JquerySeralizeObjectAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery-serialize-object';
    /**
     * @inheritdoc
     */
    public $js = [
      'jquery.serialize-object.js'
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
      'yii\bootstrap\BootstrapPluginAsset'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}
