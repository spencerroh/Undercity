var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');

var API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var formForCreate = new FormData();
var formForUpdate = new FormData();

var testPngPath = path.resolve(__dirname, 'resources/test.png');
var testJpgPath = path.resolve(__dirname, 'resources/test.jpg');

formForCreate.append('image', fs.createReadStream(testPngPath), {
    knownLength: fs.statSync(testPngPath).size
});

formForUpdate.append('image', fs.createReadStream(testJpgPath), {
    knownLength: fs.statSync(testJpgPath).size
});


frisby.create('Update a image and get a id of image')
    .post(API_ENDPOINT, formForCreate, {
        json: false,
        headers: {
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
            .get(API_ENDPOINT + imageKey)
            .expectStatus(200)
            .expectHeaderContains('content-type', 'image/png')
            .toss();
    
        frisby.create('Modify a image')
            .post(API_ENDPOINT + imageKey, formForUpdate, {
                json: false,
                headers: {
                    'content-type': 'multipart/form-data; boundary=' + formForUpdate.getBoundary(),
                    'content-length': formForUpdate.getLengthSync()
                }
            })
            .expectStatus(200)
            .toss();

        frisby.create('Check image is modified')
            .get(API_ENDPOINT + imageKey)
            .expectStatus(200)
            .expectHeaderContains('content-type', 'image/jpeg')
            .toss();

        frisby.create('Delete a image')
            .delete(API_ENDPOINT + imageKey)
            .expectStatus(200)
            .after(function (errorCode, response, body) {
                frisby.create('Check image is really deleted')
                    .get(API_ENDPOINT + imageKey)
                    .expectStatus(404)
                    .toss();
            })
            .toss();
    })
    .toss();