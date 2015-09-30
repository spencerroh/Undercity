<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-26
 */

/**
 * bookmark/shop/id
 * bookmark/sale/id
 */
$app->group('/bookmark', function () use ($app) {
    $app->get('/', function () use ($app) {
        $shops = \Undercity\StoreQuery::create()->useShopBookmarkQuery()
            ->filterByUser($app->user)
            ->endUse()
            ->find();
        $sales = \Undercity\SaleQuery::create()->useSaleBookmarkQuery()
            ->filterByUser($app->user)
            ->endUse()
            ->find();

        $response = array(
            'shops' => $shops->toArray(),
            'sales' => $sales->toArray()
        );

        echo json_encode($response, true);
    });

    $app->get('/shop/:id', function ($id) use ($app) {
        $isExists = \Undercity\ShopBookmarkQuery::create()->filterByUser($app->user)
                                                          ->filterByShopId($id)
                                                          ->exists();
        echo json_encode(array(
            'result' => $isExists
        ));
    });

    $app->post('/shop/:id', function ($id) use ($app) {
        $shop = \Undercity\StoreQuery::create()->findPk($id);

        if ($shop) {
            $bookmark = \Undercity\ShopBookmarkQuery::create()->filterByUser($app->user)
                                                              ->filterByShopId($id)
                                                              ->findOne();
            if ($bookmark == null) {
                $bookmark = new \Undercity\ShopBookmark();
                $bookmark->setUser($app->user);
                $bookmark->setShop($shop);
                $bookmark->save();
            } else {
                $app->response->setStatus(400);
            }
        } else {
            $app->response->setStatus(400);
        }
    });

    $app->delete('/shop/:id', function ($id) use ($app) {
        $bookmark = \Undercity\ShopBookmarkQuery::create()->filterByUser($app->user)
                                                          ->filterByShopId($id)
                                                          ->findOne();
        if ($bookmark) {
            $bookmark->delete();
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->get('/sale/:id', function ($id) use ($app) {
        $isExists = \Undercity\SaleBookmarkQuery::create()->filterByUser($app->user)
                                                          ->filterBySaleId($id)
                                                          ->exists();
        echo json_encode(array(
            'result' => $isExists
        ));
    });

    $app->post('/sale/:id', function ($id) use ($app) {
        $sale = \Undercity\SaleQuery::create()->findPk($id);

        if ($sale) {
            $bookmark = \Undercity\SaleBookmarkQuery::create()->filterByUser($app->user)
                                                              ->filterBySaleId($id)
                                                              ->findOne();
            if ($bookmark == null) {
                $bookmark = new \Undercity\SaleBookmark();
                $bookmark->setUser($app->user);
                $bookmark->setSale($sale);
                $bookmark->save();
            } else {
                $app->response->setStatus(400);
            }
        } else {
            $app->response->setStatus(400);
        }
    });

    $app->delete('/sale/:id', function ($id) use ($app) {
        $bookmark = \Undercity\SaleBookmarkQuery::create()->filterByUser($app->user)
                                                          ->filterBySaleId($id)
                                                          ->findOne();
        if ($bookmark) {
            $bookmark->delete();
        } else {
            $app->response->setStatus(404);
        }
    });


});