<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-10
 */
use Propel\Runtime\Propel;
use Propel\Runtime\Formatter\ObjectFormatter;

$app->group('/store', function () use ($app) {
    $app->post('/', function() use ($app) {
        $request = json_decode($app->request->getBody(), true);

        if (keyExists(array('Name', 'Address', 'Contact', 'ProductId', 'Description', 'Latitude', 'Longitude'), $request)) {
            $store = new \Undercity\Store();

            $store->setName($request['Name']);
            $store->setAddress($request['Address']);
            $store->setContact($request['Contact']);
            $store->setProductId($request['ProductId']);
            $store->setDescription($request['Description']);
            $store->setLatitude($request['Latitude']);
            $store->setLongitude($request['Longitude']);

            $store->setCreateDate(new DateTime('now'));

            if (array_key_exists('Images', $request)) {
                foreach($request['Images'] as $imageId) {
                    $storeImage = new \Undercity\StoreImage();
                    $storeImage->setImageId($imageId);
                    $store->addStoreImage($storeImage);
                }
            }

            try {
                $store->save();
                $store->toArray();
            } catch (Exception $e) {
                $app->response->setStatus(400);
            }
        } else {
            $app->response->setStatus(400);
        }
    });

    $app->get('/:id', function ($id) use ($app) {
        $shop = \Undercity\StoreQuery::create()->findPk($id);

        if ($shop != null) {
            echo json_encode($shop->toArray(), true);
        }
    });

    $app->get('/type/:type/gps/:latitude/:longitude', function ($type, $latitude, $longitude) use ($app) {
        $shops =
        \Undercity\StoreQuery::create()->withColumn('DISTANCE(latitude, longitude, '.$latitude.', '.$longitude.', "km")', 'Distance')
                                       ->orderBy('Distance')
                                       ->findByProductId($type);

        echo json_encode($shops->toArray(), true);
    });

    $app->get('/:from/:count', function ($from, $count) use ($app) {
        $lastStore = null;

        if ($from == -1) {
            $lastStore = \Undercity\StoreQuery::create()
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        } else {
            $lastStore = \Undercity\StoreQuery::create()
                ->filterById(array('max' => $from))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        }

        if ($lastStore != null) {
            $query = \Undercity\StoreQuery::create()
                ->filterById(array('max' => $lastStore->getId()))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC);
            if ($count != -1) {
                $query = $query->limit($count);
            }

            $stores = $query->find();

            echo json_encode($stores->toArray(), true);
        } else {
            echo json_encode(array(), true);
        }
    });

    $app->delete('/:id', function ($id) use ($app) {
        $store = \Undercity\StoreQuery::create()->findOneById($id);
        if ($store != null) {
            $store->getStoreImages()->delete();
            foreach ($store->getStoreImages() as $storeImage) {
                $storeImage->getImage()->delete();
            }
            $store->delete();
        } else {
            $app->response->setStatus(400);
        }
    });
});