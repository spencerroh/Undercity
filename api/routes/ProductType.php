<?php

$app->group('/product', function () use ($app) {
    $app->post('/', function () use ($app) {
        $request = json_decode($app->request->getBody(), true);

        if (array_key_exists('Type', $request)) {
            $productType = new \Undercity\ProductType();
            $productType->setType($request['Type']);
            $productType->save();
        }
    });

    $app->get('/', function () use ($app) {
        $items = \Undercity\ProductTypeQuery::create()->find();
        echo json_encode($items->toArray(), true);
    });

    $app->put('/:id', function ($id) use ($app) {
        $item = \Undercity\ProductTypeQuery::create()->findPk($id);
        if ($item == null) {
            $app->response->setStatus(404);
        } else {
            $request = json_decode($app->request->getBody(), true);
            if (array_key_exists('Type', $request)) {
                $item->setType($request['Type']);
                $item->save();
            } else {
                $app->response->setStatus(400);
            }
        }
    });

    $app->delete('/:id', function ($id) use ($app) {
        $item = \Undercity\ProductTypeQuery::create()->findPk($id);
        if ($item == null) {
            $app->response->setStatus(404);
        } else {
            $item->delete();
        }
    });
});