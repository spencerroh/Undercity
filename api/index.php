<?php
// setup autoload
require_once 'vendor/autoload.php';

// setup Propel
require_once 'config/config.php';

define('RESOURCE_PATH', 'resources/');

$app = new \Slim\Slim();

$app->group('/images', function () use ($app) {
    $app->get('/:id', function ($id) use ($app) {
        $image = \Undercity\ImageQuery::create()->findPK($id);

        if ($image != NULL) {
            $fileName = RESOURCE_PATH . $image->getSource();
            $mimeType = mime_content_type($fileName);
            if (file_exists($fileName) &&
                strpos($mimeType, 'image') !== false
            ) {
                $app->response->headers->set('Content-Type', $mimeType);
                $app->response->headers->set('Content-Length', filesize($fileName));

                fpassthru(fopen($fileName, 'rb'));
            } else {
                $image->delete();
                $app->response->setStatus(404);
            }

        } else {
            $app->response->setStatus(404);
        }
    });

    $app->post('/:id', function ($id) use ($app) {
        $image = \Undercity\ImageQuery::create()->findPK($id);

        if ($image != NULL) {
            $file = $_FILES['image'];
            $fileName = uniqid() . '.' . pathinfo($file['name'])['extension'];

            if (is_uploaded_file($file['tmp_name'])) {
                move_uploaded_file($file['tmp_name'], RESOURCE_PATH . $fileName);

                $oldFileName = RESOURCE_PATH . $image->getSource();

                if (file_exists($oldFileName)) {
                    unlink($oldFileName);
                }

                $image->setSource($fileName);
                $image->save();

                $app->response->headers->set('Content-Type', 'application/json');

                $response = array(
                    'image' => $image->getId()
                );

                echo json_encode($response);
            }
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->post('/', function () use ($app) {
        if (array_key_exists('image', $_FILES)) {
            if (!is_dir(RESOURCE_PATH)) {
                mkdir(RESOURCE_PATH);
            }

            $file = $_FILES['image'];
            $filename = uniqid() . '.' . pathinfo($file['name'])['extension'];

            if (is_uploaded_file($file['tmp_name'])) {
                move_uploaded_file($file['tmp_name'], RESOURCE_PATH . $filename);

                $image = new \Undercity\Image();
                $image->setSource($filename);
                $image->setSourceThumb($filename);
                $image->save();

                $app->response->headers->set('Content-Type', 'application/json');

                $response = array(
                    'image' => $image->getId()
                );

                echo json_encode($response);
            }
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->delete('/:id', function ($id) use ($app) {
        $image = \Undercity\ImageQuery::create()->findPK($id);
        echo '1';
        if ($image != NULL) {
            $image->delete();
        } else {
            $app->response->setStatus(404);
        }
    });
});

$app->group('/banners', function () use ($app) {
    $app->post('/', function () use ($app) {
        $banner = new \Undercity\Banner();

        $request = $app->request()->post();

        if (array_key_exists('Contact', $request) &&
            array_key_exists('ContactType', $request) &&
            array_key_exists('ImageId', $request)
        ) {
            $image = \Undercity\ImageQuery::create()->findPK($request['ImageId']);

            if ($image != NULL) {
                $banner->setContact($request['Contact']);
                $banner->setContactType($request['ContactType']);
                $banner->setImage($image);

                $banner->save();

                echo $banner->exportTo('JSON');
            } else {
                $app->response->setStatus(404);
            }

        } else {
            $app->response->setStatus(400);
        }
    });

    $app->get('/', function () use ($app) {
        $banners = \Undercity\BannerQuery::create()->find();
        echo json_encode($banners->toArray());
    });

    $app->get('/:id', function ($id) use ($app) {
        $banner = \Undercity\BannerQuery::create()->findPK($id);

        if ($banner != null) {
            echo json_encode($banner->toArray());
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->post('/:id', function ($id) use ($app) {
        $request = $app->request()->post();

        $banner = \Undercity\BannerQuery::create()->findPK($id);
        $oldImage = null;

        if ($banner != null) {
            if (array_key_exists('ImageId', $request)) {
                $newImage = \Undercity\ImageQuery::create()->findPK($request['ImageId']);
                if ($newImage != null) {
                    $oldImage = $banner->getImage();
                    $banner->setImage($newImage);
                } else {
                    $app->response->setStatus(404);
                    return;
                }


                if ($oldImage != null) {
                    $oldImage->delete();
                }
            }

            if (array_key_exists('Contact', $request)) {
                $banner->setContact($request['Contact']);
            }

            if (array_key_exists('ContactType', $request)) {
                $banner->setContactType($request['ContactType']);
            }

            $banner->save();

            if ($oldImage != null) {
                $oldImage->delete();
            }

            echo json_encode($banner->toArray());

        } else {
            $app->response->setStatus(400);
        }
    });

    $app->delete('/:id', function ($id) use ($app) {
        $banner = \Undercity\BannerQuery::create()->findPK($id);

        if ($banner != null) {
            $image = \Undercity\ImageQuery::create()->findPK($banner->getImageId());

            $banner->delete();

            if ($image != null) {
                $image->delete();
            }
        } else {
            $app->response->setStatus(404);
        }

    });
});

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

$app->group('/user', function () use ($app) {
    $app->post('/', function () use ($app) {
        $user = new \Undercity\User();
        $request = $app->request()->post();

        if (array_key_exists('DeviceToken', $request)) {
            $user->setDeviceToken($request['DeviceToken']);
            $user->save();
        } else {
            $app->response->setStatus(400);
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

            $app->response->headers->set('Content-Type', 'application/json');
            echo $sale->exportTo('JSON');

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
            $sale->setCreateDate(new DateTime('now'));
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
            $app->response->headers->set('Content-Type', 'application/json');
            echo $sale->exportTo('JSON');
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->delete('/:id', function ($id) use ($app) {
        $sale = \Undercity\SaleQuery::create()
            ->findPk($id);

        if ($sale != null) {
            $sale->delete();
        } else {
            $app->response->setStatus(404);
        }
    });
});

$app->run();
?>
