<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%file}}`.
 */
class m241021_073546_create_file_table extends Migration
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
            'mime' => $this->string(128)->notNull()->comment('MIME type'),
            'extension' => $this->string(8)->notNull()->comment('Extension'),
            'filename' => $this->string(255)->notNull()->comment('Filename'),
            'path' => $this->string(255)->notNull()->comment('Path'),
            'size' => $this->integer()->notNull()->comment('Size in byte'),
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
