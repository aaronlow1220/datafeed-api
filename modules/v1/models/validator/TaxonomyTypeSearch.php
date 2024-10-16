<?php

namespace v1\models\validator;

/**
 * TaxonomyType search model which supports the search with keyword.
 *
 *  @OA\Schema(
 *   schema="TaxonomyTypeSearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class TaxonomyTypeSearch extends ApiSearchModel
{
    /**
     * @var null|string filter name or description by keyword
     * @OA\Property(default=null)
     */
    public $keyword;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['keyword'], 'string'];

        return $rules;
    }
}
