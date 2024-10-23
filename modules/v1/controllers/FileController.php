<?php

namespace v1\controllers;

use Throwable;
use Yii;
use app\components\FileEntity;
use app\components\client\ClientRepo;
use app\components\core\FileRepo;
use app\components\core\FileService;
use app\components\datafeed\DatafeedService;
use v1\components\ActiveApiController;
use yii\base\Module;
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
 *     @OA\Parameter(
 *         name="expand",
 *         in="query",
 *         @OA\Schema(type="string", enum={"xxxx"}, description="Query related models, using comma(,) be seperator")
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
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private FileRepo $fileRepo, private FileService $fileService, private DatafeedService $datafeedService, private ClientRepo $clientRepo, $config = [])
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
    public function actionUpload(int $id): array
    {
        $uploadFile = UploadedFile::getInstanceByName('file');
        $file = FileEntity::createByUploadedFile($uploadFile);
        $client = $this->clientRepo->findOne($id);

        if (!$client) {
            throw new HttpException(404, 'Client not found');
        }

        try {
            $upload = $this->fileService->upload($file);

            return $this->datafeedService->createFromFile($client, $upload['path']);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Feed a file.
     *
     * @param string $filename
     *
     * @return Response
     */
    public function actionFeed(string $filename): Response
    {
        $file = $this->fileRepo->findOne(['filename' => $filename.'.csv']);

        if (file_exists($file['path'])) {
            return Yii::$app->response->sendFile($file['path']);
        }

        throw new HttpException(404, 'File not found');
    }
}
