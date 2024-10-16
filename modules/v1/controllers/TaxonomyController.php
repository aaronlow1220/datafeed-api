<?php

namespace v1\controllers;

use Throwable;
use v1\components\ActiveApiController;
use v1\components\core\TaxonomySearchService;
use yii\base\InvalidArgumentException;
use yii\base\Module;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="Taxonomy",
 *     description="Everything about your Taxonomy",
 * )
 *
 * @OA\Get(
 *     path="/taxonomy/{id}",
 *     summary="Get",
 *     description="Get Taxonomy by particular id",
 *     operationId="getTaxonomy",
 *     tags={"Taxonomy"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Taxonomy id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Taxonomy/properties/id")
 *     ),
 *     @OA\Parameter(
 *         name="fields",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/fields")
 *     ),
 *     @OA\Parameter(
 *         name="expand",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/TaxonomySearch/properties/expand")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Taxonomy")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/taxonomy",
 *     summary="Create",
 *     description="Create a record of Taxonomy",
 *     operationId="createTaxonomy",
 *     tags={"Taxonomy"},
 *     @OA\RequestBody(
 *         description="Taxonomy object that needs to be added",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="label", ref="#/components/schemas/Taxonomy/properties/label"),
 *                  @OA\Property(property="value", ref="#/components/schemas/Taxonomy/properties/value"),
 *                  @OA\Property(property="type_id", ref="#/components/schemas/Taxonomy/properties/type_id"),
 *                  @OA\Property(property="description", ref="#/components/schemas/Taxonomy/properties/description"),
 *                  @OA\Property(property="sort", ref="#/components/schemas/Taxonomy/properties/sort"),
 *                  @OA\Property(property="is_default", ref="#/components/schemas/Taxonomy/properties/is_default"),
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Taxonomy")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/taxonomy/{id}",
 *     summary="Update",
 *     description="Update a record of Taxonomy",
 *     operationId="updateTaxonomy",
 *     tags={"Taxonomy"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Taxonomy id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Taxonomy/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="Taxonomy object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="label", ref="#/components/schemas/Taxonomy/properties/label"),
 *                  @OA\Property(property="value", ref="#/components/schemas/Taxonomy/properties/value"),
 *                  @OA\Property(property="type_id", ref="#/components/schemas/Taxonomy/properties/type_id"),
 *                  @OA\Property(property="description", ref="#/components/schemas/Taxonomy/properties/description"),
 *                  @OA\Property(property="sort", ref="#/components/schemas/Taxonomy/properties/sort"),
 *                  @OA\Property(property="is_default", ref="#/components/schemas/Taxonomy/properties/is_default"),
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Taxonomy")
 *     )
 * )
 *
 * @OA\Delete(
 *     path="/taxonomy/{id}",
 *     summary="Delete",
 *     description="Delete a record of Taxonomy",
 *     operationId="deleteTaxonomy",
 *     tags={"Taxonomy"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Taxonomy id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Taxonomy/properties/id")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation"
 *     )
 * )
 *
 * @version 1.0.0
 */
class TaxonomyController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Taxonomy';

    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param TaxonomySearchService $taxonomySearchService
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private TaxonomySearchService $taxonomySearchService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inherit}.
     *
     * @return array<string, mixed>
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index'], $actions['delete']);

        return $actions;
    }

    /**
     * @OA\Post(
     *     path="/taxonomy/search",
     *     summary="Search",
     *     description="Search Taxonomy by particular params",
     *     operationId="searchTaxonomy",
     *     tags={"Taxonomy"},
     *     @OA\RequestBody(
     *         description="search Taxonomy",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/TaxonomySearch")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/Taxonomy")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search Taxonomy
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->taxonomySearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
