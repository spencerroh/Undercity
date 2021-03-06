<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-27
 * Time: 오후 9:46
 */
$app->group('/sales', function () use ($app) {
    $app->post('/', function () use ($app) {
        $request = json_decode($app->request->getBody(), true);

        $sale = new \Undercity\Sale();

        if (array_key_exists('Name', $request) &&
            array_key_exists('Address', $request) &&
            array_key_exists('Contact', $request) &&
            array_key_exists('Title', $request) &&
            array_key_exists('EventFrom', $request) &&
            array_key_exists('EventTo', $request) &&
            array_key_exists('Description', $request) &&
            array_key_exists('Latitude', $request) &&
            array_key_exists('Longitude', $request)) {
            $sale->setName($request['Name']);
            $sale->setAddress($request['Address']);
            $sale->setContact($request['Contact']);
            $sale->setTitle($request['Title']);
            $sale->setEventFrom(new DateTime($request['EventFrom']));
            $sale->setEventTo(new DateTime($request['EventTo']));
            $sale->setDescription($request['Description']);
            $sale->setLatitude($request['Latitude']);
            $sale->setLongitude($request['Longitude']);
            $sale->setCreateDate(new DateTime('now'));
            $sale->save();

            if (array_key_exists('Images', $request) &&
                is_array($request['Images'])) {
                $images = $request['Images'];

                foreach ($images as $imgKey) {
                    $image = \Undercity\ImageQuery::create()->findPk($imgKey);
                    if ($image != null) {
                        $saleImage = new \Undercity\SaleImages();
                        $saleImage->setImage($image);
                        $saleImage->setSale($sale);
                        $saleImage->save();
                    }
                }
            }

            $app->response->headers->set('Content-Type', 'application/json');

            $response = $sale->toArray();

            $saleImages = \Undercity\SaleImagesQuery::create()->findBySaleId($sale->getId());
            $images = array();

            if ($saleImages != null) {
                foreach ($saleImages as $s) {
                    array_push($images, $s->getImageId());
                }
            }
            $response['Images'] = $images;

            $app->response->headers->set('Content-Type', 'application/json');

            echo json_encode($response);
        } else {
            var_dump($request);
            $app->response->setStatus(400);
        }
    });

    $app->post('/:id', function ($id) use ($app) {
        $request = json_decode($app->request->getBody(), true);

        $sale = \Undercity\SaleQuery::create()
            ->findPk($id);

        if ($sale != null) {
            if (array_key_exists('Name', $request)) {
                $sale->setName($request['Name']);
            }

            if (array_key_exists('Address', $request)) {
                $sale->setAddress($request['Address']);
            }

            if (array_key_exists('Contact', $request)) {
                $sale->setContact($request['Contact']);
            }

            if (array_key_exists('Title', $request)) {
                $sale->setTitle($request['Title']);
            }

            if (array_key_exists('EventFrom', $request)) {
                $sale->setEventFrom(new DateTime($request['EventFrom']));
            }

            if (array_key_exists('EventTo', $request)) {
                $sale->setEventTo(new DateTime($request['EventTo']));
            }

            if (array_key_exists('Description', $request)) {
                $sale->setDescription($request['Description']);
            }
            
            if (array_key_exists('Latitude', $request)) {
                $sale->setLatitude($request['Latitude']);    
            }
            
            if (array_key_exists('Longitude', $request)) {
                $sale->setLongitude($request['Longitude']);    
            }

            $sale->save();

            $app->response->headers->set('Content-Type', 'application/json');
            echo $sale->exportTo('JSON');

        } else {
            $app->response->setStatus(400);
        }
    });

    $app->get('/:id', function ($id) use ($app) {
        $sale = \Undercity\SaleQuery::create()
            ->findPk($id);

        if ($sale != null) {
            $response = $sale->toArray();

            $saleImages = \Undercity\SaleImagesQuery::create()->findBySaleId($sale->getId());
            $images = array();
            if ($saleImages != null) {
                foreach ($saleImages as $saleImage) {
                    array_push($images, $saleImage->getImageId());
                }
            }
            $response['Images'] = $images;

            $app->response->headers->set('Content-Type', 'application/json');

            echo json_encode($response);
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->get('/:from/:count', function ($from, $count) use ($app) {
        $lastSales = null;

        if ($from == -1) {
            $lastSales = \Undercity\SaleQuery::create()
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        } else {
            $lastSales = \Undercity\SaleQuery::create()
                ->filterById(array('max' => $from))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        }

        if ($lastSales != null) {
            $query = \Undercity\SaleQuery::create()
                ->filterById(array('max' => $lastSales->getId()))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC);
            if ($count != -1) {
                $query = $query->limit($count);
            }

            $sales = $query->find();

            echo json_encode($sales->toArray(), true);
        } else {
            echo json_encode(array(), true);
        }
    });

    $app->delete('/:id', function ($id) use ($app) {
        $sale = \Undercity\SaleQuery::create()->findPk($id);

        if ($sale != null) {
            $sale->getSaleImages()->delete();
            foreach ($sale->getSaleImages() as $saleImage) {
                $saleImage->getImage()->delete();
            }
            $sale->delete();
        } else {
            $app->response->setStatus(404);
        }
    });

    // Modify a :id image
    $app->put('/images/:old/:new', function ($old, $new) use ($app) {
        $saleImage = \Undercity\SaleImagesQuery::create()->findOneByImageId($old);
        if ($saleImage != null) {
            $saleImage->setImageId($new);
            $saleImage->save();
        } else {
            $app->response->setStatus(404);
        }
    });

    // Remove a :id image from SaleImages
    $app->delete('/images/:id', function ($id) use ($app) {
        $saleImage = \Undercity\SaleImagesQuery::create()->findOneByImageId($id);
        if ($saleImage != null) {
            $saleImage->delete();
            foreach ($saleImage->getSaleImages() as $image) {
                $image->getImage()->delete();
            }
        } else {
            $app->response->setStatus(404);
        }
    });
});
?>