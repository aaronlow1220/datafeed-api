<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="AuditLog",
 *   title="AuditLog Model",
 *   description="This model is used to access audit_log data",
 *   @OA\Property(property="id", type="integer", description="流水號 #autoIncrement #pk", maxLength=20),
 *   @OA\Property(property="table_name", type="string", description="資料表名稱", maxLength=64),
 *   @OA\Property(property="table_id", type="integer", description="資料表流水號", maxLength=20),
 *   @OA\Property(property="changed_attributes", type="string", description="變更資料, 格式: JSON"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime", maxLength=10)
 * )
 *
 * @version 1.0.0
 */
class AuditLog extends ActiveRecord
{
    use UserCreatorTrait;

    /**
     * Return table name of audit_log.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'audit_log';
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
            [['table_name', 'changed_attributes'], 'trim'],
            [['id', 'table_id', 'created_by', 'created_at'], 'integer'],
            [['table_name', 'changed_attributes'], 'string'],
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
        unset($fields['created_by']);

        return $fields;
    }

    /**
     * return extra fields.
     *
     * @return string[]
     */
    public function extraFields()
    {
        return ['creator'];
    }
}
