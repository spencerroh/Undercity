<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-27
 * Time: 오후 9:43
 */

$app->group('/user', function () use ($app) {
    $app->post('/login', function () use ($app) {
        $request = $app->request()->post();

        if (array_key_exists('UserInfo', $request)) {
            $privateKey = openssl_pkey_get_private(file_get_contents('keys/private.pem'));

            $status = openssl_private_decrypt(base64_decode($request['UserInfo']),
                $plainText,
                $privateKey,
                OPENSSL_PKCS1_OAEP_PADDING);
            if ($status === false) {
                $app->response->setStatus(500);
            } else {
                $userInfo = json_decode($plainText);
                if (array_key_exists('DeviceToken', $userInfo) &&
                    array_key_exists('DeviceOS', $userInfo)) {

                    $user = \Undercity\UserQuery::create()->findOneByDeviceToken($userInfo->DeviceToken);

                    if ($user == null) {
                        $user = new \Undercity\User();
                        $user->setDeviceToken($userInfo->DeviceToken);
                        $user->setDeviceOs($userInfo->DeviceOS);
                        $user->setCreateDate(new DateTime('now'), new DateTimeZone('Asia/Seoul'));
                        $user->save();
                    }

                    $_SESSION = array();
                    $_SESSION['USER_ID'] = $user->getId();

                    $response = array(
                        'status' => 'success'
                    );
                    echo json_encode($response);
                } else {
                    $app->response->setStatus(400);
                }
            }
        }
        else {
            $app->response->setStatus(400);
        }
    });

    $app->map('/is_login', function () use ($app) {
        var_dump($_SESSION);

        if (isset($_SESSION['USER_ID'])) {
            $app->response->setStatus(200);
        } else {
            $app->response->setStatus(401);
        }
    })->via('GET');

    $app->map('/logout', function () use ($app) {
        session_destroy();
    })->via('GET', 'POST');
});

?>