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

$app->group('/intro', function () use ($app) {
    $app->post('/', function () use ($app) {
        if (!isset($_SESSION['USER_ID'])) {
            $app->response->setStatus(401);
            return;
        }

        $request = $app->request()->post();

        if (array_key_exists('Description', $request) &&
            array_key_exists('Title', $request)) {
            $intro = new \Undercity\IntroShop();
            $intro->setDescription($request['Description']);
            $intro->setTitle($request['Title']);
            $intro->setUserId($_SESSION['USER_ID']);

            if (array_key_exists('Images', $request)) {
                foreach ($request['Images'] as $image) {
                    $introImage = new \Undercity\IntroShopImage();
                    $introImage->setImageId($image);

                    $intro->addIntroShopImage($introImage);
                }
            }

            $intro->save();
        }
    });

    $app->get('/:from/:count', function ($from, $count) use ($app) {

    });

    $app->get('/:id', function ($id) use ($app) {
        $intro = \Undercity\IntroShopQuery::create()->findPk($id);

        if ($intro != null) {
            echo $intro->exportTo('JSON');
        }
    });

    $app->put('/:id', function ($id) use ($app) {

    });

    $app->delete('/:id', function ($id) use ($app) {
        $intro = \Undercity\IntroShopQuery::create()->findPk($id);

        if ($intro != null) {
            $intro->getIntroShopImages()->delete();
            $intro->getIntroShopReplies()->delete();
            $intro->delete();
        }
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
