<?php

namespace v1\controllers;

use app\components\client\ClientRepo;
use app\components\datafeed\DatafeedService;
use Throwable;
use v1\components\ActiveApiController;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;
use yii\base\Module;
use SimpleXMLElement;

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
 *                  @OA\Property(property="id", ref="#/components/schemas/Datafeed/properties/id"),
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
 *                  @OA\Property(property="created_by", ref="#/components/schemas/Datafeed/properties/created_by"),
 *                  @OA\Property(property="created_at", ref="#/components/schemas/Datafeed/properties/created_at"),
 *                  @OA\Property(property="updated_by", ref="#/components/schemas/Datafeed/properties/updated_by"),
 *                  @OA\Property(property="updated_at", ref="#/components/schemas/Datafeed/properties/updated_at")
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
     * @var string $modelClass
     */
    public $modelClass = 'app\models\Datafeed';

    /**
     * construct.
     *
     * @param string $id
     * @param Module $module
     * @param DatafeedService $datafeedService
     * @param ClientRepo $clientRepo
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private DatafeedService $datafeedService, private ClientRepo $clientRepo, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inherit}
     *
     * @return array<string, mixed>
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index'], $actions['create'], $actions['delete']);

        return $actions;
    }

    public function actionCreate(int $id)
    {

        // try {
            $client = $this->clientRepo->findOne($id);

            if (!$client) {
                throw new HttpException(404, 'Client not found');
            }

            $filePath = __DIR__ . '/../files/original/' . $client["name"] . '_feed.xml';

            // var_dump($filePath);
            // exit;

            $data = [];
            $processedData = [];

            $xml = new SimpleXMLElement($filePath, 0, true);

            foreach ($xml->channel->item as $item) {
                $itemData = [];
                foreach ($item->children('g', true) as $key => $value) {
                    $itemData[$key] = trim((string) $value);
                }
                $data[] = $itemData;
            }

            $processedData = $this->datafeedService->transform($data, $client);

            return $this->datafeedService->create($client, $processedData);
        // } catch (Throwable $e) {
        //     throw new HttpException(500, 'Internal server error');
        // }
    }
}
