<?php

namespace app\components\user;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\User;
use yii\db\ActiveRecord;

/**
 * This is a repository class for accessing user.
 */
class UserRepo extends AbstractRepository
{
    /**
     * @var string Model class. Required.
     */
    protected string $modelClass = User::class;

    /**
     * Find one by subject of Cyntelli auth.
     *
     * @param string $sub
     * @return null|ActiveRecord
     */
    public function findOneBySub(string $sub): ?ActiveRecord
    {
        return $this->findOne(['sub' => $sub]);
    }

    /**
     * find one by social type and social sub.
     *
     * @param string $socialType
     * @param string $socialSub
     * @return null|ActiveRecord
     */
    public function findOneBySocialTypeAndSub(string $socialType, string $socialSub): ?ActiveRecord
    {
        return $this->findOne(['social_type' => $socialType, 'social_sub' => $socialSub]);
    }
}
