<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-10
 */

use \Undercity\IntroShop as Item;
use \Undercity\IntroShopQuery as ItemQuery;
use \Undercity\IntroShopReply as Reply;
use \Undercity\IntroShopReplyQuery as ReplyQuery;
use \Undercity\IntroShopImage as Image;
use \Undercity\IntroShopImageQuery as ImageQuery;

$app->group('/intro', function () use ($app) {
    $app->post('/', function () use ($app) {
        $request = json_decode($app->request->getBody(), true);

        if (array_key_exists('Description', $request) &&
            array_key_exists('Title', $request) &&
            array_key_exists('NickName', $request)) {
            $intro = new Item();
            $intro->setDescription($request['Description']);
            $intro->setTitle($request['Title']);
            $intro->setNickName($request['NickName']);
            $intro->setUser($app->user);
            $intro->setCreateDate(new DateTime('now'));
            $intro->setLastUpdateDate(new DateTime('now'));

            if (array_key_exists('Images', $request)) {
                foreach ($request['Images'] as $image) {
                    $introImage = new Image();
                    $introImage->setImageId($image);

                    $intro->addIntroShopImage($introImage);
                }
            }
            $intro->save();
        }
    });

    $app->get('/:from/:count', function ($from, $count) use ($app) {
        $lastIntroShop = null;

        if ($from == -1) {
            $lastIntroShop = ItemQuery::create()
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        } else {
            $lastIntroShop = ItemQuery::create()
                ->filterById(array('max' => $from))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        }

        if ($lastIntroShop != null) {
            $query = ItemQuery::create()
                ->filterById(array('max' => $lastIntroShop->getId()))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC);
            if ($count != -1) {
                $query = $query->limit($count);
            }

            $introShops = $query->find();

            echo json_encode($introShops->toArray(), true);
        } else {
            echo json_encode(array(), true);
        }
    });

    $app->get('/:id', function ($id) use ($app) {
        $intro = ItemQuery::create()->findPk($id);

        if ($intro != null) {
            echo json_encode($intro->toArray(), true);
        }
    });

    $app->put('/:id', function ($id) use ($app) {
        $request = json_decode($app->request->getBody(), true);

        $intro = ItemQuery::create()->findOneById($id);
        if (array_key_exists('Description', $request)) {
            $intro->setDescription($request['Description']);
        }

        if (array_key_exists('Title', $request)) {
            $intro->setTitle($request['Title']);
        }

        $intro->setLastUpdateDate(new DateTime('now'));
    });

    $app->delete('/:id', function ($id) use ($app) {
        $intro = ItemQuery::create()->findPk($id);

        if ($intro != null) {
            $intro->getIntroShopImages()->delete();
            foreach ($intro->getIntroShopImages() as $image) {
                $image->getImage()->delete();
            }
            $intro->getIntroShopReplies()->delete();
            $intro->delete();
        }
    });

    $app->get('/reply/:id', function ($id) use ($app) {
        $replies = ReplyQuery::create()->filterByPostId($id)
                                       ->filterByIsRemoved(false)
                                       ->find();
        echo json_encode($replies->toArray(), true);
    });

    $app->post('/reply/:id', function ($id) use ($app) {
        $request = json_decode($app->request->getBody(), true);

        if (array_key_exists('Description', $request) &&
            array_key_exists('NickName', $request)) {
            $post = ItemQuery::create()->findPk($id);

            if ($post)
            {
                $reply = new Reply();
                $reply->setUser($app->user);
                $reply->setNickName($request['NickName']);
                $reply->setPost($post);
                $reply->setDescription($request['Description']);
                $reply->setCreateDate(new DateTime('now'));
                $reply->setLastUpdateDate(new DateTime('now'));
                $reply->setIsRemoved(false);
                $reply->save();
            }
            else
            {
                $app->response->setStatus(404);
            }
        } else {
            $app->response->setStatus(400);
        }
    });

    $app->put('/reply/:rid', function($rid) use ($app) {
        $request = json_decode($app->request->getBody(), true);
        $reply = ReplyQuery::create()->findPk($rid);

        if ($reply) {
            if (array_key_exists('Description', $request)) {
                $reply->setDescription($request['Description']);
            }
            if (array_key_exists('NickName', $request)) {
                $reply->setNickName($request['NickName']);
            }
            $reply->setLastUpdateDate(new DateTime('now'));
            $reply->save();
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->delete('/reply/:rid', function($rid) use ($app) {
        $reply = ReplyQuery::create()->findPk($rid);
        if ($reply) {
            $reply->setIsRemoved(true);
            $reply->save();
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->post('/image/:pid/:iid', function ($pid, $iid) use ($app) {
        $item = ItemQuery::create()->findPk($pid);
        var_dump($item);
        if ($item) {
            $image = new Image();
            $image->setImageId($iid);
            $item->addIntroShopImage($image);
            $item->save();
        } else {
            $app->response->setStatus(404);
        }
    });

    $app->delete('/image/:pid/:iid', function ($pid, $iid) use ($app) {
        $image = ImageQuery::create()->filterByPostId($pid)->filterByImageId($iid)->findOne();
        if ($image) {
            $image->delete();
            $image->getImage()->delete();
        } else {
            $app->response->setStatus(404);
        }
    });
});