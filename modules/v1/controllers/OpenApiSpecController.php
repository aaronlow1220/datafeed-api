<?php

namespace v1\controllers;

use OpenApi\Annotations as OA;
use Yii;
use yii\web\Response;

/**
 * @OA\OpenApi(
 *     security={{"BearerAuth": {}}},
 *     @OA\Server(
 *         url="http://localhost:8080",
 *         description="[Dev] Datafeed APIs server"
 *     ),
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Datafeed API v1",
 *         description="APIs document of v1 that based on Swagger OpenAPI",
 *         termsOfService="http://swagger.io/terms/",
 *         @OA\Contact(name="xxxx"),
 *         @OA\License(name="MIT", identifier="MIT")
 *     ),
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     in="header",
 *     description="Authentication(Based on User Token): Bearer {Access Token}",
 *     securityScheme="BearerAuth"
 * )
 */
class OpenApiSpecController extends \yii\web\Controller
{
    /**
     * display swagger yaml.
     *
     * @param string $format default: yaml
     * @return Response|string
     */
    public function actionIndex(?string $format = null): Response|string
    {
        $modulePath = Yii::getAlias('@v1');
        $modelPath = Yii::getAlias('@app/models');
        $openapi = \OpenApi\Generator::scan([$modulePath, $modelPath]);
        if ('json' == $format) {
            $contents = $openapi->toJson();
            $this->response->format = Response::FORMAT_JSON;
        } elseif ('yaml' == $format) {
            $contents = $openapi->toYaml();
            $this->response->headers->set('Content-Type', 'application/x-yaml');
        } else {
            $viewFile = Yii::getAlias('@v1/views/view_apidoc.php');
            $yamlUri = strstr(\yii\helpers\Url::to(['/apidoc', 'format' => 'yaml'], true), '//');
            $contents = $this->view->renderFile($viewFile, ['yamlUri' => $yamlUri]);
            $this->response->format = Response::FORMAT_HTML;
        }

        $this->response->content = $contents;

        return $this->response;
    }
}
