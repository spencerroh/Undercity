angular.module('undercity')
    .controller('ProductCtrl', function ($scope, productTypeService) {
        'use strict';

        $scope.items = {};
        $scope.submit = '올리기';
        $scope.$watch('product', function (nv) {
           if (nv === undefined || !('Id' in nv)) {
               $scope.submit = '올리기';
           } else {
               $scope.submit = '수정하기';
           }
        });

        function refreshItems() {
            productTypeService.read(function (data) {
                $scope.items = data;
            });
        }

        $scope.onSubmit = function () {
            if ('Id' in $scope.product) {
                productTypeService.modify({id:$scope.product.Id}, $scope.product, function () {
                    refreshItems();
                });
            } else {
                productTypeService.create($scope.product, function () {
                    refreshItems();
                });
            }

            $scope.product = {};
        };

        $scope.deleteItem = function (id) {
            productTypeService.delete({id:id}, function () {
                refreshItems();
            });
        };

        $scope.onCancel = function () {
            $scope.product = {};
        };

        $scope.modifyItem = function (item) {
            $scope.product = item;
        };

        refreshItems();
    });

