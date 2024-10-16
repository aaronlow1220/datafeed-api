<?php

namespace app\components\user;

use yii\caching\Cache;
use yii\db\ActiveRecord;

/**
 * This service class is used to handle user related operations.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class UserService
{
    /**
     * construct.
     *
     * @param UserRepo $userRepo
     * @return void
     */
    public function __construct(
        private UserRepo $userRepo,
    ) {}

    /**
     * login user.
     *
     * @param ActiveRecord $user
     * @param string $ip
     * @param int $timestamp default time()
     * @return ActiveRecord
     */
    public function login(ActiveRecord $user, string $ip, ?int $timestamp = null): ActiveRecord
    {
        $timestamp = $timestamp ?? time();

        return $this->userRepo->update($user, ['last_login_ip' => $ip, 'last_login_at' => $timestamp], 'login');
    }

    /**
     * update auth information.
     *
     * @param ActiveRecord $user
     * @param array<string, mixed> $data
     * @return ActiveRecord
     */
    public function updateAuthInfo(ActiveRecord $user, array $data): ActiveRecord
    {
        return $this->userRepo->update($user, $data);
    }

    /**
     * set cache.
     *
     * @param Cache $cache
     * @param string $sub
     * @param array<string, mixed> $data
     * @param int $duration default 3600
     * @return void
     */
    public function setCache(Cache $cache, string $sub, array $data, int $duration = 3600): void
    {
        $cacheKey = $this->getIdentityCacheKey($sub);
        $cache->set($cacheKey, $data, $duration);
    }

    /**
     * get cache.
     *
     * @param Cache $cache
     * @param string $sub
     * @return array<string, mixed>|bool
     */
    public function getCache(Cache $cache, string $sub): array|bool
    {
        $cacheKey = $this->getIdentityCacheKey($sub);

        return $cache->get($cacheKey);
    }

    /**
     * delete cache.
     *
     * @param Cache $cache
     * @param string $sub
     * @return void
     */
    public function deleteCache(Cache $cache, string $sub): void
    {
        $cacheKey = $this->getIdentityCacheKey($sub);
        $cache->delete($cacheKey);
    }

    /**
     * get identity cache key by sub.
     *
     * @param string $sub
     * @return string
     */
    public function getIdentityCacheKey(string $sub): string
    {
        return sprintf('token.%s', hash('sha256', $sub));
    }
}
