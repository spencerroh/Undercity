/**
 * Created by 영태 on 2015-07-26.
 */
var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var NodeRSA = require('node-rsa');
var encoding = require('encoding');
var testUtils = require('./testUtils');

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

frisby.globalSetup({
    request: {
        headers: {
            'X-Device-Id': 'TEST_DEVICE_UUID'
        }
    }
});

var images = [];
frisby.create('Upload a test image1')
    .addHeader('content-type', 'multipart/form-data; boundary=' + pngImageData.getBoundary())
    .addHeader('content-length', pngImageData.getLengthSync())
    .post(IMAGES_API_ENDPOINT, pngImageData)
    .expectStatus(200)
    .afterJSON(function (json) {
        images.push(json.image);

        frisby.create('Upload a test image2')
            .addHeader('content-type', 'multipart/form-data; boundary=' + jpgImageData.getBoundary())
            .addHeader('content-length', jpgImageData.getLengthSync())
            .post(IMAGES_API_ENDPOINT, jpgImageData)
            .expectStatus(200)
            .afterJSON(function (json) {
                images.push(json.image);

                frisby.create('Create a introduction of shop')
                    .post(INTRO_API_ENDPOINT, {
                        Title: '아삭한 이연복 탕수육',
                        Description: '겁나 맛난 이연복 탕수육 먹으로 오세욜!',
                        Images: images
                    }, {json: true})
                    .inspectBody()
                    .expectStatus(200)
                    .toss();
            })
            .toss();
    })
    .toss();