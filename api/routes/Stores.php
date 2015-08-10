<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-10
 */

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