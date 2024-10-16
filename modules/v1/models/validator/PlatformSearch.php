<?php

namespace v1\models\validator;

/**
 * Platform search model which supports the search with keyword.
 *
 *  @OA\Schema(
 *   schema="PlatformSearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class PlatformSearch extends ApiSearchModel
{
    /**
     * @var null|string Query related models, using comma(,) be seperator
     * @OA\Property(enum={"creator", "updater"}, default=null)
     */
    public $expand;

    /**
     * @var null|string keyword for search
     * @OA\Property(default=null)
     */
    public $keyword;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['keyword'], 'string'];

        return $rules;
    }
}
