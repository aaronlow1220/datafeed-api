<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feed_file}}`.
 */
class m241104_085324_create_feed_file_table extends Migration
{
    /**
     * @var string
     */
    public $table = 'file';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned()->notNull()->comment('Auto increment id'),
            'client_id' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: client.id'),
            'platform_id' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: platform.id'),
            'file_id' => $this->bigInteger(20)->unsigned()->comment('ref: file.id'),
            'filter' => $this->text()->notNull()->defaultValue('{}')->comment('Custom filter, JSON format'),
            'utm' => $this->string(255)->comment('Utm parameter'),
            'status' => 'ENUM("0", "1") NOT NULL DEFAULT "1" COMMENT "0:inactive 1:active, ref:taxonomies.value of type name[feed_file_status]"',
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
