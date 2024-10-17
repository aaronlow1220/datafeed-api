<?php

namespace v1\controllers;

use Throwable;
use app\components\client\ClientRepo;
use app\components\datafeed\DatafeedRepo;
use app\components\datafeed\DatafeedService;
use app\components\version\DataVersionRepo;
use app\components\platform\PlatformRepo;
use v1\components\ActiveApiController;
use yii\base\Module;
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
     * @param DatafeedRepo $datafeedRepo
     * @param ClientRepo $clientRepo
     * @param PlatformRepo $platformRepo
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private DatafeedService $datafeedService, private DatafeedRepo $datafeedRepo, private ClientRepo $clientRepo, private PlatformRepo $platformRepo, private DataVersionRepo $dataVersionRepo, $config = [])
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
     * Create datafeed.
     *
     * @param int $id
     * @return array<int, mixed>
     */
    public function actionCreate(int $id): array
    {
        try {
            $client = $this->clientRepo->findOne($id);
            $initialDataVersion = $this->dataVersionRepo->findOne(['client_id' => $id]);

            if (!$client) {
                throw new HttpException(404, 'Client not found');
            }

            if (!$initialDataVersion) {
                $dataVersion = [
                    'client_id' => $client['id']
                ];
                $initialDataVersion = $this->dataVersionRepo->create($dataVersion);
            }

            $data = [];

            $filePath = __DIR__.'/../files/original/';

            $filePath = $this->datafeedService->readFeedFile($filePath);

            $processedData = $this->datafeedService->transformDataFromFile($filePath, $client);

            $finalDataVersion = $this->dataVersionRepo->findOne(['client_id' => $id]);

            if ($initialDataVersion["hash"] !== $finalDataVersion["hash"]) {
                throw new HttpException(400, 'Data version not match');
            }

            $this->datafeedService->create($client, $processedData);

            $dataVersion = [
                'hash' => hash_file('md5', $filePath),
            ];

            $this->dataVersionRepo->update($finalDataVersion, $dataVersion);
            return $processedData;
        } catch (Throwable $e) {
            throw new HttpException(400, 'Create datafeed failed, '.$e->getMessage());
        }
    }

    /**
     * Export datafeed.
     *
     * @param int $id
     * @param string $platformid
     * @return void
     */
    public function actionExport(int $id, string $platformid): void
    {
        try {
            $client = $this->clientRepo->findOne(['id' => $id]);
            $platform = $this->platformRepo->findOne(['id' => $platformid]);

            if (!$client) {
                throw new HttpException(400, 'Client not found');
            }

            if (!$platform) {
                throw new HttpException(400, 'Platform not found');
            }

            $resultPath = __DIR__.'/../files/result/'.$client['name'].'_'.$platform['name'].'_feed.csv';
            $data = [];
            $datafeeds = $this->datafeedRepo->find()->where(['client_id' => $id])->all();

            foreach ($datafeeds as $datafeed) {
                $data[] = $datafeed->attributes;
            }

            if (!$data) {
                throw new HttpException(400, 'Datafeed not found');
            }

            $this->datafeedService->export($data, $platform, $client, $resultPath);

            return;
        } catch (Throwable $e) {
            throw new HttpException(400, 'Export datafeed failed');
        }
    }
}
