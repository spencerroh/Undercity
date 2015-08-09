var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var testUtils = require('./testUtils')

var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var testPngPath = path.resolve(__dirname, 'resources/test.png');
var testJpgPath = path.resolve(__dirname, 'resources/test.jpg');

var formForCreate = new FormData();
formForCreate.append('image', fs.createReadStream(testPngPath), {
    knownLength: fs.statSync(testPngPath).size
});

var formForUpdate = new FormData();
formForUpdate.append('image', fs.createReadStream(testJpgPath), {
    knownLength: fs.statSync(testJpgPath).size
});

frisby.create('Login To Server')
    .post(USER_API_ENDPOINT, {
        UserInfo: testUtils.generateUserInfo()
    })
    .after(function (err, res) {
        var sessionID = res.headers['set-cookie'];

        frisby.create('Update a image and get a id of image')
            .post(IMAGES_API_ENDPOINT, formForCreate, {
                json: false,
                headers: {
                    'cookie': sessionID,
                    'content-type': 'multipart/form-data; boundary=' + formForCreate.getBoundary(),
                    'content-length': formForCreate.getLengthSync()
                }
            })
            .expectStatus(200)
            .expectHeaderContains('content-type', 'application/json')
            .expectJSONTypes({
                image: Number
            })
            .afterJSON(function (json) {
                var imageKey = json.image;

                frisby.create('Get a image')
                    .addHeader('Cookie', sessionID)
                    .get(IMAGES_API_ENDPOINT + imageKey)
                    .expectStatus(200)
                    .expectHeaderContains('content-type', 'image/png')
                    .toss();

                frisby.create('Modify a image')
                    .post(IMAGES_API_ENDPOINT + imageKey, formForUpdate, {
                        json: false,
                        headers: {
                            'cookie': sessionID,
                            'content-type': 'multipart/form-data; boundary=' + formForUpdate.getBoundary(),
                            'content-length': formForUpdate.getLengthSync()
                        }
                    })
                    .expectStatus(200)
                    .toss();

                frisby.create('Check image is modified')
                    .addHeader('Cookie', sessionID)
                    .get(IMAGES_API_ENDPOINT + imageKey)
                    .expectStatus(200)
                    .expectHeaderContains('content-type', 'image/jpeg')
                    .toss();

                frisby.create('Delete a image')
                    .addHeader('Cookie', sessionID)
                    .delete(IMAGES_API_ENDPOINT + imageKey)
                    .expectStatus(200)
                    .after(function () {
                        frisby.create('Check image is really deleted')
                            .addHeader('Cookie', sessionID)
                            .get(IMAGES_API_ENDPOINT + imageKey)
                            .expectStatus(404)
                            .toss();
                    })
                    .toss();
            })
            .toss();
    })
    .toss();