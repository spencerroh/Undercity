<?php
// setup autoload
require_once 'vendor/autoload.php';

// setup Propel
require_once 'config/config.php';

define('RESOURCE_PATH', 'resources/');

date_default_timezone_set('asia/seoul');

$app = new \Slim\Slim();
$app->auth = new \Undercity\AuthenticationService();
$app->add(new \Undercity\AuthenticationMiddleware());

// CORS settings
$app->response->header('Access-Control-Allow-Origin', '*');
$app->response->header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
$app->response->header('Access-Control-Allow-Headers', 'Content-Type, X-Device-ID');

// include all routers
$routeFiles = (array) glob('routes/*.php');
foreach($routeFiles as $routeFile) {
    require $routeFile;
}

$app->run();
?>
