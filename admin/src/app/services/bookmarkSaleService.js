angular.module('undercity')

    .factory('bookmarkSaleService', ['$resource', 'SERVICE_ENDPOINT', function ($resource, SERVICE_ENDPOINT) {
        'use strict';
        return $resource(SERVICE_ENDPOINT + '/bookmark/sale/:id', {}, {
            create: {
                method: 'POST'
            },
            delete: {
                method: 'DELETE'
            }
        });
    }]);