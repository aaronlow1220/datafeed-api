<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="Platform",
 *   title="Platform Model",
 *   description="This model is used to access platform data",
 *   required={"id", "name", "data", "created_by", "created_at", "updated_by", "updated_at"},
 *   @OA\Property(property="id", type="integer", description="Auto increment id #autoIncrement #pk"),
 *   @OA\Property(property="label", type="string", description="Platform label", maxLength=255),
 *   @OA\Property(property="name", type="string", description="Platform name", maxLength=255),
 *   @OA\Property(property="data", type="string", description="Data mapping rule, JSON format"),
 *   @OA\Property(property="sftp", type="string", description="0:false 1:true, ref:taxonomies.value of type name[platform_sftp]", default="0", enum={"0", "1"}),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime"),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime")
 * )
 *
 * @version 1.0.0
 */
class Platform extends ActiveRecord
{
    use UserCreatorTrait;
    use UserUpdaterTrait;

    /**
     * Return table name of platform.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'platform';
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
            [['name', 'label', 'data', 'sftp'], 'trim'],
            [['id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['name', 'label', 'data', 'sftp'], 'string'],
            [['sftp'], 'in', 'range' => ['0', '1']],
            [['sftp'], 'default', 'value' => '0'],
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
