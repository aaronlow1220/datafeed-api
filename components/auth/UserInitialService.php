<?php

namespace app\components\auth;

use Throwable;
use app\components\user\UserRepo;
use yii\db\ActiveRecord;

/**
 * This service handles the user initial process, including: create user, business.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class UserInitialService
{
    /**
     * construct.
     *
     * @param UserRepo $userRepo
     */
    public function __construct(
        private UserRepo $userRepo
    ) {}

    /**
     * initialize an user.
     *
     * @param array<string, mixed> $data
     * @param string $ip
     * @return ActiveRecord
     */
    public function initialize(array $data, string $ip): ActiveRecord
    {
        $transaction = $this->userRepo->getDb()->beginTransaction();

        try {
            $values = array_merge($data, [
                'last_login_ip' => $ip,
                'last_login_at' => time(),
            ]);

            // create new user
            $user = $this->userRepo->create($values);

            // commit transaction
            $transaction->commit();

            return $user;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }
}
