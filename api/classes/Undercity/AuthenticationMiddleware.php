<?php

namespace Undercity;

use \Slim\Middleware as Middleware;
use \Undercity\UserQuery as UserQuery;

class AuthenticationMiddleware extends Middleware
{
    public function call()
    {
        $app = $this->app;

        $path = $app->request->getPath();
        $isLoginRequest = preg_match("/user/i", $path);
        $isImageRequest = preg_match("/images/i", $path) && $app->request->isGet();
        $hasDeviceID = $app->request->headers->has('X-Device-Id');
        $deviceID = $app->request->headers->get('X-Device-Id');
        $isOptionsRequest = $app->request->isOptions();

        if ($isOptionsRequest) {
            $app->response->header('Allow', 'OPTIONS, GET, POST, DELETE');
            $app->response->setStatus(200);
            return;
        }
        if (!$isLoginRequest && $hasDeviceID) {
            $user = \Undercity\UserQuery::create()->findOneBydeviceUUID($deviceID);

            if ($user == null) {
                $app->response->setStatus(400);
                return;
            }

            $app->user = $user;
        }

        if ($hasDeviceID || $isOptionsRequest || $isImageRequest || $isLoginRequest) {
            $this->next->call();
        } else {
            $app->response->setStatus(401);
        }


    }
}