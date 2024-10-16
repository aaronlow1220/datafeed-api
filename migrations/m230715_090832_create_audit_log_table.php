<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%audit_log}}`.
 */
class m230715_090832_create_audit_log_table extends Migration
{
    /**
     * @var string
     */
    private $table = 'audit_log';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey(20)->unsigned()->notNull()->comment('流水號'),
            'table_name' => $this->string(64)->notNull()->comment('資料表名稱'),
            'table_id' => $this->bigInteger(20)->unsigned()->notNull()->comment('資料表流水號'),
            'changed_attributes' => $this->text()->notNull()->comment('變更資料, 格式: JSON'),
            'created_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: user.id'),
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
