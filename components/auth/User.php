<?php

namespace app\components\auth;

/**
 * This is an user class extended yii\web\User.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 *
 * @method string getAccessToken()
 */
class User extends \yii\web\User
{
    /**
     * get access token.
     *
     * @return null|string
     */
    public function getAccessToken(): ?string
    {
        $identity = $this->getIdentity();
        if (empty($identity)) {
            return null;
        }

        return $identity->getAccessToken(); // @phpstan-ignore-line
    }
}
