/*jslint browser: true*/
/*global angular*/
angular.module('undercity')
    .controller('StoresCtrl', function ($scope, imageService, $q, IMAGE_ENDPOINT, storeService, bookmarkStoreService, productTypeService) {
        'use strict';

        var service = storeService;
        $scope.item = {
            'Name': '하모니마트',
            'Address': '경기도 용인시 기흥구 구갈동 강남마을 4단지 상가',
            'Contact': '031-254-7852',
            'Product': '잡화',
            'Description': '우리 으여니형님이 운영하는 마트임메다',
            'GPS': '37.548, 127.5'
        };
        $scope.items = [];

        function refreshItems() {
            service.readAll({
                id: -1, count: -1
            }, function (data) {
                $scope.items = data;
            });
        }
        refreshItems();

        //$scope.productType = [];
        function getProductType() {
            productTypeService.read(function (data) {
                $scope.productType = data;
            })
        }
        getProductType();



        $scope.removeInputs = function () {
            $scope.item = {};
            $scope.itemImages.length = 0;
        };

        $scope.submitInputs = function () {
            var gps = $scope.item.GPS.split(',');
            $scope.item.Latitude = gps[0].trim();
            $scope.item.Longitude = gps[1].trim();
            
            if ($scope.itemImages !== undefined) {
                var $imageKey = [];
                var $imagePromise = [];
                $scope.itemImages.forEach(function (data) {
                    $imagePromise.push(
                        imageService.create(data)
                    );
                });

                $q.all($imagePromise).then(function (data) {
                    data.forEach(function (obj) {
                        $imageKey.push(obj.data.image);
                    });
                    $scope.item.Images = $imageKey;

                    service.create($scope.item, function () {
                        refreshItems();
                        $scope.removeInputs();
                    });
                });
            } else {
                service.create($scope.item, function () {
                    refreshItems();
                    $scope.removeInputs();
                });
            }
        };

        $scope.removeItem = function (id) {
            service.delete({
                id: id
            }, function () {
                refreshItems();
            });
        };

        $scope.getImageURL = function (imageId) {
            return IMAGE_ENDPOINT + imageId;
        };

        $scope.bookmarkItem = function (id) {
            bookmarkStoreService.create({
                id: id
            }, {}, function () {
                refreshItems();
            });
        };
    });
