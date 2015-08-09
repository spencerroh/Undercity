<?php
/**
 * Created by PhpStorm.
 * User: 영태
 * Date: 2015-07-26
 * Time: 오후 4:19
 */

$app->group('/banners', function () use ($app) {
    $app->options('/', function () use ($app) {
        $app->response->header('Allow', 'GET, POST, DELETE');
    });

    $app->post('/', function () use ($app) {
        $request = $app->request()->post();

        if (array_key_exists('Contact', $request) &&
            array_key_exists('ContactType', $request) &&
            array_key_exists('ImageId', $request)
        ) {
            $image = \Undercity\ImageQuery::create()->findPK($request['ImageId']);

            if ($image != NULL) {
                $banner = new \Undercity\Banner();
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

?>