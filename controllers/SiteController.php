<?php

namespace app\controllers;

use AtelliTech\Auths\Auth;
use Throwable;
use app\components\user\UserRepo;
use yii\base\Exception;
use yii\helpers\ArrayHelper as Arr;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

/**
 * It's a default controller.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * display index.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = false;

        return 'Ok';
    }

    /**
     * display login page.
     *
     * @param null|string $inviteCode
     * @return string
     */
    public function actionLogin(?string $inviteCode = null)
    {
        try {
            $this->layout = false;

            if ($this->response instanceof Response) {
                $this->response->format = Response::FORMAT_HTML;
            }

            return $this->render('view_login', [
                'googleLoginUrl' => Url::to(['auth/login', 'socialType' => 'google', 'redirectUrl' => Url::to(['site/profile'], true), 'inviteCode' => $inviteCode], true),
                'fbLoginUrl' => Url::to(['auth/login', 'socialType' => 'fb', 'redirectUrl' => Url::to(['site/profile'], true), 'inviteCode' => $inviteCode], true),
            ]);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * display profile page.
     *
     * @param string $access_token
     * @param Auth $auth
     * @param UserRepo $userRepo
     * @return string
     */
    public function actionProfile(string $access_token, Auth $auth, UserRepo $userRepo)
    {
        try {
            // verify access token
            $auth->verifyToken($access_token);
            $decoded = (array) $auth->decodeByToken($access_token);
            $sub = Arr::getValue($decoded, 'sub');
            if (empty($sub)) {
                throw new Exception('Invalid access token');
            }

            $user = $userRepo->findOneBySub($sub);
            if (empty($user)) {
                throw new Exception('User not found');
            }

            $this->layout = false;
            if ($this->response instanceof Response) {
                $this->response->format = Response::FORMAT_HTML;
            }

            return $this->render('view_profile', [
                'accessToken' => $access_token,
                'user' => $user->toArray([], ['region', 'language']),
            ]);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
