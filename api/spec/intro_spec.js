/**
 * Created by 영태 on 2015-07-26.
 */
var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');
var NodeRSA = require('node-rsa');

var INTRO_API_ENDPOINT = process.env.API_ENDPOINT + 'intro/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';
var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';

var testPngPath = path.resolve(__dirname, 'resources/test.png');
var testJpgPath = path.resolve(__dirname, 'resources/test.jpg');

var pngImageData = new FormData();
pngImageData.append('image', fs.createReadStream(testPngPath), {
    knownLength: fs.statSync(testPngPath).size
});

var jpgImageData = new FormData();
jpgImageData.append('image', fs.createReadStream(testJpgPath), {
    knownLength: fs.statSync(testJpgPath).size
});


var publicKeyPEM =
    "-----BEGIN PUBLIC KEY-----" + "\n" +
    "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtaZXTFziKX/5EFUjiKdz" + "\n" +
    "6CIoT04McDOOYKbzn6V+HhNiBVTVxX/R2A7nlPWpUzCORihxJ3/gVrekpwGBECbq" + "\n" +
    "Rij1YrktS2AgrYuNGB2oxkEMmXaQP2FhJVeRm0rZJcc8xI44nEcqhovHV6CfoaSZ" + "\n" +
    "Ys8nqqYvpk2j7smGIOiclYnLcfsVRvdJFoySdlvfLjMEyC+vqhZKphWeSRuYAyiK" + "\n" +
    "XvTI44bk75LYYIfFyvdS6qvVsFvjv5ZDcFnVoqcJ+hj32eXGlYIXs3re15iaaY3R" + "\n" +
    "r8wqyZs3+4JN+EX1RXohrQFm7d/WbcMu1/LmNM7YMpTyRUga4ZAF7eBaW+9IX6gC" + "\n" +
    "0QIDAQAB" + "\n" +
    "-----END PUBLIC KEY-----" + "\n";
var publicKey = new NodeRSA(publicKeyPEM);
var userInfo = {
    DeviceToken: 'TEST_DEVICE_TOKEN',
    DeviceOS: 'ANDROID'
};
var encryptedUserInfo = publicKey.encrypt(userInfo, 'base64');

var images = [];
frisby.create('Login to Server')
    .post(USER_API_ENDPOINT + 'login', {
        UserInfo: encryptedUserInfo
    })
    .expectStatus(200)
    .expectJSON({
        status: 'success'
    })
    .after(function (err, res, body) {
        frisby.globalSetup({
            request: {
                headers: {
                    'Cookie': res.headers['set-cookie']
                }
            }
        });
        frisby.create('Is Login?')
            .addHeader('Cookie', res.headers['set-cookie'])
            .get(USER_API_ENDPOINT + 'is_login')
            .expectStatus(200)
            .toss();
        var session = res.headers['set-cookie'];
        frisby.create('Upload a test image1')
            .addHeader('Cookie', session)
            .post(IMAGES_API_ENDPOINT, pngImageData, {
                json: false,
                headers: {
                    'content-type': 'multipart/form-data; boundary=' + pngImageData.getBoundary(),
                    'content-length': pngImageData.getLengthSync()
                }
            })
            .expectStatus(200)
            .afterJSON(function (json, res) {
                images.push(json.image);

                frisby.create('Upload a test image2')
                    .addHeader('Cookie', session)
                    .post(IMAGES_API_ENDPOINT, jpgImageData, {
                        json: false,
                        headers: {
                            'content-type': 'multipart/form-data; boundary=' + jpgImageData.getBoundary(),
                            'content-length': jpgImageData.getLengthSync()
                        }
                    })
                    .expectStatus(200)
                    .afterJSON(function (json, res) {
                        images.push(json.image);

                        frisby.create('Create a introduction of shop')
                            .addHeader('Cookie', session)
                            .post(INTRO_API_ENDPOINT, {
                                Title: '아삭한 이연복 탕수육',
                                Description: '겁나 맛난 이연복 탕수육 먹으로 오세욜!',
                                Images: images
                            })
                            .expectStatus(200)
                            .toss();
                    })
                    .toss();
            })
            .toss();
    })
    .toss();