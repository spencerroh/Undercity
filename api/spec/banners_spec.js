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
formForBannerCreation.append('contact_type', '0');   // 0: URL, 1: Phone
formForBannerCreation.append('contact', 'http://www.google.co.kr');


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
        var imageKey = json.image;
        formForBannerCreation.append('image_id', imageKey);

        frisby.create('Create a banner object')
            .post(BANNERS_API_ENDPOINT, formForBannerCreation, {
                json: false,
                headers: {
                    'content-type': 'multipart/form-data; boundary=' + formForBannerCreation.getBoundary(),
                    'content-length': formForBannerCreation.getLengthSync()
                }
            })
            .expectStatus(200)
            .expectJSONTypes({
                Id: Number,
                ContactType: Number,
                Contact: String
            })
            .expectJSON({
                ContactType: 0,
                Contact: 'http://www.google.co.kr'
            })
            .afterJSON(function (json) {

            })
            .toss();
    })
    .toss();