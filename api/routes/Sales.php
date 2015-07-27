<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-27
 * Time: 오후 9:46
 */
$app->group('/sales', function () use ($app) {
    $app->post('/', function () use ($app) {
        $request = $app->request()->post();

        $sale = new \Undercity\Sale();

        if (array_key_exists('Name', $request) &&
            array_key_exists('Address', $request) &&
            array_key_exists('Contact', $request) &&
            array_key_exists('Title', $request) &&
            array_key_exists('EventFrom', $request) &&
            array_key_exists('EventTo', $request) &&
            array_key_exists('Description', $request) &&
            array_key_exists('GPS', $request) &&
            preg_match('/[0-9]+,[0-9]+/', $request['GPS'])) {
            $sale->setName($request['Name']);
            $sale->setAddress($request['Address']);
            $sale->setContact($request['Contact']);
            $sale->setTitle($request['Title']);
            $sale->setEventFrom(new DateTime($request['EventFrom']), new DateTimeZone('Asia/Seoul'));
            $sale->setEventTo(new DateTime($request['EventTo']), new DateTimeZone('Asia/Seoul'));
            $sale->setDescription($request['Description']);
            $sale->setGPS($request['GPS']);
            $sale->setCreateDate(new DateTime('now'), new DateTimeZone('Asia/Seoul'));
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
            $app->response->setStatus(400);
        }
    });

    $app->post('/:id', function ($id) use ($app) {
        $request = $app->request()->post();

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

            if (array_key_exists('GPS', $request) &&
                preg_match('/[0-9]+,[0-9]+/', $request['GPS'])
            ) {
                $sale->setGPS($request['GPS']);
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

    $app->delete('/:id', function ($id) use ($app) {
        $sale = \Undercity\SaleQuery::create()
            ->findPk($id);

        if ($sale != null) {
            $saleImages = \Undercity\SaleImagesQuery::create()->findBySaleId($sale->getId());
            foreach ($saleImages as $saleImage) {
                $saleImage->delete();
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
        } else {
            $app->response->setStatus(404);
        }
    });
});
?>