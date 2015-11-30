<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-11-07
 */

use Propel\Runtime\Formatter\ObjectFormatter;

$app->group('/location', function () use ($app) {
    $app->post('/', function () use ($app) {
        // 로직을 구현합시다.
        $location = json_decode($app->request->getBody(), true);
        $user = $app->user;

        $con = \Propel\Runtime\Propel::getWriteConnection(\Undercity\Map\SaleTableMap::DATABASE_NAME);

        $sql = <<<SQL
          SELECT sales.*
          FROM sales
            LEFT JOIN (
              SELECT *
              FROM location_event_log
              WHERE user_id = :userId) as Sent
            ON sales.id = Sent.sale_id
          WHERE Sent.id is null
            and sales.event_from <= curdate()
            and sales.event_to >= curdate()
            and DISTANCE(latitude, longitude, :latitude, :longitude, 'km') < 2
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