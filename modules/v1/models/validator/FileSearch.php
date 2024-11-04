<?php

namespace v1\models\validator;

/**
 * File search model which supports the search with keyword.
 *
 *  @OA\Schema(
 *   schema="FileSearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class FileSearch extends ApiSearchModel
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

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['keyword'], 'string'];

        return $rules;
    }
}