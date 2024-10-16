<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="TaxonomyType",
 *   title="TaxonomyType Model",
 *   description="This model is used to access taxonomy_type data",
 *   required={"id", "name", "description", "created_at", "updated_at", "created_by", "updated_by"},
 *   @OA\Property(property="id", type="integer", description="id #autoIncrement #pk", maxLength=10),
 *   @OA\Property(property="name", type="string", description="only allow [A-Za-z0-9\_], except "" "" and ""#""", maxLength=64),
 *   @OA\Property(property="description", type="string", description="description", maxLength=64),
 *   @OA\Property(property="created_at", type="integer", description="unixtime", maxLength=10),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime", maxLength=10),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id", maxLength=20),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id", maxLength=20)
 * )
 *
 * @version 1.0.0
 */
class TaxonomyType extends ActiveRecord
{
    use UserCreatorTrait;
    use UserUpdaterTrait;

    /**
     * Return table name of taxonomy_type.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'taxonomy_type';
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
            [['name', 'description'], 'trim'],
            [['id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name', 'description'], 'string'],
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
        return ['creator', 'updater', 'taxonomies'];
    }

    /**
     * Get taxonomies.
     *
     * @return ActiveQuery
     */
    public function getTaxonomies()
    {
        return $this->hasMany(Taxonomy::class, ['type_id' => 'id']);
    }
}
