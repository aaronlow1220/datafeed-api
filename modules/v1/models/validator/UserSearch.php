<?php

namespace v1\models\validator;

use app\components\enum\UserStatusEnum;

/**
 * User search model which supports the search with keyword.
 *
 *  @OA\Schema(
 *   schema="UserSearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class UserSearch extends ApiSearchModel
{
    /**
     * @var null|string Query related models, using comma(,) be seperator
     * @OA\Property(enum={"statusLabel"}, default=null)
     */
    public $expand;

    /**
     * @var null|string keyword for search
     * @OA\Property(default=null)
     */
    public $keyword;

    /**
     * @var null|string[] status values for search, 0:停用 1:啟用
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/User/properties/status"), default=null)
     */
    public $statusValues;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['keyword'], 'string'];
        $rules[] = [['statusValues'], 'each', 'rule' => ['in', 'range' => UserStatusEnum::toArray()]];

        return $rules;
    }
}
