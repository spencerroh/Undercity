<?php

namespace Undercity;

class GCMPushNotification {

    var $url = 'https://android.googleapis.com/gcm/send';
    var $serverApiKey = "";
    var $devices = array();
    
    public function __construct($apiKeyIn)
    {
        $this->serverApiKey = $apiKeyIn;
    }

    function setDevices($deviceIds){

        if(is_array($deviceIds)){
            $this->devices = $deviceIds;
        } else {
            $this->devices = array($deviceIds);
        }
    }

    function send($title, $msg, $data){
        if(!is_array($this->devices) || count($this->devices) == 0){
            $this->error("No devices set");
        }

        if(strlen($this->serverApiKey) < 8){
            $this->error("Server API Key not set");
        }


        $fields = array(
            'registration_ids'  => $this->devices,
            'data'              => array( 
                'title' => $title,
                'message' => $msg,
                'data' => $data)
        );

        $headers = array(
            'Authorization: key=' . $this->serverApiKey,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt( $ch, CURLOPT_URL, $this->url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
 		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
        // Execute post
        $result = curl_exec($ch);

        // Close connection
        curl_close($ch);

        echo '|'.$result.'|';
        return $result;
    }

    function error($msg){
        echo "Android send notification failed with error:";
        echo "\t" . $msg;
        exit(1);
    }
}