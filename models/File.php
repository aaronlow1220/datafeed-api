<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="File",
 *   title="File Model",
 *   description="This model is used to access file data",
 *   required={"id", "mime", "extension", "filename", "path", "size", "created_by", "created_at", "updated_by", "updated_at"},
 *   @OA\Property(property="id", type="integer", description="Auto increment id #autoIncrement #pk"),
 *   @OA\Property(property="mime", type="string", description="MIME type", maxLength=128),
 *   @OA\Property(property="extension", type="string", description="Extension", maxLength=8),
 *   @OA\Property(property="filename", type="string", description="File name", maxLength=255),
 *   @OA\Property(property="path", type="string", description="Path", maxLength=255),
 *   @OA\Property(property="size", type="integer", description="檔案大小 in byte"),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime"),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime")
 * )
 *
 * @version 1.0.0
 */
class File extends ActiveRecord
{
    /**
     * Return table name of file.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'file';
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
            [['mime', 'extension', 'filename', 'path'], 'trim'],
            [['id', 'size', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['mime', 'extension', 'filename', 'path'], 'string'],
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
