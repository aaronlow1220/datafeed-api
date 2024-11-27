<?php

namespace v1\models\validator;

use app\components\enum\DataVersionStatusEnum;

/**
 * Data Version search model which supports the search with keyword.
 *
 *  @OA\Schema(
 *   schema="DataVersionSearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class DataVersionSearch extends ApiSearchModel
{
    /**
     * @var null|string Query related models, using comma(,) be seperator
     * @OA\Property(enum={"client", "statusLabel", "creator", "updator"}, default=null)
     */
    public $expand;

    /**
     * @var null|string keyword for search
     * @OA\Property(default=null)
     */
    public $keyword;

    /**
     * @var null|string[] status values for search, 0:失敗 1:成功 2:待處理 3:處理中
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/DataVersion/properties/status"), default=null)
     */
    public $statusValues;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['keyword'], 'string'];
        $rules[] = [['statusValues'], 'each', 'rule' => ['in', 'range' => DataVersionStatusEnum::toArray()]];

        return $rules;
    }
}
