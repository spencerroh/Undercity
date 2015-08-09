var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');
var testUtils = require('./testUtils')

var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';
var SALES_API_ENDPOINT = process.env.API_ENDPOINT + 'sales/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

var salesEventInfoWithNoInfo = new FormData();

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



var salesImages = [];

frisby.create('Login To Server')
    .post(USER_API_ENDPOINT, {
        UserInfo: testUtils.generateUserInfo()
    })
    .after(function (err, res, body) {
        var sessionID = res.headers['set-cookie'];

        frisby.create('Create a Sales Event with no information')
            .post(SALES_API_ENDPOINT, salesEventInfoWithNoInfo, {
                json: false,
                headers: {
                    'cookie': sessionID,
                    'content-type': 'multipart/form-data; boundary=' + salesEventInfoWithNoInfo.getBoundary(),
                    'content-length': salesEventInfoWithNoInfo.getLengthSync()
                }
            })
            .expectStatus(400)
            .toss();

        frisby.create('Upload sale images1')
            .post(IMAGES_API_ENDPOINT, pngImage, {
                json: false,
                headers: {
                    'cookie': sessionID,
                    'content-type': 'multipart/form-data; boundary=' + pngImage.getBoundary(),
                    'content-length': pngImage.getLengthSync()
                }
            })
            .afterJSON(function (json) {
                salesImages.push(json.image);

                frisby.create('Upload sale images2')
                    .addHeader('Cookie', sessionID)
                    .post(IMAGES_API_ENDPOINT, jpgImage, {
                        json: false,
                        headers: {
                            'cookie': sessionID,
                            'content-type': 'multipart/form-data; boundary=' + jpgImage.getBoundary(),
                            'content-length': jpgImage.getLengthSync()
                        }
                    })
                    .afterJSON(function (json) {
                        salesImages.push(json.image);

                        frisby.create('Create a Sales Event with full information')
                            .addHeader('Cookie', sessionID)
                            .post(SALES_API_ENDPOINT, {
                                Name: '하모니마트',
                                Address: '경기도 용인시 기흥구',
                                Contact: '031-547-7891',
                                Title: '삼겹살 할인 이벤트 1근+1근',
                                EventFrom: '2015-05-05',
                                EventTo: '2015-05-09',
                                Description: '날이면 날마다 오는... 삼겹살이에요. 덴마트산 냉동 돼지고기가 1근+1근',
                                GPS: '35.456,127.01234',
                                Images: salesImages
                            })
                            .expectStatus(200)
                            .expectJSON({
                                Name: '하모니마트',
                                Address: '경기도 용인시 기흥구',
                                Contact: '031-547-7891',
                                Title: '삼겹살 할인 이벤트 1근+1근'
                            })
                            .afterJSON(function (json) {
                                var id = json.id;

                                frisby.create('Get a sales event')
                                    .addHeader('Cookie', sessionID)
                                    .get(SALES_API_ENDPOINT + json.Id)
                                    .expectStatus(200)
                                    .expectJSON({
                                        Id: id,
                                        Name: '하모니마트',
                                        Address: '경기도 용인시 기흥구',
                                        Contact: '031-547-7891',
                                        Title: '삼겹살 할인 이벤트 1근+1근',
                                        EventFrom: '2015-05-05',
                                        EventTo: '2015-05-09',
                                        Description: '날이면 날마다 오는... 삼겹살이에요. 덴마트산 냉동 돼지고기가 1근+1근',
                                        GPS: '35.456,127.01234',
                                        Images: salesImages
                                    })
                                    .toss();

                                frisby.create('Modify \'EventTo\' field')
                                    .addHeader('Cookie', sessionID)
                                    .post(SALES_API_ENDPOINT + json.Id, {
                                        EventTo: '2015-05-19'
                                    })
                                    .expectStatus(200)
                                    .expectJSON({
                                        Name: '하모니마트',
                                        EventTo: '2015-05-19'
                                    })
                                    .toss();

                                frisby.create('Check \'EventTo\' field is changed to 2015-05-19')
                                    .addHeader('Cookie', sessionID)
                                    .get(SALES_API_ENDPOINT + json.Id)
                                    .expectStatus(200)
                                    .expectJSON({
                                        EventTo: '2015-05-19'
                                    })
                                    .toss();

                                frisby.create('Delete a test sales event')
                                    .addHeader('Cookie', sessionID)
                                    .delete(SALES_API_ENDPOINT + json.Id)
                                    .expectStatus(200)
                                    .toss();

                                frisby.create('Check test sales event is deleted')
                                    .addHeader('Cookie', sessionID)
                                    .get(SALES_API_ENDPOINT + json.Id)
                                    .expectStatus(404)
                            })
                            .toss();

                    })
                    .toss();
            })
            .toss();
    })
    .toss();

