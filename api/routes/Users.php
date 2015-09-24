<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-27
 * Time: 오후 9:43
 */
use \Firebase\JWT\JWT;

$app->group('/user', function () use ($app) {
    $app->options('/', function () use ($app) {
        $app->response->header('Allow', 'GET, POST, DELETE');
    });

    $app->map('/', function () use ($app) {
        $request = json_decode($app->request->getBody(), true);
        if ($app->auth->loginFromEncryptedData($request, $loginData) ||
            $app->auth->loginFromEncryptedDataV1($request, $loginData)) {
            $loginData = json_decode($loginData, true);
            
            if (array_key_exists('DeviceUUID', $loginData) &&
                array_key_exists('DeviceToken', $loginData) &&
                array_key_exists('DeviceOS', $loginData) &&
                array_key_exists('Now', $loginData)) {
                $app->user = $app->auth->logIn($loginData['DeviceUUID'],
                                               $loginData['DeviceToken'],
                                               $loginData['DeviceOS']);
                
                if ($app->auth->createToken($loginData, $app->user->getId(), $token)) {
                    echo json_encode(array(
                        'token' => $token
                    ));
                } else {
                    // Login Data에 적힌 시간이 일정시간보다 초과함.
                    echo 'timeout';
                    $app->response->setStatus(400);
                }
            } else {
                // 입력된 데이터가 올바르지 않음.
                echo 'not enough data';
                $app->response->setStatus(400);
            }    
        } else {
            // 로그인 데이터의 암호화가 잘못됨.
            echo 'encryption failed';
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