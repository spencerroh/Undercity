<?php

namespace Undercity;

use \Slim\Middleware as Middleware;
use \Undercity\UserQuery as UserQuery;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;

class AuthenticationMiddleware extends Middleware
{
    public function call()
    {
        $app = $this->app;

        $path = $app->request->getPath();
        $isLoginRequest = preg_match("/user/i", $path);
        $isImageRequest = preg_match("/images/i", $path) && $app->request->isGet();
        $isCertRequest = preg_match("/cert/i", $path);
        $isTestRequest = preg_match("/test/i", $path);
        $isOptionsRequest = $app->request->isOptions();

        $hasToken = $app->request->headers->has('X-Token');

        if ($isOptionsRequest) {
            $app->response->header('Allow', 'OPTIONS, GET, POST, DELETE');
            $app->response->setStatus(200);
            return;
        }

        if ($hasToken) {
            try {
                $decoded = JWT::decode($app->request->headers->get('X-Token'), JWT_TOKEN_SECRET_KEY, array('HS256'));

                $userId = $decoded->context->user;
                $app->user = \Undercity\UserQuery::create()->findPk($userId);
                $this->next->call();
            } catch(ExpiredException $e) {
                echo $e->getMessage();
                $app->response->setStatus(401);
            }
        } else {
            if ($isLoginRequest || $isImageRequest || $isCertRequest || $isTestRequest) {
                $this->next->call();
            } else {
                $app->response->setStatus(401);
            }
        }
    }
}