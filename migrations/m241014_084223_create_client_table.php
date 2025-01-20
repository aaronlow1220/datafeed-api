<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%client}}`.
 */
class m241014_084223_create_client_table extends Migration
{
    /**
     * @var string
     */
    public $table = 'client';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned()->notNull()->comment('Auto increment id'),
            'label' => $this->string(255)->notNull()->comment('Client label'),
            'name' => $this->string(255)->notNull()->comment('Client name'),
            'data' => $this->text()->notNull()->comment('Data mapping rule, JSON format'),
            'currency' => $this->string(3)->notNull()->comment('Currency code'),
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
