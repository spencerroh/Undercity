<?php
/**
 * User: spencer.roh@gmail.com
 * Date: 2015-08-23
 */

// 업로드한 이미지가 저장될 경로
define('RESOURCE_PATH', 'resources/');
// 최대 이미지 크기(가로 또는 세로)
define('IMAGE_MAX_SIZE', 1280);
// 썸네일 이미지 크기(가로 또는 세로)
define('IMAGE_THUMB_SIZE', 512);
// 지원하는 이미지 포맷
define('ACCEPTABLE_IMAGE_FORMAT', 'image/png;image/jpeg;');
// 이미지 파일이 깨진 경우 보여줄 이미지 경로
define('NOT_AVAILABLE_PHOTO', 'statics/photo_not_available.jpg');
// 수신된 로그인 데이터의 만료시간
define('LOGIN_DATA_EXPIRE_SECONDS', 60);
// 토큰 만료 시간
define('JWT_TOKEN_EXPIRE_SECONDS', 3600);
// JWT Token Secret Key
define('JWT_TOKEN_SECRET_KEY', 'undercity');
// Private Key
define('RSA_SECRET_KEY', 'certs/private.pem');
// Symmetric Crypto Algorithm
define('SYM_CRYPTO_ALGORITHM', 'aes-256-cbc');
// GCM Server Key
define('SERVER_GCM_KEY', 'AIzaSyAhmO7JOx2TMefe4PNdo7DUqRxP3Mz7c4w');