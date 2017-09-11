<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid\models;

use Yii;

/**
 * This is the model class for table "{{%user_list_preference}}".
 *
 * @property integer $id
 * @property string $title
 * @property integer $user_id
 * @property string $grid_id
 * @property string $model
 * @property string $filters
 */
class UserListPreference extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_list_preference}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'user_id', 'grid_id', 'model', 'filters'], 'required'],
            [['user_id'], 'integer'],
            [['filters'], 'string'],
            [['title', 'grid_id', 'model'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'user_id' => Yii::t('app', 'List Type'),
            'grid_id' => Yii::t('app', 'Grid ID'),
            'model' => Yii::t('app', 'Model'),
            'filters' => Yii::t('app', 'Filters'),
        ];
    }
}
