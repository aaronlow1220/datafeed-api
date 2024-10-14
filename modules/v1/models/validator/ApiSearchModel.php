<?php

namespace v1\models\validator;

use yii\base\Model;

/**
 * base search model.
 *
 * @OA\Schema()
 */
class ApiSearchModel extends Model
{
    /**
     * @OA\Property(property="page", type="integer", title="Current page", description="Current page", default=1, minimum=1)
     *
     * @var int
     */
    public $page;

    /**
     * @OA\Property(property="pageSize", type="integer", description="Page size", minimum=1, maximum=50, default=20)
     *
     * @var int
     */
    public $pageSize;

    /**
     * @OA\Property(property="sort", type="string", description="Sort column ex: -id means desc by id, id means asc by id", default="-id")
     *
     * @var string
     */
    public $sort;

    /**
     * @OA\Property(property="fields", type="string", description="Select specific fields, using comma be a seperator", default=null)
     *
     * @var string
     */
    public $fields;

    /**
     * @return array<int, mixed>
     */
    public function rules()
    {
        return [
            [['page', 'pageSize'], 'integer', 'min' => 1],
            [['sort', 'fields'], 'string'],
        ];
    }
}
