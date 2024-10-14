<?php

namespace v1\models\validator;

/**
 * Client search model which supports the search with keyword and enabled values.
 *
 *  @OA\Schema(
 *   schema="ClientSearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class ClientSearch extends ApiSearchModel
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
