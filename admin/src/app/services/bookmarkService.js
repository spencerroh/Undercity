angular.module('undercity')

    .factory('bookmarkService', ['$resource', 'SERVICE_ENDPOINT', function ($resource, SERVICE_ENDPOINT) {
        'use strict';
        return $resource(SERVICE_ENDPOINT + '/bookmark/', {}, {
            readAll: {
                method: 'GET'
            }
        });
    }]);