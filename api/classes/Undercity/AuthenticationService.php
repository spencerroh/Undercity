<?php

namespace Undercity;

use \Undercity\UserQuery as UserQuery;
use \Undercity\User as User;

class AuthenticationService
{
    public function logIn($deviceUUID, $deviceToken, $deviceOS)
    {
        $user = \Undercity\UserQuery::create()->findOneBydeviceUUID($deviceUUID);
        if ($user == null) {
            $user = new User();
            $user->setDeviceUUID($deviceUUID);
            $user->setDeviceToken($deviceToken);
            $user->setdeviceOS($deviceOS);
            $user->save();
        }

        $_SESSION = array();
        $_SESSION['USER_ID'] = $user->getId();

        $response = array(
            'status' => 'success'
        );

        echo json_encode($response);
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['USER_ID']);
    }

    public function logOut()
    {
        $_SESSION = array();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }

        session_destroy();
    }
}