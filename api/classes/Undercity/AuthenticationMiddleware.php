<?php

namespace Undercity;

use \Slim\Middleware as Middleware;
use \Undercity\UserQuery as UserQuery;
use \Firebase\JWT\JWT;

class AuthenticationMiddleware extends Middleware
{
    public function call()
    {
        $app = $this->app;

        $path = $app->request->getPath();
        $isLoginRequest = preg_match("/user/i", $path);
        $isImageRequest = preg_match("/images/i", $path) && $app->request->isGet();
        $isCertRequest = preg_match("/cert/i", $path);
        $isOptionsRequest = $app->request->isOptions();

        /*
        if ($app->request->getIp() == "127.0.0.1") {
            $isLoginRequest = false;
        }
        */

        $hasToken = $app->request->headers->has('X-Token');
        if (!$hasToken) {
            if ($isOptionsRequest) {
                $app->response->header('Allow', 'OPTIONS, GET, POST, DELETE');
                $app->response->setStatus(200);
            } else if ($isLoginRequest || $isImageRequest || $isCertRequest) {
                $this->next->call();
            } else {
                $app->response->setStatus(401);
            }
        } else {
            $token = $app->request->headers->get('X-Token');

            try {
                $decoded = JWT::decode($token, JWT_TOKEN_SECRET_KEY, array('HS256'));

                $userId = $decoded->context->user;
                $app->user = \Undercity\UserQuery::create()->findPk($userId);

                $this->next->call();
            } catch(Exception $e) {
                $app->response->setStatus(401);
            }

        }
    }
}