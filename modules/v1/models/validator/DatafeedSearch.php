<?php

namespace v1\models\validator;

use app\components\enum\DatafeedStatusEnum;

/**
 * Datafeed search model which supports the search with keyword.
 *
 *  @OA\Schema(
 *   schema="DatafeedSearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class DatafeedSearch extends ApiSearchModel
{
    /**
     * @var null|string Query related models, using comma(,) be seperator
     * @OA\Property(enum={"statusLabel", "client", "creator", "updater"}, default=null)
     */
    public $expand;

    /**
     * @var null|string keyword for search
     * @OA\Property(default=null)
     */
    public $keyword;

    /**
     * @var null|string[] status values for search, 0:停用 1:啟用
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/Datafeed/properties/status"), default=null)
     */
    public $statusValues;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['keyword'], 'string'];
        $rules[] = [['statusValues'], 'each', 'rule' => ['in', 'range' => DatafeedStatusEnum::toArray()]];

        return $rules;
    }
}
