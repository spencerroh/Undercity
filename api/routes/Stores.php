<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-10
 */

$app->group('/store', function () use ($app) {
    $app->post('/', function() use ($app) {
        $request = json_decode($app->request->getBody(), true);

        if (keyExists(array('Name', 'Address', 'Contact', 'Product', 'Description', 'Latitude', 'Longitude'), $request)) {
            $store = new \Undercity\Store();

            $store->setName($request['Name']);
            $store->setAddress($request['Address']);
            $store->setContact($request['Contact']);
            $store->setProduct($request['Product']);
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

            $store->save();
            $store->toArray();
            //echo json_encode(, true);
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