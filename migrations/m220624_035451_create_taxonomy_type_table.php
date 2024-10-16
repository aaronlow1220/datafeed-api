<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%taxonomy_type}}`.
 */
class m220624_035451_create_taxonomy_type_table extends Migration
{
    /**
     * @var string
     */
    private $table = 'taxonomy_type';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(64)->notNull()->comment('only allow [A-Za-z0-9\_], except " " and "#"'),
            'description' => $this->string(64)->notNull(),
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            'updated_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            'created_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'updated_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
        ]);

        // setup index
        $this->createIndex('UNIQUE_NAME', $this->table, ['name'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
