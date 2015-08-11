/**
 * Created by 영태 on 2015-08-11.
 */

var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');
var STORE_API_ENDPOINT = process.env.API_ENDPOINT + 'store/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var testPngPath = path.resolve(__dirname, 'resources/test.png');
var testJpgPath = path.resolve(__dirname, 'resources/test.jpg');

var pngImage = new FormData();
pngImage.append('image', fs.createReadStream(testPngPath), {
    knownLength: fs.statSync(testPngPath).size
});
var jpgImage = new FormData();
jpgImage.append('image', fs.createReadStream(testJpgPath), {
    knownLength: fs.statSync(testJpgPath).size
});

frisby.globalSetup({
    request: {
        headers: {
            'X-Device-Id': 'TEST_DEVICE_UUID'
        }
    }
});

var storeImages = [];

frisby.create('Upload sale images1')
    .addHeader('content-type', 'multipart/form-data; boundary=' + pngImage.getBoundary())
    .addHeader('content-length', pngImage.getLengthSync())
    .post(IMAGES_API_ENDPOINT, pngImage)
    .afterJSON(function (json) {
        storeImages.push(json.image);

frisby.create('Upload sale images2')
    .addHeader('content-type', 'multipart/form-data; boundary=' + jpgImage.getBoundary())
    .addHeader('content-length', jpgImage.getLengthSync())
    .post(IMAGES_API_ENDPOINT, jpgImage)
    .afterJSON(function (json) {
        storeImages.push(json.image);

        frisby.create('Create a store')
            .post(STORE_API_ENDPOINT, {
                Name: '하모니마트',
                Address: '경기도 용인시 기흥구 구갈동 강남마을 4단지 상가',
                Contact: '031-254-7852',
                Product: '잡화',
                Description: '우리 으여니형님이 운영하는 마트임메다',
                GPS: '37.548, 127.5',
                Images: storeImages
            }, { json: true })
            .expectStatus(200)
            .inspectBody()
            .toss();
    })
    .toss();
    })
    .toss();