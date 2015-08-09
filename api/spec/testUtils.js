/**
 * Created by ¿µÅÂ on 2015-08-09.
 */
var NodeRSA = require('node-rsa');

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

var publicKey = new NodeRSA(publicKeyPEM);

var userInfo = {
    DeviceUUID: 'TEST_DEVICE_UUID',
    DeviceToken: 'TEST_DEVICE_TOKEN',
    DeviceOS: 'ANDROID'
};

module.exports = {
    generateUserInfo: function () {
        return (new NodeRSA(publicKeyPEM)).encrypt(userInfo, 'base64');
    }
};