<?php

namespace v1\models\validator;

use yii\base\Model;

class DatafeedFilter extends Model
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $condition;

    /**
     * @var string
     */
    public $google_product_category;

    /**
     * @var string
     */
    public $brand;

    /**
     * @var string
     */
    public $item_group_id;

    /**
     * @var string
     */
    public $custom_label_0;

    /**
     * @var string
     */
    public $custom_label_1;

    /**
     * @var string
     */
    public $custom_label_2;

    /**
     * @var string
     */
    public $custom_label_3;

    /**
     * @var string
     */
    public $custom_label_4;

    /**
     * rules.
     *
     * @return array<int, mixed>
     */
    public function rules()
    {
        return [
            [['title', 'description', 'condition', 'google_product_category', 'brand', 'item_group_id', 'custom_label_0', 'custom_label_1', 'custom_label_2', 'custom_label_3', 'custom_label_4'], 'safe'],
        ];
    }
}
