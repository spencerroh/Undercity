var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var testUtils = require('./testUtils')

var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var testPngPath = path.resolve(__dirname, 'resources/test.png');
var testJpgPath = path.resolve(__dirname, 'resources/test_big.jpg');

var formForCreate = new FormData();
formForCreate.append('image', fs.createReadStream(testPngPath), {
    knownLength: fs.statSync(testPngPath).size
});

var formForUpdate = new FormData();
formForUpdate.append('image', fs.createReadStream(testJpgPath), {
    knownLength: fs.statSync(testJpgPath).size
});

frisby.globalSetup({
    request: {
        headers: {
            'X-Device-Id': 'TEST_DEVICE_UUID'
        }
    }
});

frisby.create('Update a image and get a id of image')
    .addHeader('content-type', 'multipart/form-data; boundary=' + formForCreate.getBoundary())
    .addHeader('content-length', formForCreate.getLengthSync())
    .post(IMAGES_API_ENDPOINT, formForCreate)
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSONTypes({
        image: Number
    })
    .afterJSON(function (json) {
        console.log(json.image);
        var imageKey = json.image;

        frisby.create('Get a image')
            .get(IMAGES_API_ENDPOINT + imageKey)
            .expectStatus(200)
            .expectHeaderContains('content-type', 'image/png')
            .toss();

        frisby.create('Modify a image')
            .addHeader('content-type', 'multipart/form-data; boundary=' + formForUpdate.getBoundary())
            .addHeader('content-length', formForUpdate.getLengthSync())
            .post(IMAGES_API_ENDPOINT + imageKey, formForUpdate)
            .expectStatus(200)
            .toss();

        frisby.create('Check image is modified')
            .get(IMAGES_API_ENDPOINT + imageKey)
            .expectStatus(200)
            .expectHeaderContains('content-type', 'image/jpeg')
            .toss();

        frisby.create('Delete a image')
            .delete(IMAGES_API_ENDPOINT + imageKey)
            .expectStatus(200)
            .after(function () {
                frisby.create('Check image is really deleted')
                    .get(IMAGES_API_ENDPOINT + imageKey)
                    .expectStatus(404)
                    .toss();
            })
            .toss();
    })
    .toss();