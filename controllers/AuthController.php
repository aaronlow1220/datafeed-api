<?php

namespace app\controllers;

use AtelliTech\Auths\Auth;
use Throwable;
use app\components\auth\UserInitialService;
use app\components\user\UserRepo;
use app\components\user\UserService;
use yii\base\Exception;
use yii\base\Security;
use yii\caching\Cache;
use yii\helpers\ArrayHelper as Arr;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

/**
 * This controller handles the authentication process.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class AuthController extends Controller
{
    /**
     * display login page.
     *
     * @param Auth $auth
     * @param Cache $cache
     * @param Security $security
     * @param string $socialType
     * @param string $redirectUrl
     * @param null|string $inviteCode
     * @return Response
     */
    public function actionLogin(Auth $auth, Cache $cache, Security $security, string $socialType, string $redirectUrl, ?string $inviteCode = null): Response
    {
        try {
            $client = $auth->getApplicationClient();
            $result = $client->getAuthProviders(['clientId' => $auth->getClientId()]);
            $providers = $result['providers'] ?? null;
            if (empty($providers) || !is_array($providers)) {
                throw new Exception('No providers');
            }

            $provider = null;
            foreach ($providers as $p) {
                if ($p['type_value'] === $socialType) {
                    $provider = $p;

                    break;
                }
            }

            if (empty($provider)) {
                throw new Exception('No provider found');
            }

            $sess = $security->generateRandomString(8);
            $data = [
                'socialType' => $socialType,
                'redirectUrl' => $redirectUrl,
                'inviteCode' => $inviteCode,
            ];
            $cache->set($sess, $data, 300);

            $redirectUri = Url::to(['auth/callback', 'sess' => $sess], true);
            $url = $provider['login_url'].'&redirectUri='.urlencode($redirectUri);

            return $this->redirect($url);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * handle callback from social login.
     *
     * @param string $sess
     * @param string $state
     * @param string $access_token
     * @param string $message
     * @param Auth $auth
     * @param UserInitialService $userInitialService
     * @param UserRepo $userRepo
     * @param UserService $userService
     * @param Cache $cache
     * @param Request $request
     * @return Response
     */
    public function actionCallback(string $sess, string $state, string $access_token, string $message, Auth $auth, UserInitialService $userInitialService, UserRepo $userRepo, UserService $userService, Cache $cache, Request $request): Response
    {
        try {
            $data = $cache->get($sess);
            if (empty($data)) {
                throw new Exception('Invalid session or session expired');
            }
            $redirectUrl = Arr::getValue($data, 'redirectUrl');

            // verify access token
            $auth->verifyToken($access_token);
            $decoded = (array) $auth->decodeByToken($access_token);
            $sub = $decoded['sub'] ?? null;
            $socialType = $decoded['social_type'] ?? null;
            $socialSub = $decoded['social_sub'] ?? null;
            if (empty($sub) || empty($socialType) || empty($socialSub)) {
                throw new Exception('Invalid Access Token');
            }

            // check user exists
            $socialType = 'fb' == $socialType ? 'meta' : $socialType;
            $decoded['social_type'] = $socialType;
            $user = $userRepo->findOneBySocialTypeAndSub($socialType, $socialSub);
            if (empty($user)) { // create new user record when user not exists
                $user = $userInitialService->initialize($decoded, $request->getUserIP());
            } else {
                // update user record
                $decoded['social_type'] = $socialType;
                $user = $userService->updateAuthInfo($user, $decoded);
                $userService->login($user, $request->getUserIP());
            }

            // clear user cache
            $userService->deleteCache($cache, $sub);

            $query = http_build_query([
                'access_token' => $access_token,
                'code' => 200,
                'message' => 'Ok',
            ]);

            if (false !== strpos($redirectUrl, '?')) {
                $redirectUrl .= '&'.$query;
            } else {
                $redirectUrl .= '?'.$query;
            }

            // redirect to original url
            return $this->redirect($redirectUrl);
        } catch (Throwable $e) {
            throw $e;
        } finally {
            $cache->delete($sess);
        }
    }
}
