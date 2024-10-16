<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * @OA\Schema(
 *   schema="Datafeed",
 *   title="Datafeed Model",
 *   description="This model is used to access datafeed data",
 *   required={"id", "datafeedid", "status", "created_by", "created_at", "updated_by", "updated_at"},
 *   @OA\Property(property="id", type="integer", description="Auto increment id #autoIncrement #pk"),
 *   @OA\Property(property="datafeedid", type="string", description="Datafeed id", maxLength=255),
 *   @OA\Property(property="condition", type="string", description="Condition", maxLength=255),
 *   @OA\Property(property="availability", type="string", description="Availability", maxLength=255),
 *   @OA\Property(property="description", type="string", description="Description", maxLength=2048),
 *   @OA\Property(property="image_link", type="string", description="Image link", maxLength=2048),
 *   @OA\Property(property="link", type="string", description="Link", maxLength=2048),
 *   @OA\Property(property="title", type="string", description="Title", maxLength=255),
 *   @OA\Property(property="price", type="string", description="Price", maxLength=255),
 *   @OA\Property(property="sale_price", type="string", description="Sale price", maxLength=255),
 *   @OA\Property(property="gtin", type="string", description="Gtin", maxLength=255),
 *   @OA\Property(property="mpn", type="string", description="Mpn", maxLength=255),
 *   @OA\Property(property="brand", type="string", description="Brand", maxLength=255),
 *   @OA\Property(property="google_product_category", type="string", description="Google product category", maxLength=255),
 *   @OA\Property(property="item_group_id", type="string", description="Item group id", maxLength=255),
 *   @OA\Property(property="custom_label_0", type="string", description="Custom label 0", maxLength=255),
 *   @OA\Property(property="custom_label_1", type="string", description="Custom label 1", maxLength=255),
 *   @OA\Property(property="custom_label_2", type="string", description="Custom label 2", maxLength=255),
 *   @OA\Property(property="custom_label_3", type="string", description="Custom label 3", maxLength=255),
 *   @OA\Property(property="custom_label_4", type="string", description="Custom label 4", maxLength=255),
 *   @OA\Property(property="status", type="string", description="1: active 2:inactive, ref:taxonomies.value of type name[datafeed_status]", default="1", enum={"1", "2"}),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime"),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime")
 * )
 *
 * @version 1.0.0
 */
class Datafeed extends ActiveRecord
{
    /**
     * Return table name of datafeed.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'datafeed';
    }

    /**
     * Use timestamp to store time of login, update and create
     *
     * @return array<int, mixed>
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeTypecastBehavior::class,
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterFind' => true,
            ],
            [
                'class' => BlameableBehavior::class,
                'defaultValue' => 0,
            ],
            TimestampBehavior::class
        ];
    }

    /**
     * rules
     *
     * @return array<int, mixed>
     */
    public function rules()
    {
        return [
            [['datafeedid', 'condition', 'availability', 'description', 'image_link', 'link', 'title', 'price', 'sale_price', 'gtin', 'mpn', 'brand', 'google_product_category', 'item_group_id', 'custom_label_0', 'custom_label_1', 'custom_label_2', 'custom_label_3', 'custom_label_4', 'status'], 'trim'],
            [['id', 'client_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['datafeedid', 'condition', 'availability', 'description', 'image_link', 'link', 'title', 'price', 'sale_price', 'gtin', 'mpn', 'brand', 'google_product_category', 'item_group_id', 'custom_label_0', 'custom_label_1', 'custom_label_2', 'custom_label_3', 'custom_label_4', 'status'], 'string'],
            [['status'], 'in', 'range'=>['1', '2']],
            [['status'], 'default', 'value'=>'1']
        ];
    }

    /**
     * fields
     *
     * @return array<string, mixed>
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['created_by']);
        unset($fields['updated_by']);
        return $fields;
    }

    /**
     * return extra fields
     *
     * @return string[]
     */
    public function extraFields()
    {
        return ['item_group'];
    }
}
