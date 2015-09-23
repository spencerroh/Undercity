<?php

namespace Undercity;

use \DateTime;
use \Undercity\UserQuery as UserQuery;
use \Undercity\User as User;

class AuthenticationService
{
    protected $user = null;
    
    protected function cryptoJsAesDecrypt($passphrase, $jsonString){
        $jsondata = json_decode($jsonString, true);
        $salt = hex2bin($jsondata["s"]);
        $ct = base64_decode($jsondata["ct"]);
        $iv  = hex2bin($jsondata["iv"]);
        $concatedPassphrase = $passphrase.$salt;
        $md5 = array();
        $md5[0] = md5($concatedPassphrase, true);
        $result = $md5[0];
        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
            $result .= $md5[$i];
        }
        $key = substr($result, 0, 32);
        $data = openssl_decrypt($ct, SYM_CRYPTO_ALGORITHM, $key, true, $iv);
        return $data;
    }

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

        return $user;
    }
    
    public function loginFromEncryptedData($encryptedLoginData, &$loginData)
    {
        $data = json_decode($encryptedLoginData, true);
        if (array_key_exists('key', $data) &&
            array_key_exists('data', $data)) {
            $privateKey = openssl_get_privatekey(file_get_contents(RSA_SECRET_KEY));
            $encryptedKey = base64_decode($data['key']);
            $encryptedData = $data['data'];
                    
            if (openssl_private_decrypt($encryptedKey, 
                                        $decryptedKey, 
                                        $privateKey, 
                                        OPENSSL_PKCS1_PADDING)) {
                $loginData = $this->cryptoJsAesDecrypt($decryptedKey, $encryptedData);
            }
        }
        return false;
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