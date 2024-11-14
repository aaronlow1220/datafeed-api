<?php

namespace v1\controllers;

use InvalidArgumentException;
use Throwable;
use app\modules\v1\Module;
use v1\components\ActiveApiController;
use v1\components\version\DataVersionSearchService;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="DataVersion",
 *     description="Everything about your DataVersion",
 * )
 *
 * @OA\Get(
 *     path="/data-version/{id}",
 *     summary="Get",
 *     description="Get DataVersion by particular id",
 *     operationId="getDataVersion",
 *     tags={"DataVersion"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="DataVersion id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/DataVersion/properties/id")
 *     ),
 *     @OA\Parameter(
 *         name="fields",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/fields")
 *     ),
 *     @OA\Parameter(
 *         name="expand",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/DataVersionSearch/properties/expand")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/DataVersion")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/data-version",
 *     summary="Create",
 *     description="Create a record of DataVersion",
 *     operationId="createDataVersion",
 *     tags={"DataVersion"},
 *     @OA\RequestBody(
 *         description="DataVersion object that needs to be added",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="client_id", ref="#/components/schemas/DataVersion/properties/client_id"),
 *                  @OA\Property(property="hash", ref="#/components/schemas/DataVersion/properties/hash")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/DataVersion")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/data-version/{id}",
 *     summary="Update",
 *     description="Update a record of DataVersion",
 *     operationId="updateDataVersion",
 *     tags={"DataVersion"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="DataVersion id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/DataVersion/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="DataVersion object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="client_id", ref="#/components/schemas/DataVersion/properties/client_id"),
 *                  @OA\Property(property="hash", ref="#/components/schemas/DataVersion/properties/hash")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/DataVersion")
 *     )
 * )
 *
 * @version 1.0.0
 */
class DataVersionController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\DataVersion';

    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param DataVersionSearchService $dataVersionSearchService
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function __construct(string $id, Module $module, private DataVersionSearchService $dataVersionSearchService, array $config = [])
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
     *     path="/data-version/search",
     *     summary="Search",
     *     description="Search DataVersion by particular params",
     *     operationId="searchDataVersion",
     *     tags={"DataVersion"},
     *     @OA\RequestBody(
     *         description="search DataVersion",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/DataVersionSearch")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/DataVersion")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search DataVersion.
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->dataVersionSearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
