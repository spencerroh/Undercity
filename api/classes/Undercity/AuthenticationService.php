<?php

namespace Undercity;

use \DateTime;
use \Undercity\UserQuery as UserQuery;
use \Undercity\User as User;

class AuthenticationService
{
    protected $user = null;

    public function logIn($deviceUUID, $deviceToken, $deviceOS)
    {
        $user = \Undercity\UserQuery::create()->findOneBydeviceUUID($deviceUUID);

        if ($user == null) {
            $user = new User();
            $user->setDeviceUUID($deviceUUID);
            $user->setDeviceToken($deviceToken);
            $user->setdeviceOS($deviceOS);
            $user->setCreateDate(new DateTime('now'));
        }

        if ($deviceToken !== $user->getDeviceToken() ||
            $deviceOS !== $user->getdeviceOS()) {
            $user->setDeviceToken($deviceToken);
            $user->setdeviceOS($deviceOS);
        }

        $user->setLastLoginDate(new DateTime('now'));
        $user->save();

        $this->user = $user;

        $response = array(
            'status' => 'success'
        );

        echo json_encode($response);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function isLoggedIn()
    {
        return true;
    }

    public function logOut()
    {
        $this->user = null;
    }
}