<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="Client",
 *   title="Client Model",
 *   description="This model is used to access client data",
 *   required={"id", "name", "data", "created_by", "created_at", "updated_by", "updated_at"},
 *   @OA\Property(property="id", type="integer", description="Auto increment id #autoIncrement #pk"),
 *   @OA\Property(property="label", type="string", description="Client label", maxLength=255),
 *   @OA\Property(property="name", type="string", description="Client name", maxLength=255),
 *   @OA\Property(property="data", type="string", description="Data mapping rule, JSON format"),
 *   @OA\Property(property="password", type="string", description="Client password", maxLength=255),
 *   @OA\Property(property="currency", type="string", description="Currency code", maxLength=3),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime"),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime")
 * )
 *
 * @version 1.0.0
 */
class Client extends ActiveRecord
{
    use UserCreatorTrait;
    use UserUpdaterTrait;

    /**
     * Return table name of client.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client';
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
            [['name', 'label', 'data', 'currency'], 'trim'],
            [['id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['name', 'label', 'data', 'currency', 'password'], 'string'],
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
        return ['creator', 'updater'];
    }
}
