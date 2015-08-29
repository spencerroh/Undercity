<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-29
 */

$app->group('/cert', function () use ($app) {
    $app->get('/', function () use ($app) {
        echo file_get_contents('../certs/public.pem');
    });
});