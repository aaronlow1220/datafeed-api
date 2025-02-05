<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="User",
 *   title="User Model",
 *   description="This model is used to access user data",
 *   required={"id", "sub", "username", "email", "status", "created_by", "created_at", "updated_by", "updated_at"},
 *   @OA\Property(property="id", type="integer", description="Auto increment id #autoIncrement #pk"),
 *   @OA\Property(property="sub", type="string", description="Subject of Cyntelli auth", maxLength=20),
 *   @OA\Property(property="social_sub", type="string", description="Subject of social auth", maxLength=128),
 *   @OA\Property(property="social_type", type="string", description="Social type", maxLength=16),
 *   @OA\Property(property="username", type="string", description="Username", maxLength=128),
 *   @OA\Property(property="family_name", type="string", description="First name", maxLength=32),
 *   @OA\Property(property="given_name", type="string", description="Last name", maxLength=32),
 *   @OA\Property(property="email", type="string", description="Email", maxLength=255),
 *   @OA\Property(property="avatar", type="string", description="Avatar", maxLength=2048),
 *   @OA\Property(property="last_login_ip", type="string", description="Last login IP address", maxLength=64),
 *   @OA\Property(property="last_login_at", type="integer", description="Last login unixtime"),
 *   @OA\Property(property="status", type="string", description="1: active 2:inactive, ref:taxonomies.value of type name[user_status]", default="1", enum={"1", "2"}),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime"),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime")
 * )
 *
 * @version 1.0.0
 */
class User extends ActiveRecord
{
    /**
     * Return table name of user.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'user';
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
            [['sub', 'social_sub', 'social_type', 'username', 'family_name', 'given_name', 'email', 'avatar', 'last_login_ip', 'status'], 'trim'],
            [['id', 'last_login_at', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['sub', 'social_sub', 'social_type', 'username', 'family_name', 'given_name', 'email', 'avatar', 'last_login_ip', 'status'], 'string'],
            [['status'], 'in', 'range' => ['1', '2']],
            [['status'], 'default', 'value' => '1'],
        ];
    }

    /**
     * scenarios.
     *
     * @return array<string, mixed>
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['login'] = ['last_login_ip', 'last_login_at'];

        return $scenarios;
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
        return [];
    }
}
