<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-26
 * Time: 오후 4:18
 */

$app->group('/images', function () use ($app) {
    function showImage($app, $path) {
        if (file_exists($path)) {
            $mimeType = mime_content_type($path);
            if (strpos(ACCEPTABLE_IMAGE_FORMAT, $mimeType) !== false) {
                $app->response->headers->set('content-type', $mimeType);
                $app->response->headers->set('content-length', filesize($path));

                fpassthru(fopen($path, 'rb'));

                return;
            }
        }

        $mimeType = mime_content_type(NOT_AVAILABLE_PHOTO);
        $app->response->headers->set('content-type', $mimeType);
        $app->response->headers->set('content-length', filesize(NOT_AVAILABLE_PHOTO));
        fpassthru(fopen(NOT_AVAILABLE_PHOTO, 'rb'));
    }

    function saveImage($app, $file) {
        $mimeType = mime_content_type($file);
        if (strpos(ACCEPTABLE_IMAGE_FORMAT, $mimeType) === false) {
            return false;
        }

        $filename = uniqid();
        $thumbnail = uniqid();

        list($w, $h) = getimagesize($file);
        $ratio = max($w, $h) / IMAGE_MAX_SIZE;

        // 만약 이미지 사이즈가 IMAGE_MAX_SIZE보다 크다면 IMAGE_MAX_SIZE로 이미지 조정
        if ($ratio > 1) {
            $image = $app->imageOps[$mimeType]['create']($file);
            $resampled = imagecreatetruecolor($w/$ratio, $h/$ratio);
            imagecopyresampled($resampled, $image, 0, 0, 0, 0, $w/$ratio, $h/$ratio, $w, $h);
            $app->imageOps[$mimeType]['save']($resampled, RESOURCE_PATH . $filename);
        } else {
            move_uploaded_file($file, RESOURCE_PATH . $filename);
        }

        // 썸네일 만들기
        list($w, $h) = getimagesize(RESOURCE_PATH . $filename);
        $ratio = max($w, $h) / IMAGE_THUMB_SIZE;

        if ($ratio > 1) {
            $image = $app->imageOps[$mimeType]['create'](RESOURCE_PATH . $filename);
            $resampled = imagecreatetruecolor($w/$ratio, $h/$ratio);
            imagecopyresampled($resampled, $image, 0, 0, 0, 0, $w/$ratio, $h/$ratio, $w, $h);
            $app->imageOps[$mimeType]['save']($resampled, RESOURCE_PATH . $thumbnail);
        } else {
            $thumbnail = $filename;
        }

        return array($filename, $thumbnail);
    }

    $app->get('/thumb/:id', function ($id) use ($app) {
        $image = \Undercity\ImageQuery::create()->findPK($id);

        if ($image != NULL) {
            $fileName = RESOURCE_PATH . $image->getSourceThumb();
            showImage($app, $fileName);
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->get('/:id', function ($id) use ($app) {
        $image = \Undercity\ImageQuery::create()->findPK($id);

        if ($image != NULL) {
            $fileName = RESOURCE_PATH . $image->getSource();
            showImage($app, $fileName);
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

            if (is_uploaded_file($file['tmp_name'])) {
                $mimeType = mime_content_type($file['tmp_name']);

                if (strpos(ACCEPTABLE_IMAGE_FORMAT, $mimeType) === false) {
                    $app->response->setStatus(400);
                    echo json_encode(array(
                        'error' => 'image format is not supported'
                    ));
                    return;
                }

                list($source, $thumb) = saveImage($app, $file['tmp_name']);

                $image = new \Undercity\Image();
                $image->setSource($source);
                $image->setSourceThumb($thumb);
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

    $app->post('/:id', function ($id) use ($app) {
        if (array_key_exists('image', $_FILES)) {
            if (!is_dir(RESOURCE_PATH)) {
                mkdir(RESOURCE_PATH);
            }

            $file = $_FILES['image'];

            if (is_uploaded_file($file['tmp_name'])) {
                $mimeType = mime_content_type($file['tmp_name']);

                if (strpos(ACCEPTABLE_IMAGE_FORMAT, $mimeType) === false) {
                    $app->response->setStatus(400);
                    echo json_encode(array(
                        'error' => 'image format is not supported'
                    ));
                    return;
                }

                list($source, $thumb) = saveImage($app, $file['tmp_name']);

                $image = \Undercity\ImageQuery::create()->findPk($id);
                $image->removeResources();

                $image->setSource($source);
                $image->setSourceThumb($thumb);
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
        if ($image != NULL) {
            $image->removeResources();
            $image->delete();
        } else {
            $app->response->setStatus(404);
        }
    });
});