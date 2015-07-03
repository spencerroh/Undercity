var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');

var API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var form = new FormData();
var testImagePath = path.resolve(__dirname, 'resources/test.png');

form.append('image', fs.createReadStream(testImagePath), {
    knownLength: fs.statSync(testImagePath).size
});


frisby.create('Update a image and get a id of image')
    .post(API_ENDPOINT, form, {
        json: false,
        headers: {
            'content-type': 'multipart/form-data; boundary=' + form.getBoundary(),
            'content-length': form.getLengthSync()
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
            .put(API_ENDPOINT + imageKey, form, {
                json: false,
                headers: {
                    'content-type': 'multipart/form-data; boundary=' + form.getBoundary(),
                    'content-length': form.getLengthSync()
                }
            })
            .expectStatus(200)
            .toss();
/*
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
            */
    })
    .toss();