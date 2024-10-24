<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m241016_081322_create_user_table extends Migration
{
    /**
     * @var string
     */
    public $table = 'user';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned()->notNull()->comment('Auto increment id'),
            'sub' => $this->string(20)->notNull()->comment('Subject of Cyntelli auth'),
            'social_sub' => $this->string(128)->null()->comment('Subject of social auth'),
            'social_type' => $this->string(16)->null()->comment('Social type'),
            'username' => $this->string(128)->notNull()->comment('Username'),
            'family_name' => $this->string(32)->null()->comment('First name'),
            'given_name' => $this->string(32)->null()->comment('Last name'),
            'email' => $this->string(255)->notNull()->comment('Email'),
            'avatar' => $this->string(2048)->null()->comment('Avatar'),
            'last_login_ip' => $this->string(64)->null()->comment('Last login IP address'),
            'last_login_at' => $this->integer(10)->unsigned()->null()->comment('Last login unixtime'),
            'status' => 'ENUM("0", "1") NOT NULL DEFAULT "1" COMMENT "0:inactive 1: active, ref:taxonomies.value of type name[user_status]"',
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
