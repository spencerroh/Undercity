/**
 * Created by Spencer Roh on 2015-08-29.
 */
angular.module('undercity').directive('auth', function (authenticationService, $http, certService, authService) {
    'use strict';
    return {
        restrict: 'C',
        link: function (scope) {
            scope.$on('event:auth-loginRequired', function () {
                console.log('login required');
                delete $http.defaults.headers.common['X-Token'];

                certService.then(function (response) {
                    var publicKey = response.data;
                    var now = new Date();
                    var time = now.getTime();

                    var loginData = angular.fromJson({
                        'Now': parseInt(time / 1000),
                        'DeviceUUID': 'ADMIN_DEBUG_DEVICE',
                        'DeviceToken': 'ADMIN_DEBUG_TOKEN',
                        'DeviceOS': 'ADMIN'
                    });

                    var encrypt = new JSEncrypt();
                    encrypt.setPublicKey(publicKey);
                    var encrypted = encrypt.encrypt(JSON.stringify(loginData));

                    authenticationService.login({
                        'Login': encrypted
                    }, function (response) {
                        $http.defaults.headers.common['X-Token'] = response.token;
                        authService.loginConfirmed(response.token, function (config) {
                            config.headers['X-Token'] = response.token;
                            return config;
                        });
                        console.log('login success');
                    }, function (error) {
                        console.log('login failed', error);
                        delete $http.defaults.headers.common['X-Token'];
                    });
                });
            });
        }
    };
});
