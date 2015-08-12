/**
 * Created by 영태 on 2015-08-12.
 */
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