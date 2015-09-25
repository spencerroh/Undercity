angular.module('undercity')
    .controller('DemoCtrl', function ($scope, saleService) {
    'use strict';

    $scope.selectedId = -1;
    
    saleService.readAll({
         id: -1, count: -1
    }, function (data) {
        $scope.saleItems = data;
        console.log($scope.selectedId);
    });
    
    $scope.submitInputs = function () {
        
    };
});