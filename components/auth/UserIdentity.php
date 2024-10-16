<?php

namespace app\components\auth;

use Throwable;
use Yii;
use app\components\enum\UserStatusEnum;
use app\models\User;
use yii\base\Exception;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;

/**
 * This is an identity class for handling user related operations.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class UserIdentity extends User implements IdentityInterface
{
    /**
     * construct.
     *
     * @param array<string, mixed> $config
     * @param string $accessToken
     * @return void
     */
    public function __construct(array $config = [], private string $accessToken = '')
    {
        parent::__construct($config);
    }

    /**
     * find identity by id.
     *
     * @param int $id
     * @return null|self
     */
    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    /**
     * find by access token.
     *
     * note: this method must install `atellitech/auths-sdk-php` package and register cache component.
     *
     * @param string $token
     * @param string $type
     * @return null|self
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {
            $auth = Yii::$container->get('AtelliTech\Auths\Auth');
            $auth->verifyToken($token);
            $decoded = (array) $auth->decodeByToken($token);
            $sub = $decoded['sub'] ?? null;
            if (empty($sub)) {
                throw new Exception('Invalid Access Token');
            }
            $socialType = $decoded['social_type'] ?? null;
            if ('fb' == $socialType) {
                $socialType = 'meta';
            }

            $socialSub = $decoded['social_sub'] ?? null;

            // check cache exists
            $cache = Yii::$app->getCache();
            $userService = Yii::$container->get('app\components\user\UserService');
            $data = false; // $userService->getCache($cache, $sub);
            // if (empty($data)) {
            // cache not exists
            $user = self::findOne(['social_type' => $socialType, 'social_sub' => $socialSub]);
            if (empty($user)) {
                throw new Exception('User not found by sub');
            }

            $data = $user->getAttributes();
            // $userService->setCache($cache, $sub, $data);
            // }

            // check user enabled
            if (!UserStatusEnum::from($data['status'])->equals(UserStatusEnum::ACTIVE())) {
                throw new Exception('User is not active');
            }

            return new self($data, $token);
        } catch (Throwable $e) {
            throw new UnauthorizedHttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this['id'];
    }

    /**
     * get subject of Cyntelli Auth.
     *
     * @return string
     */
    public function getSub(): string
    {
        return $this['sub'];
    }

    /**
     * get given name.
     *
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this['first_name'];
    }

    /**
     * get family name.
     *
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this['last_name'];
    }

    /**
     * get fullname.
     *
     * @return string
     */
    public function getFullname(): string
    {
        $fullname = sprintf('%s %s', $this->getFirstName(), $this->getLastName());

        return trim($fullname);
    }

    /**
     * get social type.
     *
     * @return null|string
     */
    public function getSocialType(): ?string
    {
        return $this['social_type'];
    }

    /**
     * get social sub.
     *
     * @return null|string
     */
    public function getSocialSub(): ?string
    {
        return $this['social_sub'];
    }

    /**
     * get region name.
     *
     * @return null|string
     */

    /**
     * get auth key.
     *
     * @return string
     */
    public function getAuthKey()
    {
        return uniqid();
    }

    /**
     * validate auth key.
     *
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * get access token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
