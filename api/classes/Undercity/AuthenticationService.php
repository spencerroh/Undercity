<?php

namespace Undercity;

use \DateTime;
use \Firebase\JWT\JWT;

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

    public function logIn($deviceUUID, $deviceOS)
    {
        $user = \Undercity\UserQuery::create()->findOneBydeviceUUID($deviceUUID);

        if ($user == null) {
            $user = new User();
            $user->setDeviceUUID($deviceUUID);
            $user->setdeviceOS($deviceOS);
            $user->setCreateDate(new DateTime('now'));
        }

        if ($deviceOS !== $user->getdeviceOS()) {
            $user->setdeviceOS($deviceOS);
        }

        $user->setLastLoginDate(new DateTime('now'));
        $user->save();

        return $user;
    }
    
    public function loginFromEncryptedDataV1($encryptedLoginData, &$loginData)
    {
        if (array_key_exists('Login', $encryptedLoginData)) {
            $privateKey = openssl_get_privatekey(file_get_contents(RSA_SECRET_KEY));
            $encryptedData = base64_decode($encryptedLoginData['Login']);
        
            if (openssl_private_decrypt($encryptedData, 
                                        $decryptedData, 
                                        $privateKey, 
                                        OPENSSL_PKCS1_PADDING)) {

                $loginData = $decryptedData;
                return true;
            }
            return false;
        }
        return false;
    }
    
    public function loginFromEncryptedData($encryptedLoginData, &$loginData)
    {
        if (array_key_exists('key', $encryptedLoginData) &&
            array_key_exists('data', $encryptedLoginData)) {
            $privateKey = openssl_get_privatekey(file_get_contents(RSA_SECRET_KEY));
            $encryptedKey = base64_decode($encryptedLoginData['key']);
            $encryptedData = $encryptedLoginData['data'];

            if (openssl_private_decrypt($encryptedKey, 
                                        $decryptedKey, 
                                        $privateKey, 
                                        OPENSSL_PKCS1_PADDING)) {
                $loginData = $this->cryptoJsAesDecrypt($decryptedKey, $encryptedData);
                return true;
            }
        }
        return false;
    }
    
    public function createToken($loginData, $userId, &$token)
    {
        $timeElapsed = (new DateTime('now'))->getTimestamp() - $loginData['Now'];
        
        if ($timeElapsed > LOGIN_DATA_EXPIRE_SECONDS)
            return false;
        
        $iat = (new DateTime('now'))->getTimestamp();
        $exp = $iat + JWT_TOKEN_EXPIRE_SECONDS;
        
        $token = array (
            'usr'=> $userId,
            'iat'=> $iat,
            'exp'=> $exp,
            'context' => array(
                'user' => $userId
            )
        );
        
        $token = JWT::encode($token, JWT_TOKEN_SECRET_KEY);
        return true;
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