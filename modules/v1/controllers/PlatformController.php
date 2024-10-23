<?php

namespace v1\controllers;

use InvalidArgumentException;
use Throwable;
use app\modules\v1\Module;
use v1\components\ActiveApiController;
use v1\components\platform\PlatformSearchService;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="Platform",
 *     description="Everything about your Platform",
 * )
 *
 * @OA\Get(
 *     path="/platform",
 *     summary="List",
 *     description="List all Platform",
 *     operationId="listPlatform",
 *     tags={"Platform"},
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
 *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/Platform")),
 *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
 *             )
 *         )
 *     )
 * )
 *
 * @OA\Get(
 *     path="/platform/{id}",
 *     summary="Get",
 *     description="Get Platform by particular id",
 *     operationId="getPlatform",
 *     tags={"Platform"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Platform id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Platform/properties/id")
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
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Platform")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/platform",
 *     summary="Create",
 *     description="Create a record of Platform",
 *     operationId="createPlatform",
 *     tags={"Platform"},
 *     @OA\RequestBody(
 *         description="Platform object that needs to be added",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="name", ref="#/components/schemas/Platform/properties/name"),
 *                  @OA\Property(property="label", ref="#/components/schemas/Platform/properties/label"),
 *                  @OA\Property(property="data", ref="#/components/schemas/Platform/properties/data")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Platform")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/platform/{id}",
 *     summary="Update",
 *     description="Update a record of Platform",
 *     operationId="updatePlatform",
 *     tags={"Platform"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Platform id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/Platform/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="Platform object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="name", ref="#/components/schemas/Platform/properties/name"),
 *                  @OA\Property(property="label", ref="#/components/schemas/Platform/properties/label"),
 *                  @OA\Property(property="data", ref="#/components/schemas/Platform/properties/data")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/Platform")
 *     )
 * )
 *
 * @version 1.0.0
 */
class PlatformController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\Platform';

    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param PlatformSearchService $platformSearchService
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function __construct(string $id, Module $module, private PlatformSearchService $platformSearchService, array $config = [])
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
     *     path="/platform/search",
     *     summary="Search",
     *     description="Search Platform by particular params",
     *     operationId="searchPlatform",
     *     tags={"Platform"},
     *     @OA\RequestBody(
     *         description="search Platform",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/PlatformSearch")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/Platform")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search Platform
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->platformSearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
