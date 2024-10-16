<?php

namespace v1\controllers;

use Throwable;
use v1\components\ActiveApiController;
use v1\components\core\TaxonomyTypeSearchService;
use yii\base\InvalidArgumentException;
use yii\base\Module;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="TaxonomyType",
 *     description="Everything about your TaxonomyType",
 * )
 *
 * @OA\Get(
 *     path="/taxonomy-type/{id}",
 *     summary="Get",
 *     description="Get TaxonomyType by particular id",
 *     operationId="getTaxonomyType",
 *     tags={"TaxonomyType"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="TaxonomyType id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/TaxonomyType/properties/id")
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
 *         @OA\JsonContent(type="object", ref="#/components/schemas/TaxonomyType")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/taxonomy-type",
 *     summary="Create",
 *     description="Create a record of TaxonomyType",
 *     operationId="createTaxonomyType",
 *     tags={"TaxonomyType"},
 *     @OA\RequestBody(
 *         description="TaxonomyType object that needs to be added",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="name", ref="#/components/schemas/TaxonomyType/properties/name"),
 *                  @OA\Property(property="description", ref="#/components/schemas/TaxonomyType/properties/description"),
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/TaxonomyType")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/taxonomy-type/{id}",
 *     summary="Update",
 *     description="Update a record of TaxonomyType",
 *     operationId="updateTaxonomyType",
 *     tags={"TaxonomyType"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="TaxonomyType id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/TaxonomyType/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="TaxonomyType object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="name", ref="#/components/schemas/TaxonomyType/properties/name"),
 *                  @OA\Property(property="description", ref="#/components/schemas/TaxonomyType/properties/description"),
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/TaxonomyType")
 *     )
 * )
 *
 * @OA\Delete(
 *     path="/taxonomy-type/{id}",
 *     summary="Delete",
 *     description="Delete a record of TaxonomyType",
 *     operationId="deleteTaxonomyType",
 *     tags={"TaxonomyType"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="TaxonomyType id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/TaxonomyType/properties/id")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation"
 *     )
 * )
 *
 * @version 1.0.0
 */
class TaxonomyTypeController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\TaxonomyType';

    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param TaxonomyTypeSearchService $taxonomyTypeSearchService
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private TaxonomyTypeSearchService $taxonomyTypeSearchService, $config = [])
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
     *     path="/taxonomy-type/search",
     *     summary="Search",
     *     description="Search TaxonomyType by particular params",
     *     operationId="searchTaxonomyType",
     *     tags={"TaxonomyType"},
     *     @OA\RequestBody(
     *         description="search TaxonomyType",
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
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/TaxonomyType")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search TaxonomyType
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->taxonomyTypeSearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
