/**
 * Created by Spencer Roh on 2015-08-29.
 */
angular.module('undercity').directive('auth', function (authenticationService, $http, certService, authService, encryptService) {
    'use strict';
    return {
        restrict: 'C',
        link: function (scope) {
            scope.$on('event:auth-loginRequired', function () {
                console.log('login required');
                
                delete $http.defaults.headers.common['X-Token'];

                var now = new Date();
                var time = now.getTime();
                var loginData = angular.fromJson({
                    'Now': parseInt(time / 1000),
                    'DeviceUUID': 'ADMIN_DEBUG_DEVICE',
                    'DeviceToken': 'ADMIN_DEBUG_TOKEN',
                    'DeviceOS': 'ADMIN'
                });
                certService.then(function (response) {
                    var encryptedLoginData = encryptService(response.data, loginData);
                    
                    authenticationService.login(encryptedLoginData, 
                        function (response) {
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
