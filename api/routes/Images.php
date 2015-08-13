<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-26
 * Time: 오후 4:18
 */

$app->group('/images', function () use ($app) {
    $app->options('/', function () use ($app) {
        $app->response->header('Allow', 'GET, POST, DELETE');
    });

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


            $filename = uniqid();// . '.' . pathinfo($file['name'])['extension'];

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

?>