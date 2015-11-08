<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-26
 */

use Propel\Runtime\Formatter\ObjectFormatter;

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

    $app->get('/images', function () use ($app) {
        $images = \Undercity\ImageQuery::create()->find();

        $imageFiles = array();
        foreach ($images as $image) {
            array_push($imageFiles, $image->getSource());
            if ($image->getSource() != $image->getSourceThumb()) {
                array_push($imageFiles, $image->getSourceThumb());
            }
        }

        $zip = new ZipArchive();
        $filename = 'temp/'.uniqid('zip').'.zip';
        $zip->open($filename, ZipArchive::CREATE);
        foreach ($imageFiles as $file) {
            $zip->addFile(RESOURCE_PATH . $file);
        }
        $zip->close();

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filename);
        $size = filesize($filename);
        $name = basename($filename);

        header('Cache-Control: private, max-age=120, must-revalidate');
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // long ago
        header("Content-Type: $mimeType");
        header('Content-Disposition: attachment; filename="' . $name . '";');
        header("Accept-Ranges: bytes");
        header('Content-Length: ' . filesize($filename));

        print readfile($filename);

        unlink($filename);
    });

    $app->get('/location', function () use ($app){
        $location = array('latitude' => 37.5555, 'longitude' => 127.55555);
        $user = \Undercity\UserQuery::create()->findPk(5);

        $con = \Propel\Runtime\Propel::getWriteConnection(\Undercity\Map\SaleTableMap::DATABASE_NAME);

        $sql = <<<SQL
          SELECT sales.*
          FROM sales
            LEFT JOIN (
              SELECT *
              FROM location_event_log
              WHERE user_id = :userId) as Sent
            ON sales.id = Sent.sale_id
          WHERE Sent.id is null and DISTANCE(latitude, longitude, :latitude, :longitude, 'km') < 200
SQL;
        $stmt = $con->prepare($sql);
        $stmt->execute(array(
            ':latitude' => $location['latitude'],
            ':longitude' => $location['longitude'],
            ':userId' => $user->getId()
        ));

        $formatter = new ObjectFormatter();
        $formatter->setClass('\Undercity\Sale');
        $sales = $formatter->format($con->getDataFetcher($stmt));

        $app->GCM->setDevices($user->getDeviceToken());
        foreach ($sales as $sale) {
            $app->GCM->send('생생정보시장', $sale->getTitle(), $sale->toArray());

            $log = new \Undercity\LocationEventLog();
            $log->setUser($user);
            $log->setSaleId($sale->getId());
            $log->save();
        }
    });
});