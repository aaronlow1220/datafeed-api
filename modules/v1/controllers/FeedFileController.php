<?php

namespace v1\controllers;

use InvalidArgumentException;
use Throwable;
use app\modules\v1\Module;
use v1\components\ActiveApiController;
use v1\components\datafeed\FeedFileSearchService;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="FeedFile",
 *     description="Everything about your FeedFile",
 * )
 *
 * @OA\Get(
 *     path="/feed-file/{id}",
 *     summary="Get",
 *     description="Get FeedFile by particular id",
 *     operationId="getFeedFile",
 *     tags={"FeedFile"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="FeedFile id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/FeedFile/properties/id")
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
 *         @OA\JsonContent(type="object", ref="#/components/schemas/FeedFile")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/feed-file",
 *     summary="Create",
 *     description="Create a record of FeedFile",
 *     operationId="createFeedFile",
 *     tags={"FeedFile"},
 *     @OA\RequestBody(
 *         description="FeedFile object that needs to be added",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="id", ref="#/components/schemas/FeedFile/properties/id"),
 *                  @OA\Property(property="client_id", ref="#/components/schemas/FeedFile/properties/client_id"),
 *                  @OA\Property(property="platform_id", ref="#/components/schemas/FeedFile/properties/platform_id"),
 *                  @OA\Property(property="file_id", ref="#/components/schemas/FeedFile/properties/file_id"),
 *                  @OA\Property(property="filter", ref="#/components/schemas/FeedFile/properties/filter"),
 *                  @OA\Property(property="utm", ref="#/components/schemas/FeedFile/properties/utm"),
 *                  @OA\Property(property="status", ref="#/components/schemas/FeedFile/properties/status"),
 *                  @OA\Property(property="created_by", ref="#/components/schemas/FeedFile/properties/created_by"),
 *                  @OA\Property(property="created_at", ref="#/components/schemas/FeedFile/properties/created_at"),
 *                  @OA\Property(property="updated_by", ref="#/components/schemas/FeedFile/properties/updated_by"),
 *                  @OA\Property(property="updated_at", ref="#/components/schemas/FeedFile/properties/updated_at")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/FeedFile")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/feed-file/{id}",
 *     summary="Update",
 *     description="Update a record of FeedFile",
 *     operationId="updateFeedFile",
 *     tags={"FeedFile"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="FeedFile id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/FeedFile/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="FeedFile object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="id", ref="#/components/schemas/FeedFile/properties/id"),
 *                  @OA\Property(property="client_id", ref="#/components/schemas/FeedFile/properties/client_id"),
 *                  @OA\Property(property="platform_id", ref="#/components/schemas/FeedFile/properties/platform_id"),
 *                  @OA\Property(property="file_id", ref="#/components/schemas/FeedFile/properties/file_id"),
 *                  @OA\Property(property="filter", ref="#/components/schemas/FeedFile/properties/filter"),
 *                  @OA\Property(property="utm", ref="#/components/schemas/FeedFile/properties/utm"),
 *                  @OA\Property(property="status", ref="#/components/schemas/FeedFile/properties/status"),
 *                  @OA\Property(property="created_by", ref="#/components/schemas/FeedFile/properties/created_by"),
 *                  @OA\Property(property="created_at", ref="#/components/schemas/FeedFile/properties/created_at"),
 *                  @OA\Property(property="updated_by", ref="#/components/schemas/FeedFile/properties/updated_by"),
 *                  @OA\Property(property="updated_at", ref="#/components/schemas/FeedFile/properties/updated_at")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/FeedFile")
 *     )
 * )
 *
 * @version 1.0.0
 */
class FeedFileController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\FeedFile';

    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param FeedFileSearchService $feedFileSearchService
     * @param array<string, mixed> $config
     */
    public function __construct($id, $module, private FeedFileSearchService $feedFileSearchService, $config = [])
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

        unset($actions['index']);

        return $actions;
    }

    /**
     * @OA\Post(
     *     path="/feed-file/search",
     *     summary="Search",
     *     description="Search FeedFile by particular params",
     *     operationId="searchFeedFile",
     *     tags={"FeedFile"},
     *     @OA\RequestBody(
     *         description="search FeedFile",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/FeedFileSearch"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/FeedFile")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search FeedFile
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->feedFileSearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
