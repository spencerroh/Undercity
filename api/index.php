<?php
// setup autoload
require_once 'vendor/autoload.php';

// setup Propel
require_once 'config/config.php';

define('RESOURCE_PATH', 'resources/');

$app = new \Slim\Slim();

$app->group('/banners', function () use ($app) {
    $app->get('/', function () use ($app) {

    });
});

$app->group('/images', function () use ($app) {
    $app->get('/:id', function($id) use ($app) {
        $image = \Undercity\ImagesQuery::create()->findPK($id);

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

    $app->post('/', function () use ($app) {
        if (array_key_exists('image', $_FILES)) {
            if (!is_dir(RESOURCE_PATH)) {
                mkdir(RESOURCE_PATH);
            }

            $file = $_FILES['image'];

            $filename = uniqid() . '.' . pathinfo($file['name'])['extension'];

            if (is_uploaded_file($file['tmp_name'])) {
                move_uploaded_file($file['tmp_name'], RESOURCE_PATH . $filename);

                $image = new \Undercity\Images();
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
    
    $app->put('/:id', function ($id) use ($app) {
        echo array_key_exists('image', $_FILES);
        var_dump($_FILES);
    });

    $app->delete('/:id', function ($id) use ($app) {
        $image = \Undercity\ImagesQuery::create()->findPK($id);

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
