<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-27
 * Time: 오후 9:43
 */

$app->group('/user', function () use ($app) {
    $app->options('/', function () use ($app) {
        $app->response->header('Allow', 'GET, POST, DELETE');
    });

    $app->map('/', function () use ($app) {
        $request = json_decode($app->request->getBody(), true);

        if (array_key_exists('DeviceUUID', $request) &&
            array_key_exists('DeviceToken', $request) &&
            array_key_exists('DeviceOS', $request)) {
            $app->auth->logIn($request['DeviceUUID'],
                              $request['DeviceToken'],
                              $request['DeviceOS']);
        } else {
            $app->response->setStatus(400);
        }
    })->via('POST');

    $app->map('/', function () use ($app) {
        if ($app->auth->isLoggedIn()) {
            $app->response->setStatus(200);
        } else {
            $app->response->setStatus(401);
        }
    })->via('GET');

    $app->map('/', function () use ($app) {
        $app->auth->logOut();
    })->via('DELETE');
});

?>