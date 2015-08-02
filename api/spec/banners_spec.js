var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');

var BANNERS_API_ENDPOINT = process.env.API_ENDPOINT + 'banners/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var testPngPath = path.resolve(__dirname, 'resources/test.png');

var formForImage = new FormData();
formForImage.append('image', fs.createReadStream(testPngPath), {
    knownLength: fs.statSync(testPngPath).size
});

var formForBannerCreation = new FormData();
formForBannerCreation.append('ContactType', '0');   // 0: URL, 1: Phone
formForBannerCreation.append('Contact', 'http://www.google.co.kr');

var formForBannerUpdate = new FormData();
formForBannerUpdate.append('ContactType', '1');   // 0: URL, 1: Phone
formForBannerUpdate.append('Contact', '010-1234-5678');


frisby.create('Create a banner')
    .post(IMAGES_API_ENDPOINT, formForImage, {
        json: false,
        headers: {
            'content-type': 'multipart/form-data; boundary=' + formForImage.getBoundary(),
            'content-length': formForImage.getLengthSync()
        }
    })
    .expectStatus(200)
    .afterJSON(function (json) {
        frisby.create('Create a banner object')
            .post(BANNERS_API_ENDPOINT, {
                Contact: 'http://www.google.co.kr',
                ContactType: 0,
                ImageId: json.image
            }, {
                json: true,
                headers: {
                    'content-type': 'application/json'
                }
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
                    .get(BANNERS_API_ENDPOINT + json.Id)
                    .expectStatus(200)
                    .toss();

                frisby.create('Modify a banner')
                    .post(BANNERS_API_ENDPOINT + json.Id, formForBannerUpdate, {
                        json: false,
                        headers: {
                            'content-type': 'multipart/form-data; boundary=' + formForBannerUpdate.getBoundary(),
                            'content-length': formForBannerUpdate.getLengthSync()
                        }
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