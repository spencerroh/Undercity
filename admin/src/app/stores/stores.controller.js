/*jslint browser: true*/
/*global angular*/
angular.module('undercity')
    .controller('StoresCtrl', function ($scope, imageService, $q, storeService) {
        'use strict';
        $scope.store = {
            'Name': '하모니마트',
            'Address': '경기도 용인시 기흥구 구갈동 강남마을 4단지 상가',
            'Contact': '031-254-7852',
            'Product': '잡화',
            'Description': '우리 으여니형님이 운영하는 마트임메다',
            'GPS': '37.548, 127.5'
        };

        $scope.removeCurrentStore = function () {
            $scope.store = {};
            $scope.storeForm.$setPristine(true);
        }

        $scope.submitCurrentStore = function () {
            console.log($scope.Images);
            if ($scope.Images != undefined) {
                var $imageKey = [];
                var $imagePromise = [];
                $scope.Images.forEach(function (data) {
                    $imagePromise.push(
                        imageService.create(data).success(function (data) {
                            $imageKey.push(data.image);
                        })
                    );
                });
                $q.all($imagePromise).then(function (result) {
                    $scope.store.Images = $imageKey;

                    storeService.create($scope.store, function (data) {
                        refreshStores();
                    });
                });
            }
        }

        function refreshStores() {
            storeService.readAll({
                id: -1, count: -1
            }, function (data) {
                $scope.stores = data;
            });
        }

        $scope.removeStore = function (id) {
            storeService.delete({
                id: id
            }, function (){
                refreshStores();
            })
        }

        refreshStores();
    });
