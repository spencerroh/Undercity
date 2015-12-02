angular.module('undercity')

.factory('lightningDealService', ['$resource', 'SERVICE_ENDPOINT', function ($resource, SERVICE_ENDPOINT) {
        'use strict';
        return $resource(SERVICE_ENDPOINT + '/deal/:id/:count', {}, {
            create: {
                method: 'POST'
            },
            readAll: {
                method: 'GET',
                isArray: true
            },
            read: {
                method: 'GET'
            },
            modify: {
                method: 'POST'
            },
            delete: {
                method: 'DELETE'
            }
        });
    }])
    .factory('lightningDealImageService', ['$resource', 'SERVICE_ENDPOINT', function ($resource, SERVICE_ENDPOINT) {
        'use strict';
        return $resource(SERVICE_ENDPOINT + '/deal/image/:pid/:iid', {}, {
            add: {
                method: 'GET'
            },
            delete: {
                method: 'DELETE'
            }
        });
    }]);