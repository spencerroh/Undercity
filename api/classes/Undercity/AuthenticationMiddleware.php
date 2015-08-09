<?php

namespace Undercity;

use \Slim\Middleware as Middleware;

class AuthenticationMiddleware extends Middleware
{
    public function call()
    {
        $app = $this->app;
        $auth = $app->auth;

        $path = $app->request->getPath();
        $isLoginRequest = preg_match("/user/i", $path);
        $isImageRequest = preg_match("/images/i", $path) && $app->request->isGet();

        if ($auth->isLoggedIn() || $isLoginRequest || $isImageRequest) {
            $this->next->call();
        } else {
            $app->response->setStatus(401);
        }
    }
}