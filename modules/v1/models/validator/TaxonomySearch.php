<?php

namespace v1\models\validator;

/**
 * Taxonomy search model which supports the search with keyword, typeName, typeIds.
 *
 *  @OA\Schema(
 *   schema="TaxonomySearch",
 *   oneOf={
 *      @OA\Schema(ref="#/components/schemas/ApiSearchModel"),
 *   }
 * )
 */
class TaxonomySearch extends ApiSearchModel
{
    /**
     * @var null|string Query related models, using comma(,) be seperator
     * @OA\Property(enum={"type", "creator", "updater"}, default=null)
     */
    public $expand;

    /**
     * @var null|string filter name or description by keyword
     * @OA\Property(default=null)
     */
    public $keyword;

    /**
     * @var null|string filter by type name
     * @OA\Property(default=null)
     */
    public $typeName;

    /**
     * @var null|int[] filter by type ids
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/Taxonomy/properties/type_id"), default=null)
     */
    public $typeIds;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['keyword', 'expand', 'typeName'], 'string'];
        $rules[] = [['typeIds'], 'each', 'rule' => ['integer']];

        return $rules;
    }
}
