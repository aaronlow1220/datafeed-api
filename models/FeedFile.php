<?php

namespace app\models;

use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @OA\Schema(
 *   schema="FeedFile",
 *   title="FeedFile Model",
 *   description="This model is used to access feed_file data",
 *   required={"id", "client_id", "platform_id", "file_id", "filter", "utm", "status", "created_by", "created_at", "updated_by", "updated_at"},
 *   @OA\Property(property="id", type="integer", description="Auto increment id #autoIncrement #pk"),
 *   @OA\Property(property="client_id", type="integer", description="ref: client.id"),
 *   @OA\Property(property="platform_id", type="integer", description="ref: platform.id"),
 *   @OA\Property(property="file_id", type="integer", description="ref: file.id"),
 *   @OA\Property(property="filter", type="string", description="Custom filter, JSON format"),
 *   @OA\Property(property="utm", type="string", description="Utm parameter", maxLength=255),
 *   @OA\Property(property="status", type="string", description="0:inactive 1:active, ref:taxonomies.value of type name[feed_file_status]", default="1", enum={"0", "1"}),
 *   @OA\Property(property="created_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="created_at", type="integer", description="unixtime"),
 *   @OA\Property(property="updated_by", type="integer", description="ref: > user.id"),
 *   @OA\Property(property="updated_at", type="integer", description="unixtime")
 * )
 *
 * @version 1.0.0
 */
class FeedFile extends ActiveRecord
{
    use TaxonomyTrait;
    use UserCreatorTrait;
    use UserUpdaterTrait;

    /**
     * Return table name of feed_file.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'feed_file';
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
            [['filter', 'utm', 'status'], 'trim'],
            [['id', 'client_id', 'platform_id', 'file_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['filter', 'utm', 'status'], 'string'],
            [['status'], 'in', 'range' => ['0', '1']],
            [['status'], 'default', 'value' => '1'],
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
        return ['statusLabel', 'client', 'platform', 'file', 'creator', 'updater'];
    }

    /**
     * Get status label.
     *
     * @return ActiveQuery
     */
    public function getStatusLabel(): ActiveQuery
    {
        return $this->getTaxonomy('feed_file_status', 'status');
    }

    /**
     * Get client.
     *
     * @return ActiveQuery
     */
    public function getClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * Get platform.
     *
     * @return ActiveQuery
     */
    public function getPlatform(): ActiveQuery
    {
        return $this->hasOne(Platform::class, ['id' => 'platform_id']);
    }

    /**
     * Get file.
     *
     * @return ActiveQuery
     */
    public function getFile(): ActiveQuery
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }
}
