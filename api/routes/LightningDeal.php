<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-12
 */

use \Undercity\LightningDealQuery as ItemQuery;
use \Undercity\LightningDealReply as Reply;
use \Undercity\LightningDealReplyQuery as ReplyQuery;

$app->group('/deal', function () use ($app) {
    $app->post('/', function () use ($app) {
        $request = json_decode($app->request->getBody(), true);

        if (array_key_exists('Description', $request) &&
            array_key_exists('Title', $request) &&
            array_key_exists('NickName', $request) &&
            array_key_exists('EndDate', $request)) {
            $lightningDeal = new \Undercity\LightningDeal();
            $lightningDeal->setDescription($request['Description']);
            $lightningDeal->setTitle($request['Title']);
            $lightningDeal->setNickName($request['NickName']);
            $lightningDeal->setEndDate($request['EndDate']);
            $lightningDeal->setUser($app->user);
            $lightningDeal->setCreateDate(new DateTime('now'));
            $lightningDeal->setLastUpdateDate(new DateTime('now'));

            if (array_key_exists('Images', $request)) {
                foreach ($request['Images'] as $image) {
                    $lightningDealImage = new \Undercity\LightningDealImage();
                    $lightningDealImage->setImageId($image);

                    $lightningDeal->addLightningDealImage($lightningDealImage);
                }
            }
            $lightningDeal->save();
        }
    });

    $app->get('/:from/:count', function ($from, $count) use ($app) {
        $lastItem = null;

        if ($from == -1) {
            $lastItem = \Undercity\LightningDealQuery::create()
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        } else {
            $lastItem = \Undercity\LightningDealQuery::create()
                ->filterById(array('max' => $from))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC)
                ->findOne();
        }

        if ($lastItem != null) {
            $query = \Undercity\LightningDealQuery::create()
                ->filterById(array('max' => $lastItem->getId()))
                ->orderById(\Propel\Runtime\ActiveQuery\Criteria::DESC);
            if ($count != -1) {
                $query = $query->limit($count);
            }

            $items = $query->find();

            echo json_encode($items->toArray(), true);
        } else {
            echo json_encode(array(), true);
        }
    });

    $app->get('/:id', function ($id) use ($app) {
        $item = \Undercity\LightningDealQuery::create()->findPk($id);

        if ($item != null) {
            echo json_encode($item->toArray(), true);
        }
    });

    $app->put('/:id', function ($id) use ($app) {
        $request = json_decode($app->request->getBody(), true);

        $item = \Undercity\LightningDealQuery::create()->findOneById($id);
        if (array_key_exists('Description', $request)) {
            $item->setDescription($request['Description']);
        }

        if (array_key_exists('Title', $request)) {
            $item->setTitle($request['Title']);
        }

        if (array_key_exists('NickName', $request)) {
            $item->setNickName($request['NickName']);
        }

        if (array_key_exists('EndDate', $request)) {
            $item->setNickName($request['EndDate']);
        }

        $item->setLastUpdateDate(new DateTime('now'));
    });

    $app->delete('/:id', function ($id) use ($app) {
        $item = \Undercity\LightningDealQuery::create()->findPk($id);

        if ($item != null) {
            $item->getLightningDealImages()->delete();
            $item->getLightningDealReplies()->delete();
            $item->delete();
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
});
