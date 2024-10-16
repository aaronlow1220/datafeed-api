<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="Taxonomy",
 *   title="Taxonomy Model",
 *   description="This model is used to access taxonomy data",
 *   required={"id", "label", "value", "type_id", "sort", "is_default", "created_at", "updated_at", "created_by", "updated_by"},
 *   @OA\Property(property="id", type="integer", description="id #autoIncrement #pk", maxLength=20),
 *   @OA\Property(property="label", type="string", description="名稱", maxLength=64),
 *   @OA\Property(property="value", type="string", description="unique value with type_id", maxLength=64),
 *   @OA\Property(property="type_id", type="integer", description="ref: > taxonomy_types.id", maxLength=10),
 *   @OA\Property(property="description", type="string", description="description", maxLength=255),
 *   @OA\Property(property="sort", type="integer", description="sort", maxLength=10, default=1),
 *   @OA\Property(property="is_default", type="string", description="是否為預設 0: 否, 1: 是", default="0", enum={"0", "1"}),
 * )
 *
 * @version 1.0.0
 */
class Taxonomy extends ActiveRecord
{
    use UserCreatorTrait;
    use UserUpdaterTrait;

    /**
     * Return table name of taxonomy.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'taxonomy';
    }

    /**
     * Use timestamp to store time of login, update and create.
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
            TimestampBehavior::class,
        ];
    }

    /**
     * rules.
     *
     * @return array<int, mixed>
     */
    public function rules()
    {
        return [
            [['label', 'value', 'description', 'is_default'], 'trim'],
            [['id', 'type_id', 'sort', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['label', 'value', 'description', 'is_default'], 'string'],
            [['sort'], 'default', 'value' => '1'],
            [['is_default'], 'in', 'range' => ['0', '1']],
        ];
    }

    /**
     * transactions.
     *
     * @return array<string, int>
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * fields.
     *
     * @return array<string, mixed>
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['created_by'], $fields['updated_by']);

        return $fields;
    }

    /**
     * return extra fields.
     *
     * @return string[]
     */
    public function extraFields()
    {
        return ['type'];
    }

    /**
     * return type.
     *
     * @return ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(TaxonomyType::class, ['id' => 'type_id']);
    }
}
