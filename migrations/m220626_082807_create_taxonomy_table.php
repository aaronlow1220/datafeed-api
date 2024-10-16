<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%taxonomy}}`.
 */
class m220626_082807_create_taxonomy_table extends Migration
{
    /**
     * @var string
     */
    private $table = 'taxonomy';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey(20)->unsigned(),
            'label' => $this->string(64)->notNull()->comment('名稱'),
            'value' => $this->string(64)->notNull()->comment('unique value with type_id'),
            'type_id' => $this->integer(10)->unsigned()->notNull()->comment('ref: > taxonomy_types.id'),
            'description' => $this->string(255)->null(),
            'sort' => $this->integer(10)->notNull()->defaultValue('1'),
            'is_default' => "ENUM('0','1') DEFAULT '0' NOT NULL COMMENT '是否為預設 0: 否, 1: 是'",
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            'updated_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            'created_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'updated_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
        ]);

        // create index
        $this->createIndex('INDEX_TYPE_VALUE', $this->table, ['type_id', 'value'], false);

        // initail data
        $types = [
            ['sys_status', '系統啟用狀態', time(), time(), 0, 0],
        ];
        foreach ($types as $type) {
            $this->batchInsert('taxonomy_type', ['name', 'description', 'created_at', 'updated_at', 'created_by', 'updated_by'], $types);
        }

        $taxonomies = [
            [
                ['啟用', '1', 1, '0', time(), time(), 0, 0],
                ['停用', '0', 1, '0', time(), time(), 0, 0],
            ],
        ];
        foreach ($taxonomies as $idx => $items) {
            $sql = sprintf('select * from taxonomy_type where name like "%s"', $types[$idx][0]);
            $type = $this->db->createCommand($sql)
                ->queryOne();

            $data = [];
            foreach ($items as $item) {
                $data[] = array_merge($item, [$type['id']]);
            }

            $this->batchInsert($this->table, ['label', 'value', 'sort', 'is_default', 'created_at', 'updated_at', 'created_by', 'updated_by', 'type_id'], $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
