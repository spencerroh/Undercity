var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var testUtils = require('./testUtils')

var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';
var BANNERS_API_ENDPOINT = process.env.API_ENDPOINT + 'banners/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var testPngPath = path.resolve(__dirname, 'resources/test.png');

var formForImage = new FormData();
formForImage.append('image', fs.createReadStream(testPngPath), {
    knownLength: fs.statSync(testPngPath).size
});

frisby.globalSetup({
    request: {
        headers: {
            'X-Device-Id': 'TEST_DEVICE_UUID'
        }
    }
});

frisby.create('Create a banner')
    .addHeader('content-type', 'multipart/form-data; boundary=' + formForImage.getBoundary())
    .addHeader('content-length', formForImage.getLengthSync())
    .post(IMAGES_API_ENDPOINT, formForImage)
    .expectStatus(200)
    .afterJSON(function (json) {
        frisby.create('Create a banner object')
            .post(BANNERS_API_ENDPOINT, {
                Contact: 'http://www.google.co.kr',
                ContactType: 0,
                ImageId: json.image
            }, {json: true})
            .expectStatus(200)
            .expectJSONTypes({
                Id: Number,
                ContactType: Number,
                Contact: String,
                ImageId: Number
            })
            .expectJSON({
                ContactType: 0,
                Contact: 'http://www.google.co.kr'
            })
            .afterJSON(function (json) {
                frisby.create('Get a banner')
                    .get(BANNERS_API_ENDPOINT + json.Id)
                    .expectStatus(200)
                    .toss();

                frisby.create('Modify a banner')
                    .post(BANNERS_API_ENDPOINT + json.Id, {
                        ContactType: 1,
                        Contact: "010-1234-5678"
                    }, {json: true})
                    .expectStatus(200)
                    .expectJSONTypes({
                        Id: Number,
                        ContactType: Number,
                        Contact: String,
                        ImageId: Number
                    })
                    .expectJSON({
                        ContactType: 1,
                        Contact: '010-1234-5678'
                    })
                    .toss();

                frisby.create('Delete a banner')
                    .delete(BANNERS_API_ENDPOINT + json.Id)
                    .expectStatus(200)
                    .toss();

                frisby.create('Check banner is deleted')
                    .get(BANNERS_API_ENDPOINT + json.Id)
                    .expectStatus(404)
                    .toss();
            })
            .toss();
    })
    .toss();