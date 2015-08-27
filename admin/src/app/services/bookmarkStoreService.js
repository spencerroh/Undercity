angular.module('undercity')

    .factory('bookmarkStoreService', ['$resource', 'SERVICE_ENDPOINT', function ($resource, SERVICE_ENDPOINT) {
        'use strict';
        return $resource(SERVICE_ENDPOINT + '/bookmark/shop/:id', {}, {
            create: {
                method: 'POST'
            },
            delete: {
                method: 'DELETE'
            }
        });
    }]);
