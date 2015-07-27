/**
 * Created by 영태 on 2015-07-27.
 */
var frisby = require('frisby');
var process = require('process');
var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var encoding = require('encoding');
var NodeRSA = require('node-rsa');

var USER_API_ENDPOINT = process.env.API_ENDPOINT + 'user/';

var publicKeyPEM =
    "-----BEGIN PUBLIC KEY-----" + "\n" +
    "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtaZXTFziKX/5EFUjiKdz" + "\n" +
    "6CIoT04McDOOYKbzn6V+HhNiBVTVxX/R2A7nlPWpUzCORihxJ3/gVrekpwGBECbq" + "\n" +
    "Rij1YrktS2AgrYuNGB2oxkEMmXaQP2FhJVeRm0rZJcc8xI44nEcqhovHV6CfoaSZ" + "\n" +
    "Ys8nqqYvpk2j7smGIOiclYnLcfsVRvdJFoySdlvfLjMEyC+vqhZKphWeSRuYAyiK" + "\n" +
    "XvTI44bk75LYYIfFyvdS6qvVsFvjv5ZDcFnVoqcJ+hj32eXGlYIXs3re15iaaY3R" + "\n" +
    "r8wqyZs3+4JN+EX1RXohrQFm7d/WbcMu1/LmNM7YMpTyRUga4ZAF7eBaW+9IX6gC" + "\n" +
    "0QIDAQAB" + "\n" +
    "-----END PUBLIC KEY-----" + "\n";

var plainText = "Long Live the King!";

var publicKey = new NodeRSA(publicKeyPEM);
var cipherText = publicKey.encrypt(plainText, 'base64');

frisby.create('Test RSA')
    .post(USER_API_ENDPOINT + 'login', {
        Cipher: cipherText
    })
    .expectStatus(200)
    .expectJSON({
        Plain: plainText
    })
    .toss();