<?php
// setup autoload
require_once 'vendor/autoload.php';

// setup Propel
require_once 'config/config.php';

define('RESOURCE_PATH', 'resources/');

$app = new \Slim\Slim();

$app->group('/banners', function () use ($app) {
    $app->post('/', function ()  use ($app) {
        $banner = new \Undercity\Banner();

        $request = $app->request()->post();

        if (array_key_exists('Contact', $request) &&
            array_key_exists('ContactType', $request) &&
            array_key_exists('ImageId', $request)) {
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

            if ($oldImage != null)
            {
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

$app->group('/images', function () use ($app) {
    $app->get('/:id', function($id) use ($app) {
        $image = \Undercity\ImageQuery::create()->findPK($id);

        if ($image != NULL) {
            $fileName = RESOURCE_PATH.$image->getSource();
            $mimeType = mime_content_type($fileName);
            if (file_exists($fileName) &&
                strpos($mimeType, 'image') !== false ) {
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

                $oldFileName = RESOURCE_PATH.$image->getSource();

                if (file_exists($oldFileName))
                {
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
        }
        else {
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

$app->run();
?>
