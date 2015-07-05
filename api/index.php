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

        if (array_key_exists('contact', $request) &&
            array_key_exists('contact_type', $request) &&
            array_key_exists('image_id', $request)) {

            $image = \Undercity\ImageQuery::create()->findPK($request['image_id']);

            if ($image != NULL) {
                $banner->setContact($request['contact']);
                $banner->setContactType($request['contact_type']);
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
        echo ($banners->toJSON())['Banners'];
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

        if ($image != NULL) {
            $fileName = RESOURCE_PATH.$image->getSource();

            if (file_exists($fileName))
            {
                unlink($fileName);
            }

            $image->delete();
        } else {
            $app->response->setStatus(404);
        }
    });
});

$app->run();
?>
