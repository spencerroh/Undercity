angular.module('undercity')

    .factory('authenticationService', ['$resource', 'SERVICE_ENDPOINT', function ($resource, SERVICE_ENDPOINT) {
        'use strict';
        return $resource(SERVICE_ENDPOINT + '/user', {}, {
            login: {
                method: 'POST'
            },
            isLoggedIn: {
                method: 'GET'
            },
            logout: {
                method: 'DELETE'
            }
        });
    }]);

angular.module('undercity')
    .factory('encryptService', function () {
        'use strict';
        var CryptoJSAesJson = {
            stringify: function (cipherParams) {
                var j = {
                    ct: cipherParams.ciphertext.toString(CryptoJS.enc.Base64)
                };
                if (cipherParams.iv) j.iv = cipherParams.iv.toString();
                if (cipherParams.salt) j.s = cipherParams.salt.toString();
                return JSON.stringify(j);
            },
            parse: function (jsonStr) {
                var j = JSON.parse(jsonStr);
                var cipherParams = CryptoJS.lib.CipherParams.create({
                    ciphertext: CryptoJS.enc.Base64.parse(j.ct)
                });
                if (j.iv) cipherParams.iv = CryptoJS.enc.Hex.parse(j.iv)
                if (j.s) cipherParams.salt = CryptoJS.enc.Hex.parse(j.s)
                return cipherParams;
            }
        }        
        
        return function (publicKey, data) {
            var passphase = CryptoJS.lib.WordArray.random(128/8).toString(CryptoJS.enc.Hex);
            var iv = CryptoJS.lib.WordArray.random(128/8).toString(CryptoJS.enc.Hex);
            
            var encryptedData = CryptoJS.AES.encrypt(JSON.stringify(data), passphase, {
                iv: iv,
                format: CryptoJSAesJson
            }).toString();
            
            var encrypt = new JSEncrypt();
            encrypt.setPublicKey(publicKey);
            var encryptedKey = encrypt.encrypt(passphase);
            
            console.log(passphase);
            console.log(encryptedKey);
            
            return {
                key: encryptedKey,
                data: encryptedData
            };
        };    
    })

    .factory('encService', function ($resource, SERVICE_ENDPOINT) {

        'use strict';
        return $resource(SERVICE_ENDPOINT + '/test/enc', {}, {
            enc: {
                method: 'POST'
            }
        });
    });