<?php
// setup autoload
require_once 'vendor/autoload.php';

// setup Propel
require_once 'config/config.php';

require_once 'config.php';

date_default_timezone_set('asia/seoul');

$app = new \Slim\Slim();
$app->auth = new \Undercity\AuthenticationService();
$app->add(new \Undercity\AuthenticationMiddleware());

// 주의: imageOps 배열은 ACCEPTABLE_IMAGE_FORMAT 에 정의된 이미지 포맷을 읽고, 저장하는
// 함수를 반드시 선언해야 한다.
$app->imageOps = array(
    'image/png' => array (
      'create' => 'imagecreatefrompng',
      'save' => 'imagepng'
    ),
    'image/jpeg' => array (
        'create' => 'imagecreatefromjpeg',
        'save' => 'imagejpeg'
    )
);

// CORS settings
$app->response->header('Access-Control-Allow-Origin', '*');
$app->response->header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
$app->response->header('Access-Control-Allow-Headers', 'Content-Type, X-Device-ID');

function keyExists($keys, $array) {
    $isAllExists = true;
    foreach($keys as $key) {
        $isAllExists &= array_key_exists($key, $array);
    }
    return $isAllExists;
}

// include all routers
$routeFiles = (array) glob('routes/*.php');
foreach($routeFiles as $routeFile) {
    require $routeFile;
}

$app->run();

?>
