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

var images = [];
frisby.create('Login To Server')
    .post(USER_API_ENDPOINT, {
        UserInfo: testUtils.generateUserInfo()
    })
    .after(function (err, res) {
        var sessionID = res.headers['set-cookie'];

        frisby.create('Upload a test image1')
            .addHeader('Cookie', sessionID)
            .post(IMAGES_API_ENDPOINT, pngImageData, {
                json: false,
                headers: {
                    'cookie': sessionID,
                    'content-type': 'multipart/form-data; boundary=' + pngImageData.getBoundary(),
                    'content-length': pngImageData.getLengthSync()
                }
            })
            .expectStatus(200)
            .afterJSON(function (json) {
                images.push(json.image);

                frisby.create('Upload a test image2')
                    .addHeader('Cookie', sessionID)
                    .post(IMAGES_API_ENDPOINT, jpgImageData, {
                        json: false,
                        headers: {
                            'cookie': sessionID,
                            'content-type': 'multipart/form-data; boundary=' + jpgImageData.getBoundary(),
                            'content-length': jpgImageData.getLengthSync()
                        }
                    })
                    .expectStatus(200)
                    .afterJSON(function (json) {
                        images.push(json.image);

                        frisby.create('Create a introduction of shop')
                            .addHeader('Cookie', sessionID)
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