<?php

namespace v1\controllers;

use Throwable;
use Yii;
use app\components\client\ClientRepo;
use app\components\core\FileRepo;
use app\components\datafeed\DatafeedService;
use app\components\platform\PlatformRepo;
use v1\components\ActiveApiController;
use yii\base\Module;
use yii\db\ActiveRecord;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="Datafeed",
 *     description="Everything about your Datafeed",
 * )
 *
 * @OA\Get(
 *     path="/datafeed/{id}",
 *     summary="Get",
 *     description="Get Datafeed by particular id",
 *     operationId="getDatafeed",
 *     tags={"Datafeed"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Datafeed id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Datafeed/properties/id")
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
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Datafeed")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/datafeed/{id}",
 *     summary="Update",
 *     description="Update a record of Datafeed",
 *     operationId="updateDatafeed",
 *     tags={"Datafeed"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Datafeed id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Datafeed/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="Datafeed object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="datafeedid", ref="#/components/schemas/Datafeed/properties/datafeedid"),
 *                  @OA\Property(property="condition", ref="#/components/schemas/Datafeed/properties/condition"),
 *                  @OA\Property(property="availability", ref="#/components/schemas/Datafeed/properties/availability"),
 *                  @OA\Property(property="description", ref="#/components/schemas/Datafeed/properties/description"),
 *                  @OA\Property(property="image_link", ref="#/components/schemas/Datafeed/properties/image_link"),
 *                  @OA\Property(property="link", ref="#/components/schemas/Datafeed/properties/link"),
 *                  @OA\Property(property="title", ref="#/components/schemas/Datafeed/properties/title"),
 *                  @OA\Property(property="price", ref="#/components/schemas/Datafeed/properties/price"),
 *                  @OA\Property(property="sale_price", ref="#/components/schemas/Datafeed/properties/sale_price"),
 *                  @OA\Property(property="gtin", ref="#/components/schemas/Datafeed/properties/gtin"),
 *                  @OA\Property(property="mpn", ref="#/components/schemas/Datafeed/properties/mpn"),
 *                  @OA\Property(property="brand", ref="#/components/schemas/Datafeed/properties/brand"),
 *                  @OA\Property(property="google_product_category", ref="#/components/schemas/Datafeed/properties/google_product_category"),
 *                  @OA\Property(property="item_group_id", ref="#/components/schemas/Datafeed/properties/item_group_id"),
 *                  @OA\Property(property="custom_label_0", ref="#/components/schemas/Datafeed/properties/custom_label_0"),
 *                  @OA\Property(property="custom_label_1", ref="#/components/schemas/Datafeed/properties/custom_label_1"),
 *                  @OA\Property(property="custom_label_2", ref="#/components/schemas/Datafeed/properties/custom_label_2"),
 *                  @OA\Property(property="custom_label_3", ref="#/components/schemas/Datafeed/properties/custom_label_3"),
 *                  @OA\Property(property="custom_label_4", ref="#/components/schemas/Datafeed/properties/custom_label_4"),
 *                  @OA\Property(property="status", ref="#/components/schemas/Datafeed/properties/status"),
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Datafeed")
 *     )
 * )
 *
 * @version 1.0.0
 */
class DatafeedController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Datafeed';

    /**
     * construct.
     *
     * @param string $id
     * @param Module $module
     * @param DatafeedService $datafeedService
     * @param ClientRepo $clientRepo
     * @param PlatformRepo $platformRepo
     * @param FileRepo $fileRepo
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private DatafeedService $datafeedService, private ClientRepo $clientRepo, private PlatformRepo $platformRepo, private FileRepo $fileRepo, $config = [])
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

        unset($actions['index'], $actions['create'], $actions['delete']);

        return $actions;
    }

    /**
     * @OA\Post(
     *     path="/datafeed/export/{id}/{platformid}",
     *     summary="Export",
     *     description="Export a record of Datafeed",
     *     operationId="exportDatafeed",
     *     tags={"Datafeed"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client id",
     *         required=true,
     *         @OA\Schema(ref="#/components/schemas/Client/properties/id")
     *     ),
     *     @OA\Parameter(
     *         name="platformid",
     *         in="path",
     *         description="Platform id",
     *         required=true,
     *         @OA\Schema(ref="#/components/schemas/Platform/properties/id")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object", ref="#/components/schemas/Datafeed")
     *    )
     * )
     *
     * Export datafeed.
     *
     * @param int $id
     * @param string $platformid
     * @return ActiveRecord
     */
    public function actionExport(int $id, string $platformid): ActiveRecord
    {
        $params = Yii::$app->request->get();
        unset($params['id'], $params['platformid']);

        try {
            $client = $this->clientRepo->findOne(['id' => $id]);
            $platform = $this->platformRepo->findOne(['id' => $platformid]);

            if (!$client) {
                throw new HttpException(400, 'Client not found');
            }

            if (!$platform) {
                throw new HttpException(400, 'Platform not found');
            }

            $resultPath = sprintf('%s/%s_%s_%s_feed.csv', __DIR__.'/../../../runtime/files/result', uniqid(), $client['name'], $platform['name']);

            $this->datafeedService->export($platform, $client, $resultPath, $params);

            $data = [
                'mime' => 'text/csv',
                'extension' => 'csv',
                'filename' => basename($resultPath),
                'path' => $resultPath,
                'size' => filesize($resultPath),
            ];

            return $this->fileRepo->create($data);
        } catch (Throwable $e) {
            throw new HttpException(400, 'Export datafeed failed');
        }
    }
}
