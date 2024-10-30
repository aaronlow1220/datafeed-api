<?php

namespace v1\controllers;

use InvalidArgumentException;
use Throwable;
use Yii;
use app\components\FileEntity;
use app\components\client\ClientRepo;
use app\components\core\FileRepo;
use app\components\core\FileService;
use app\components\datafeed\DatafeedService;
use v1\components\ActiveApiController;
use v1\components\file\FileSearchService;
use yii\base\Module;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * @OA\Tag(
 *     name="File",
 *     description="Everything about your File",
 * )
 *
 * @OA\Get(
 *     path="/file/{id}",
 *     summary="Get",
 *     description="Get File by particular id",
 *     operationId="getFile",
 *     tags={"File"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="File id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/File/properties/id")
 *     ),
 *     @OA\Parameter(
 *         name="fields",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/fields")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/File")
 *     )
 * )
 *
 * @version 1.0.0
 */
class FileController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\File';

    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param FileRepo $fileRepo
     * @param FileService $fileService
     * @param DatafeedService $datafeedService
     * @param ClientRepo $clientRepo
     * @param FileSearchService $fileSearchService
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private FileRepo $fileRepo, private FileService $fileService, private DatafeedService $datafeedService, private ClientRepo $clientRepo, private FileSearchService $fileSearchService, $config = [])
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

        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * Upload file.
     *
     * @param int $id
     *
     * @return array<int, mixed>
     */
    /**
     * @OA\Post(
     *    path="/file/upload/{id}",
     *    summary="Upload",
     *    description="Upload a file",
     *    operationId="uploadFile",
     *    tags={"File"},
     *    @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client id",
     *         required=true,
     *         @OA\Schema(ref="#/components/schemas/Client/properties/id")
     *    ),
     *    @OA\RequestBody(
     *        description="Upload a file",
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload"),
     *            )
     *       ),
     *       @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                 required={"url"},
     *                 @OA\Property(property="url", type="string", description="File to upload"),
     *            )
     *       ),
     *    ),
     *    @OA\Response(
     *       response=200,
     *       description="Successful operation",
     *       @OA\JsonContent(type="object", ref="#/components/schemas/File")
     *    )
     * )
     *
     * @return ActiveRecord
     */
    public function actionUpload(int $id): ActiveRecord
    {
        $uploadFile = UploadedFile::getInstanceByName('file');
        $params = $this->getRequestParams();

        try {
            if (isset($params['url'])) {
                $uploadFile = $this->fileService->loadFileToUploadedFile($params['url']);
            }
            $file = FileEntity::createByUploadedFile($uploadFile);

            $client = $this->clientRepo->findOne($id);
            $upload = $this->fileService->upload($file);
            $this->datafeedService->createFromFile($client, $upload['path']);

            return $upload;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/file/feed/{id}}",
     *     summary="Download",
     *     description="Download File by particular id",
     *     operationId="downloadFile",
     *     tags={"File"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File id",
     *         required=true,
     *         @OA\Schema(ref="#/components/schemas/File/properties/id")
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
     * @param int $id
     * @return Response
     */
    public function actionFeed(int $id): Response
    {
        try {
            $file = $this->fileRepo->findOne($id);

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
     *     path="/file/search",
     *     summary="Search",
     *     description="Search File by particular params",
     *     operationId="searchFile",
     *     tags={"File"},
     *     @OA\RequestBody(
     *         description="search File",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/FileSearch")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/File")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search DataVersion
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->fileSearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
