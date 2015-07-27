<?php
// setup autoload
require_once 'vendor/autoload.php';

// setup Propel
require_once 'config/config.php';

session_start();

define('RESOURCE_PATH', 'resources/');

date_default_timezone_set('asia/seoul');

$app = new \Slim\Slim();

$routeFiles = (array) glob('routes/*.php');
foreach($routeFiles as $routeFile) {
    require $routeFile;
}

$app->group('/store', function () use ($app) {
    $app->get('/:from/:count', function ($from, $count) use ($app) {
        $lastStore = null;

        if ($from == -1) {
            $lastStore = \Undercity\StoreQuery::create()
                ->orderByCreatedate(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        } else {
            $lastStore = \Undercity\StoreQuery::create()
                ->findPk($from);
        }

        if ($lastStore != null) {
            $createDate = $lastStore->getCreateDate();

            \Undercity\StoreQuery::create()
                ->filterByCreateDate();

        } else {
            $app->response->setStatus(204);
            return;
        }
    });
});

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

$app->group('/intro', function () use ($app) {
    $app->post('/', function () use ($app) {
        $request = $app->request()->post();

        if (array_key_exists('Description', $request) &&
            array_key_exists('UserId', $request)) {
            $intro = new \Undercity\IntroShop();
            $intro->setDescription($request['Description']);

            //$user = \Undercity\UserQuery::create()->findPk()

        }
    });

    $app->get('/:from/:count', function ($from, $count) use ($app) {

    });

    $app->get('/:id', function ($id) use ($app) {

    });

    $app->put('/:id', function ($id) use ($app) {

    });

    $app->delete('/:id', function ($id) use ($app) {

    });

    $app->get('/:id/reply', function ($id) use ($app) {

    });

    $app->post('/:id/reply', function ($id) use ($app) {

    });

    $app->put('/:id/reply/:rid', function($id, $rid) use ($app) {

    });

    $app->delete('/:id/reply/:rid', function($id, $rid) use ($app) {

    });

    $app->get('/:id:/images/', function ($id) use ($app) {

    });

    $app->post('/:id:/images/', function ($id) use ($app) {

    });

    $app->put('/:id:/images/:iid', function ($id, $iid) use ($app) {

    });

    $app->delete('/:id:/images/:iid', function ($id, $iid) use ($app) {

    });
});

$app->run();
?>
