<?php

namespace v1\controllers;

use InvalidArgumentException;
use Throwable;
use Yii;
use app\components\core\FileRepo;
use app\components\datafeed\FeedFileRepo;
use app\modules\v1\Module;
use v1\components\ActiveApiController;
use v1\components\datafeed\FeedFileCreateService;
use v1\components\datafeed\FeedFileSearchService;
use v1\components\datafeed\FeedFileUpdateService;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\Response;

/**
 * @OA\Tag(
 *     name="FeedFile",
 *     description="Everything about your FeedFile",
 * )
 *
 * @OA\Get(
 *     path="/feed-file",
 *     summary="List",
 *     description="List all FeedFile",
 *     operationId="listFeedFile",
 *     tags={"FeedFile"},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/page")
 *     ),
 *     @OA\Parameter(
 *         name="pageSize",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/pageSize")
 *     ),
 *     @OA\Parameter(
 *         name="sort",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/sort")
 *     ),
 *     @OA\Parameter(
 *         name="fields",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/fields")
 *     ),
 *     @OA\Parameter(
 *         name="expand",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/FeedFileSearch/properties/expand")
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
     * @param FeedFileRepo $feedFileRepo
     * @param FileRepo $fileRepo
     * @param FeedFileCreateService $feedFileCreateService
     * @param FeedFileUpdateService $feedFileUpdateService
     * @param array<string, mixed> $config
     */
    public function __construct($id, $module, private FeedFileSearchService $feedFileSearchService, private FeedFileRepo $feedFileRepo, private FileRepo $fileRepo, private FeedFileCreateService $feedFileCreateService, private FeedFileUpdateService $feedFileUpdateService, $config = [])
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
        unset($actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
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
     *                  @OA\Property(property="client_id", ref="#/components/schemas/FeedFile/properties/client_id"),
     *                  @OA\Property(property="platform_id", ref="#/components/schemas/FeedFile/properties/platform_id"),
     *                  @OA\Property(property="file_id", ref="#/components/schemas/FeedFile/properties/file_id"),
     *                  @OA\Property(property="filter", ref="#/components/schemas/FeedFile/properties/filter"),
     *                  @OA\Property(property="utm", ref="#/components/schemas/FeedFile/properties/utm"),
     *                  @OA\Property(property="status", ref="#/components/schemas/FeedFile/properties/status"),
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
     * @return ActiveRecord
     */
    public function actionCreate()
    {
        try {
            $params = $this->getRequestParams();

            return $this->feedFileCreateService->create($params);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
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
     *                  @OA\Property(property="client_id", ref="#/components/schemas/FeedFile/properties/client_id"),
     *                  @OA\Property(property="platform_id", ref="#/components/schemas/FeedFile/properties/platform_id"),
     *                  @OA\Property(property="file_id", ref="#/components/schemas/FeedFile/properties/file_id"),
     *                  @OA\Property(property="filter", ref="#/components/schemas/FeedFile/properties/filter"),
     *                  @OA\Property(property="utm", ref="#/components/schemas/FeedFile/properties/utm"),
     *                  @OA\Property(property="status", ref="#/components/schemas/FeedFile/properties/status"),
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
     * @param int $id
     * @return ActiveRecord
     */
    public function actionUpdate($id)
    {
        try {
            $params = $this->getRequestParams();
            $feedFile = $this->feedFileRepo->findOne($id);

            return $this->feedFileUpdateService->update($feedFile, $params);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/feed-file/feed/{id}",
     *     summary="Download",
     *     description="Download File by particular id",
     *     operationId="downloadFile",
     *     tags={"FeedFile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="FeedFile id",
     *         required=true,
     *         @OA\Schema(ref="#/components/schemas/FeedFile/properties/id")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     )
     * )
     *
     * Download FeedFile.
     *
     * @param int $id
     * @return Response
     */
    public function actionFeed(int $id): Response
    {
        try {
            $feedFile = $this->feedFileRepo->findOne($id);
            $file = $this->fileRepo->findOne($feedFile['file_id']);

            if (!file_exists($file['path'])) {
                throw new HttpException(400, 'File not found');
            }

            return Yii::$app->response->sendFile($file['path']);
        } catch (Throwable $e) {
            throw $e;
        }
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
     * Search FeedFile.
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
