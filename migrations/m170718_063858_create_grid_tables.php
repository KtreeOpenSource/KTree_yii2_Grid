<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
use yii\db\Migration;
use yii\db\Schema;

class m170718_063858_create_grid_tables extends Migration
{
    public function up()
    {
        $this->createTable($this->db->tablePrefix.'user_grid_preference', [
            'id' => 'pk',
            'user_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'entity' => Schema::TYPE_STRING . '(255) NOT NULL',
            'grid_id' => Schema::TYPE_STRING . '(255) NOT NULL',
            'columns' => Schema::TYPE_TEXT.' NOT NULL',
            'created_at' => Schema::TYPE_TIMESTAMP. ' NOT NULL DEFAULT NOW()',
            'updated_at' => Schema::TYPE_TIMESTAMP. ' NOT NULL DEFAULT NOW()'
      ]);


        $this->createTable($this->db->tablePrefix.'user_list_preference', [
          'id' => 'pk',
          'title' => Schema::TYPE_STRING . '(255) NOT NULL',
          'user_id' => Schema::TYPE_INTEGER.' NOT NULL',
          'grid_id' => Schema::TYPE_STRING . '(255) NOT NULL',
          'model' => Schema::TYPE_STRING . '(255) NOT NULL',
          'filters' => Schema::TYPE_TEXT.' NOT NULL '
      ]);
    }

    public function down()
    {
        echo "m170718_063858_create_grid_tables cannot be reverted.\n";

        return false;
    }
}
