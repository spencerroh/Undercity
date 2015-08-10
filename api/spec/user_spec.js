var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');
var testUtils = require('./testUtils');

var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';

frisby.create('New User')
    .post(USER_API_ENDPOINT, testUtils.generateUserInfo(), {json: true})
    .expectStatus(200)
    .expectJSON({
        status: 'success'
    })
    .after(function (err, res, body) {
        frisby.create('Is Login?')
            .addHeader('X-Device-Id', 'TEST_DEVICE_UUID')
            .get(USER_API_ENDPOINT)
            .expectStatus(200)
            .after(function (err, res, body) {
                frisby.create('Logout')
                    .delete(USER_API_ENDPOINT)
                    .expectStatus(200)
                    .after( function () {
                    })
                    .toss();
            })
            .toss();
    })
    .toss();