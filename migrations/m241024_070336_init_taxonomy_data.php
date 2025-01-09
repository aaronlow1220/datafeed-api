<?php

use yii\db\Migration;

/**
 * Class m241024_070336_init_taxonomy_data.
 */
class m241024_070336_init_taxonomy_data extends Migration
{
    /**
     * @var string
     */
    private $typeTable = 'taxonomy_type';

    /**
     * @var string
     */
    private $table = 'taxonomy';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $types = [
            ['datafeed_status', 'Datafeed Status', time(), time(), 0, 0],
            ['user_status', 'User Status', time(), time(), 0, 0],
            ['feed_file_status', 'Feed File Status', time(), time(), 0, 0],
            ['data_version_status', 'Data Version Status', time(), time(), 0, 0],
            ['platform_sftp', 'Platform SFTP', time(), time(), 0, 0],
        ];

        $this->batchInsert($this->typeTable, ['name', 'description', 'created_at', 'updated_at', 'created_by', 'updated_by'], $types);

        $taxonomies = [
            [ // datafeed_status
                ['停用', '0', 1, '0', '', time(), time(), 0, 0],
                ['啟用', '1', 1, '0', '', time(), time(), 0, 0],
            ],
            [ // user_status
                ['停用', '0', 1, '0', '', time(), time(), 0, 0],
                ['啟用', '1', 1, '0', '', time(), time(), 0, 0],
            ],
            [ // feed_file_status
                ['停用', '0', 1, '0', '', time(), time(), 0, 0],
                ['啟用', '1', 1, '0', '', time(), time(), 0, 0],
            ],
            [ // data_version_status
                ['失敗', '0', 1, '0', '', time(), time(), 0, 0],
                ['成功', '1', 1, '0', '', time(), time(), 0, 0],
                ['待處理', '1', 1, '0', '', time(), time(), 0, 0],
                ['處理中', '1', 1, '0', '', time(), time(), 0, 0],
            ],
            [ // platform_sftp_status
                ['否', '0', 1, '0', '', time(), time(), 0, 0],
                ['是', '1', 1, '0', '', time(), time(), 0, 0],
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

            $this->batchInsert($this->table, ['label', 'value', 'sort', 'is_default', 'description', 'created_at', 'updated_at', 'created_by', 'updated_by', 'type_id'], $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241024_070336_init_taxonomy_data cannot be reverted.\n";

        return false;
    }
}
