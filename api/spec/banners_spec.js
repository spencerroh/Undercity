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

frisby.create('Login To Server')
    .post(USER_API_ENDPOINT, {
        UserInfo: testUtils.generateUserInfo()
    })
    .after(function (err, res, body) {
        var sessionID = res.headers['set-cookie'];
        frisby.create('Create a banner')
            .post(IMAGES_API_ENDPOINT, formForImage, {
                json: false,
                headers: {
                    'cookie': sessionID,
                    'content-type': 'multipart/form-data; boundary=' + formForImage.getBoundary(),
                    'content-length': formForImage.getLengthSync()
                }
            })
            .expectStatus(200)
            .afterJSON(function (json) {
                frisby.create('Create a banner object')
                    .addHeader('Cookie', sessionID)
                    .post(BANNERS_API_ENDPOINT, {
                        Contact: 'http://www.google.co.kr',
                        ContactType: 0,
                        ImageId: json.image
                    })
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
                            .addHeader('Cookie', sessionID)
                            .get(BANNERS_API_ENDPOINT + json.Id)
                            .expectStatus(200)
                            .toss();

                        frisby.create('Modify a banner')
                            .addHeader('Cookie', sessionID)
                            .post(BANNERS_API_ENDPOINT + json.Id, {
                                ContactType: 1,
                                Contact: "010-1234-5678"
                            })
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
                            .addHeader('Cookie', sessionID)
                            .delete(BANNERS_API_ENDPOINT + json.Id)
                            .expectStatus(200)
                            .toss();

                        frisby.create('Check banner is deleted')
                            .addHeader('Cookie', sessionID)
                            .get(BANNERS_API_ENDPOINT + json.Id)
                            .expectStatus(404)
                            .toss();
                    })
                    .toss();
            })
            .toss();
    })
    .toss();