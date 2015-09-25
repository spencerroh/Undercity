<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-26
 */


$app->group('/test', function () use ($app) {
    $app->get('/gcm', function () use ($app) {
        $app->GCM->setDevices('APA91bHvoQNsX4NEr9gR-lzQhND1TemQLh04aiIboWI3Nws8OQwZpZGPQQtwGb9a61PhyQi7E1UqeuuBECVnA-og2XayP-EW8BybcAs3IpIsSVk_5EbA1lzvv89ThK1dqqwpKv7LeeXdgttujtJgD-dXdG_qo0w3Mg');
        $app->GCM->send('Title', 'Hello');
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
            $store->setDescription('설명');
            $store->setLatitude($baseX + $baseDelta * $ratioX);
            $store->setLongitude($baseY + $baseDelta * $ratioY);
            $store->setCreateDate(new DateTime('now'));
            $store->save();
        }
    });
    $app->get('/demo/:sale', function ($sale) use ($app) {
        $users = \Undercity\UserQuery::create()->find();
        $sale = \Undercity\SaleQuery::create()->findPK($sale);
        foreach ($users as $user) {
            $app->GCM->setDevices($user->getDeviceToken());
            //$app->GCM->setDevices('APA91bHvoQNsX4NEr9gR-lzQhND1TemQLh04aiIboWI3Nws8OQwZpZGPQQtwGb9a61PhyQi7E1UqeuuBECVnA-og2XayP-EW8BybcAs3IpIsSVk_5EbA1lzvv89ThK1dqqwpKv7LeeXdgttujtJgD-dXdG_qo0w3Mg');
            $app->GCM->send('생생정보시장', $sale->getTitle(), $sale->toArray());
        }
    });
});