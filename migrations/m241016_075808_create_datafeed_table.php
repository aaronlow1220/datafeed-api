<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%datafeed}}`.
 */
class m241016_075808_create_datafeed_table extends Migration
{
    /**
     * @var string
     */
    public $table = 'datafeed';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned()->notNull()->comment('Auto increment id'),
            'client_id' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: client.id'),
            'datafeedid' => $this->string(255)->notNull()->comment('Datafeed id'),
            'condition' => $this->string(255)->comment('Condition'),
            'availability' => $this->string(255)->comment('Availability'),
            'description' => $this->string(2048)->comment('Description'),
            'image_link' => $this->string(2048)->comment('Image link'),
            'link' => $this->string(2048)->comment('Link'),
            'title' => $this->string(255)->comment('Title'),
            'price' => $this->string(255)->comment('Price'),
            'sale_price' => $this->string(255)->comment('Sale price'),
            'gtin' => $this->string(255)->comment('Gtin'),
            'mpn' => $this->string(255)->comment('Mpn'),
            'brand' => $this->string(255)->comment('Brand'),
            'google_product_category' => $this->string(255)->comment('Google product category'),
            'item_group_id' => $this->string(255)->comment('Item group id'),
            'currency' => $this->string(3)->comment('Currency'),
            'custom_label_0' => $this->string(255)->comment('Custom label 0'),
            'custom_label_1' => $this->string(255)->comment('Custom label 1'),
            'custom_label_2' => $this->string(255)->comment('Custom label 2'),
            'custom_label_3' => $this->string(255)->comment('Custom label 3'),
            'custom_label_4' => $this->string(255)->comment('Custom label 4'),
            'version' => $this->string(14)->notNull()->comment('Version date:YmdHis'),
            'status' => 'ENUM("0", "1") NOT NULL DEFAULT "1" COMMENT "0:inactive 1:active, ref:taxonomies.value of type name[datafeed_status]"',
            'created_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'created_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            'updated_by' => $this->bigInteger(20)->unsigned()->notNull()->comment('ref: > user.id'),
            'updated_at' => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
        ]);

        $this->createIndex('INDEX_CLIENT_ID', $this->table, 'client_id', false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
