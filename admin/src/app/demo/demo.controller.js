angular.module('undercity')
    .controller('DemoCtrl', function ($scope, saleService, $resource, SERVICE_ENDPOINT) {
    'use strict';

    $scope.selectedId = -1;
    
    saleService.readAll({
         id: -1, count: -1
    }, function (data) {
        $scope.saleItems = data;
    });

    var service = $resource(SERVICE_ENDPOINT + '/test/demo/:sale', {}, {
        send: {
            method: 'GET'
        }
    });
    
    $scope.submitInputs = function () {

        console.log($scope.selectedId);
        service.send({
            sale: $scope.selectedId
        }, function () {

        });
    };
});