angular.module('undercity')

.factory('bannerService', ['$resource', 'SERVICE_ENDPOINT', function ($resource, SERVICE_ENDPOINT) {
    'use strict';
    return $resource(SERVICE_ENDPOINT + '/banners/:id', {}, {
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
    }]);
