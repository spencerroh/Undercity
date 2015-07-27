/**
 * Created by 영태 on 2015-07-26.
 */
var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');

var SALES_API_ENDPOINT = process.env.API_ENDPOINT + 'intro/';
var IMAGES_API_ENDPOINT = process.env.API_ENDPOINT + 'images/';

