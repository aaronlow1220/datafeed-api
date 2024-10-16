<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%data_version}}`.
 */
class m241014_084241_create_data_version_table extends Migration
{
    /**
     * @var string
     */
    public $table = 'data_version';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned()->notNull()->comment('Auto increment id'),
            'client_id' => $this->bigInteger()->unsigned()->notNull()->comment('Client id'),
            'hash' => $this->string(255)->notNull()->comment('Data hash'),
            'status' => 'ENUM("1", "2") NOT NULL DEFAULT "1" COMMENT "1: success 2:failed 3:processing, ref:taxonomies.value of type name[data_version_status]"',
            'created_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            'updated_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'updated_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
