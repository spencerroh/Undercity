/**
 * Created by 영태 on 2015-07-27.
 */
var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');
var testUtils = require('./testUtils')

var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';

frisby.create('New User')
    .post(USER_API_ENDPOINT, {
        UserInfo: testUtils.generateUserInfo()
    })
    .expectStatus(200)
    .expectJSON({
        status: 'success'
    })
    .after(function (err, res, body) {
        var sessionID = res.headers['set-cookie'];
        frisby.create('Is Login?')
            .addHeader('Cookie', sessionID)
            .get(USER_API_ENDPOINT)
            .expectStatus(200)
            .after(function (err, res, body) {
                frisby.create('Logout')
                    .addHeader('Cookie',sessionID)
                    .delete(USER_API_ENDPOINT)
                    .expectStatus(200)
                    .after( function () {
                        frisby.create('Is Login?')
                            .addHeader('Cookie',sessionID)
                            .get(USER_API_ENDPOINT)
                            .expectStatus(401)
                            .toss();
                    })
                    .toss();
            })
            .toss();
    })
    .toss();