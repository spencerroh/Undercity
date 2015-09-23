angular.module('undercity')

    .factory('certService', ['$q', '$http', 'SERVICE_ENDPOINT', function ($q, $http, SERVICE_ENDPOINT) {
        'use strict';
        var deferred = $q.defer();
        
        $http.get(SERVICE_ENDPOINT + '/cert').then(function (data) {
            deferred.resolve(data);
        }, function (error) {
            deferred.reject(error);
        });
        
        return deferred.promise;
    }]);