<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-26
 */


$app->group('/test', function () use ($app) {
    $app->get('/gcm', function () use ($app) {
        $app->GCM->setDevices('APA91bHxhzlBESt1STUj5pR1qqa-1c5gq_ybbZ7ebgwTn4Q7_yCrAicLDtRWFP86EAOR6CmK7dVDzMCjwVHRzBCY0Nf_0kFl2vY_FsM4WyJjE1DtYiLINepHpBymGHPRbt6bgEoqYcr13knWbaAmIsRrP1QdNI6IIg');
        $app->GCM->send('Hello');
    });
    $app->get('/addTestShop', function () use ($app) {
        $baseX = 37.264;
        $baseY = 127.020;
        $baseDelta = 0.004;
    
        for ($i = 0; $i < 1000; $i++) {
            $ratioX = rand(0, 1000)/1000;
            $ratioY = rand(0, 1000)/1000;
            
            $store = new \Undercity\Store();
            $store->setName('관리자');
            $store->setAddress('주소');
            $store->setContact(rand(0, 10000000));
            $store->setProduct('품목');
            $store->setDescription('설명');
            $store->setLatitude($baseX + $baseDelta * $ratioX);
            $store->setLongitude($baseY + $baseDelta * $ratioY);
            $store->setCreateDate(new DateTime('now'));
            $store->save();
        }
    });
});