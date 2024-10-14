<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="DataVersion",
 *   title="DataVersion Model",
 *   description="This model is used to access data_version data",
 *   required={"id", "client_id", "hash", "created_by", "created_at", "updated_by", "updated_at"},
 *   @OA\Property(property="id", type="integer", description="Auto increment id #autoIncrement #pk"),
 *   @OA\Property(property="client_id", type="integer", description="Client id"),
 *   @OA\Property(property="hash", type="string", description="Data hash", maxLength=255),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime"),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime")
 * )
 *
 * @version 1.0.0
 */
class DataVersion extends ActiveRecord
{
    /**
     * Return table name of data_version.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'data_version';
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
            [['hash'], 'trim'],
            [['id', 'client_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['hash'], 'string'],
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
        return ['client'];
    }
}
