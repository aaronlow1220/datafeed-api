<?php

namespace v1\controllers;

use InvalidArgumentException;
use Throwable;
use app\modules\v1\Module;
use v1\components\ActiveApiController;
use v1\components\user\UserSearchService;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

/**
 * @OA\Tag(
 *     name="User",
 *     description="Everything about your User",
 * )
 *
 * @OA\Get(
 *     path="/user/{id}",
 *     summary="Get",
 *     description="Get User by particular id",
 *     operationId="getUser",
 *     tags={"User"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="User id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/User/properties/id")
 *     ),
 *     @OA\Parameter(
 *         name="fields",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/StandardParams/properties/fields")
 *     ),
 *     @OA\Parameter(
 *         name="expand",
 *         in="query",
 *         @OA\Schema(ref="#/components/schemas/UserSearch/properties/expand")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/User")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/user",
 *     summary="Create",
 *     description="Create a record of User",
 *     operationId="createUser",
 *     tags={"User"},
 *     @OA\RequestBody(
 *         description="User object that needs to be added",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="id", ref="#/components/schemas/User/properties/id"),
 *                  @OA\Property(property="sub", ref="#/components/schemas/User/properties/sub"),
 *                  @OA\Property(property="social_sub", ref="#/components/schemas/User/properties/social_sub"),
 *                  @OA\Property(property="social_type", ref="#/components/schemas/User/properties/social_type"),
 *                  @OA\Property(property="username", ref="#/components/schemas/User/properties/username"),
 *                  @OA\Property(property="family_name", ref="#/components/schemas/User/properties/family_name"),
 *                  @OA\Property(property="given_name", ref="#/components/schemas/User/properties/given_name"),
 *                  @OA\Property(property="email", ref="#/components/schemas/User/properties/email"),
 *                  @OA\Property(property="avatar", ref="#/components/schemas/User/properties/avatar"),
 *                  @OA\Property(property="last_login_ip", ref="#/components/schemas/User/properties/last_login_ip"),
 *                  @OA\Property(property="last_login_at", ref="#/components/schemas/User/properties/last_login_at"),
 *                  @OA\Property(property="status", ref="#/components/schemas/User/properties/status"),
 *                  @OA\Property(property="created_by", ref="#/components/schemas/User/properties/created_by"),
 *                  @OA\Property(property="created_at", ref="#/components/schemas/User/properties/created_at"),
 *                  @OA\Property(property="updated_by", ref="#/components/schemas/User/properties/updated_by"),
 *                  @OA\Property(property="updated_at", ref="#/components/schemas/User/properties/updated_at")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/User")
 *     )
 * )
 *
 * @OA\Patch(
 *     path="/user/{id}",
 *     summary="Update",
 *     description="Update a record of User",
 *     operationId="updateUser",
 *     tags={"User"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="User id",
 *         required=true,
 *         @OA\Schema(ref="#/components/schemas/User/properties/id")
 *     ),
 *     @OA\RequestBody(
 *         description="User object that needs to be updated",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                  @OA\Property(property="id", ref="#/components/schemas/User/properties/id"),
 *                  @OA\Property(property="sub", ref="#/components/schemas/User/properties/sub"),
 *                  @OA\Property(property="social_sub", ref="#/components/schemas/User/properties/social_sub"),
 *                  @OA\Property(property="social_type", ref="#/components/schemas/User/properties/social_type"),
 *                  @OA\Property(property="username", ref="#/components/schemas/User/properties/username"),
 *                  @OA\Property(property="family_name", ref="#/components/schemas/User/properties/family_name"),
 *                  @OA\Property(property="given_name", ref="#/components/schemas/User/properties/given_name"),
 *                  @OA\Property(property="email", ref="#/components/schemas/User/properties/email"),
 *                  @OA\Property(property="avatar", ref="#/components/schemas/User/properties/avatar"),
 *                  @OA\Property(property="last_login_ip", ref="#/components/schemas/User/properties/last_login_ip"),
 *                  @OA\Property(property="last_login_at", ref="#/components/schemas/User/properties/last_login_at"),
 *                  @OA\Property(property="status", ref="#/components/schemas/User/properties/status"),
 *                  @OA\Property(property="created_by", ref="#/components/schemas/User/properties/created_by"),
 *                  @OA\Property(property="created_at", ref="#/components/schemas/User/properties/created_at"),
 *                  @OA\Property(property="updated_by", ref="#/components/schemas/User/properties/updated_by"),
 *                  @OA\Property(property="updated_at", ref="#/components/schemas/User/properties/updated_at")
 *             )
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="object", ref="#/components/schemas/User")
 *     )
 * )
 *
 * @version 1.0.0
 */
class UserController extends ActiveApiController
{
    /**
     * @var string
     */
    public $modelClass = 'app\models\User';

    /**
     * UserController constructor.
     *
     * @param string $id
     * @param Module $module
     * @param UserSearchService $userSearchService
     * @param array<string, mixed> $config
     */
    public function __construct($id, $module, private UserSearchService $userSearchService, $config = [])
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
     *     path="/user/search",
     *     summary="Search",
     *     description="Search User by particular params",
     *     operationId="searchUser",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         description="search User",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UserSearch")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="_data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *              @OA\Property(property="_meta", type="object", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     *
     * Search User
     *
     * @return ActiveDataProvider
     */
    public function actionSearch(): ActiveDataProvider
    {
        try {
            $params = $this->getRequestParams();

            return $this->userSearchService->createDataProvider($params);
        } catch (InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
