<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%platform}}`.
 */
class m241014_084232_create_platform_table extends Migration
{
    /**
     * @var string
     */
    public $table = 'platform';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned()->notNull()->comment('Auto increment id'),
            'label' => $this->string(255)->notNull()->comment('Platform label'),
            'name' => $this->string(255)->notNull()->comment('Platform name'),
            'data' => $this->text()->notNull()->comment('Data mapping rule, JSON format'),
            'sftp' => 'ENUM("0", "1") NOT NULL DEFAULT "0" COMMENT "0:false 1:true, ref:taxonomies.value of type name[platfor_sftp]"',
            'price_currency' => 'ENUM("0", "1") NOT NULL DEFAULT "0" COMMENT "0:false 1:true, ref:taxonomies.value of type name[platfor_price_currency]"',
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
