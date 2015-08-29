angular.module('undercity')

    .factory('certService', ['$http', 'SERVICE_ENDPOINT', function ($http, SERVICE_ENDPOINT) {
        'use strict';
        return $http.get(SERVICE_ENDPOINT + '/cert');
    }]);