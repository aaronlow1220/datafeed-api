<?php

namespace v1\controllers;

use Throwable;
use Yii;
use app\components\FileEntity;
use app\components\core\FileRepo;
use app\components\core\FileService;
use v1\components\ActiveApiController;
use yii\base\Module;
use yii\db\ActiveRecord;
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
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private FileRepo $fileRepo, private FileService $fileService, $config = [])
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
     * @OA\Post(
     *    path="/file/upload",
     *    summary="Upload",
     *    description="Upload a file",
     *    operationId="uploadFile",
     *    tags={"File"},
     *    @OA\RequestBody(
     *        description="Upload a file",
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload")
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
    public function upload(): ActiveRecord
    {
        $uploadFile = UploadedFile::getInstanceByName('file');
        $file = FileEntity::createByUploadedFile($uploadFile);

        try {
            return $this->fileService->upload($file);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/file/{id}/download",
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
    public function actionDownload($id)
    {
        try {
            $file = $this->fileRepo->findOne($id);

            return Yii::$app->response->sendFile($file['path'], $file['filename']);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
