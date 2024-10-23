<?php

namespace v1\controllers;

use InvalidArgumentException;
use Throwable;
use app\modules\v1\Module;
use v1\components\ActiveApiController;
use v1\components\client\ClientSearchService;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="Client",
 *     description="Everything about your Client",
 * )
 *
 * @OA\Get(
 *     path="/client",
 *     summary="List",
 *     description="List all Client",
 *     operationId="listClient",
 *     tags={"Client"},
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
 *         @OA\Schema(type="string", enum={"xxxx"}, description="Query related models, using comma(,) be seperator")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/Client")),
 *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
 *             )
 *         )
 *     )
 * )
 *
 * @OA\Get(
 *     path="/client/{id}",
 *     summary="Get",
 *     description="Get Client by particular id",
 *     operationId="getClient",
 *     tags={"Client"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Client id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Client/properties/id")
 *     ),
 *     @OA\Parameter(
 *         name="fields",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/fields")
 *     ),
 *     @OA\Parameter(
 *         name="expand",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/ClientSearch/properties/expand")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Client")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/client",
 *     summary="Create",
 *     description="Create a record of Client",
 *     operationId="createClient",
 *     tags={"Client"},
 *     @OA\RequestBody(
 *         description="Client object that needs to be added",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="name", ref="#/components/schemas/Client/properties/name"),
 *                  @OA\Property(property="label", ref="#/components/schemas/Client/properties/label"),
 *                  @OA\Property(property="data", ref="#/components/schemas/Client/properties/data")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Client")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/client/{id}",
 *     summary="Update",
 *     description="Update a record of Client",
 *     operationId="updateClient",
 *     tags={"Client"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Client id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Client/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="Client object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="name", ref="#/components/schemas/Client/properties/name"),
 *                  @OA\Property(property="label", ref="#/components/schemas/Client/properties/label"),
 *                  @OA\Property(property="data", ref="#/components/schemas/Client/properties/data")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Client")
 *     )
 * )
 *
 * @version 1.0.0
 */
class ClientController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Client';

    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param ClientSearchService $clientSearchService
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function __construct(string $id, Module $module, private ClientSearchService $clientSearchService, array $config = [])
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
        return parent::actions();
    }

    /**
     * @OA\Post(
     *     path="/client/search",
     *     summary="Search",
     *     description="Search Client by particular params",
     *     operationId="searchClient",
     *     tags={"Client"},
     *     @OA\RequestBody(
     *         description="search Client",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/ClientSearch")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/Client")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search Client
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->clientSearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
