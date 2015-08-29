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

        $privateKey = file_get_contents('certs/private.pem');
        $res = openssl_get_privatekey($privateKey);
        $encrypted = $request['Login'];
        $decrypted = '';

        if (openssl_private_decrypt(base64_decode($encrypted), $decrypted, $res, OPENSSL_PKCS1_PADDING)) {
            $loginData = json_decode($decrypted, true);

            if (array_key_exists('DeviceUUID', $loginData) &&
                array_key_exists('DeviceToken', $loginData) &&
                array_key_exists('DeviceOS', $loginData) &&
                array_key_exists('Now', $loginData)) {
                $timeElapsed = (new DateTime('now'))->getTimestamp() - $loginData['Now'];
                if ($timeElapsed < LOGIN_DATA_EXPIRE_SECONDS) {
                    $app->user = $app->auth->logIn($loginData['DeviceUUID'],
                                                   $loginData['DeviceToken'],
                                                   $loginData['DeviceOS']);

                    $iat = (new DateTime('now'))->getTimestamp();
                    $exp = $iat + 60*60;
                    $token = array (
                        'usr'=> $app->user->getId(),
                        'iat'=> $iat,
                        'exp'=> $exp,
                        'context' => array(
                            'user' => $app->user->getId()
                        )
                    );
                    $jwt = JWT::encode($token, JWT_TOKEN_SECRET_KEY);

                    $response = array(
                        'token'=> $jwt
                    );

                    echo json_encode($response);

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