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
            'filename' => $this->string(255)->notNull()->comment('File name'),
            'hash' => $this->string(255)->notNull()->comment('Data hash'),
            'version' => $this->integer(10)->notNull()->comment('Version'),
            'status' => 'ENUM("0", "1", "2", "3") NOT NULL DEFAULT "2" COMMENT "0:failed 1:success 2:pending 3:processing, ref:taxonomies.value of type name[data_version_status]"',
            'created_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            'updated_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'updated_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
        ]);

        $this->createIndex('INDEX_CLIENT_ID', $this->table, 'client_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
