var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');

var SALES_API_ENDPOINT = process.env.API_ENDPOINT + 'sales/';

var salesEventInfoWithNoInfo = new FormData();

frisby.create('Create a Sales Event with no informations')
    .post(SALES_API_ENDPOINT, salesEventInfoWithNoInfo, {
        json: false,
        headers: {
            'content-type': 'multipart/form-data; boundary=' + salesEventInfoWithNoInfo.getBoundary(),
            'content-length': salesEventInfoWithNoInfo.getLengthSync()
        }
    })
    .expectStatus(400)
    .toss();

var salesEventInfoWithFullInfo = new FormData();
salesEventInfoWithFullInfo.append('Name', '하모니마트');
salesEventInfoWithFullInfo.append('Address', '경기도 용인시 기흥구');
salesEventInfoWithFullInfo.append('Contact', '031-547-7891');
salesEventInfoWithFullInfo.append('Title', '삼겹살 할인 이벤트 1근+1근');
salesEventInfoWithFullInfo.append('EventFrom', '2015-05-05');
salesEventInfoWithFullInfo.append('EventTo', '2015-05-09');
salesEventInfoWithFullInfo.append('Description', '날이면 날마다 오는... 삼겹살이에요. 덴마트산 냉동 돼지고기가 1근+1근');
salesEventInfoWithFullInfo.append('GPS', '35.456,127.01234'); // Valid GPS Format is "[0-9]+,[0-9]+"

frisby.create('Create a Sales Event with full information')
    .post(SALES_API_ENDPOINT, salesEventInfoWithFullInfo, {
        json: false,
        headers: {
            'content-type': 'multipart/form-data; boundary=' + salesEventInfoWithFullInfo.getBoundary(),
            'content-length': salesEventInfoWithFullInfo.getLengthSync()
        }
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
                GPS: '35.456,127.01234'
            })
            .toss();

        var modifyEventTo = new FormData();
        modifyEventTo.append('EventTo', '2015-05-19');

        frisby.create('Modify \'EventTo\' field')
            .post(SALES_API_ENDPOINT + json.Id, modifyEventTo, {
                json: false,
                headers: {
                    'content-type': 'multipart/form-data; boundary=' + modifyEventTo.getBoundary(),
                    'content-length': modifyEventTo.getLengthSync()
                }
            })
            .expectStatus(200)
            .expectJSON({
                Name: '하모니마트',
                EventTo: '2015-05-19'
            })
            .toss();

        frisby.create('Check \'EventTo\' field is changed to 2015-05-19')
            .get(SALES_API_ENDPOINT + json.Id)
            .expectStatus(200)
            .expectJSON({
                EventTo: '2015-05-19'
            })
            .toss();

        frisby.create('Delete a test sales event')
            .delete(SALES_API_ENDPOINT + json.Id)
            .expectStatus(200)
            .toss();

        frisby.create('Check test sales event is deleted')
            .get(SALES_API_ENDPOINT + json.Id)
            .expectStatus(404)
    })
    .toss();
