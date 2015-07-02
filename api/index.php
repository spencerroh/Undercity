<?php
// setup autoload
require_once 'vendor/autoload.php';

// setup Propel
require_once 'config/config.php';

define('RESOURCE_PATH', 'resources/');

$app = new \Slim\Slim();

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

    $app->post('/', function ($id) use ($app) {
        $image = new \Undercity\Images();
        $image->setSource('aurora_blue.jpg');
        $image->setSourceThumb('aurora_blue.jpg');
        $image->save();
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
