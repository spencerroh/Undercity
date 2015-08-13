/*jslint browser: true*/
/*global angular*/
angular.module('undercity')
    .controller('StoresCtrl', function ($scope, imageService, $q, IMAGE_ENDPOINT, storeService) {
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

        $scope.removeInputs = function () {
            $scope.item = {};
            $scope.itemImages.length = 0;
        };

        $scope.submitInputs = function () {
            if ($scope.itemImages !== undefined) {
                var $imageKey = [];
                var $imagePromise = [];
                $scope.itemImages.forEach(function (data) {
                    $imagePromise.push(
                        imageService.create(data).success(function (data) {
                            $imageKey.push(data.image);
                        })
                    );
                });

                $q.all($imagePromise).then(function () {
                    $scope.item.Images = $imageKey;

                    service.create($scope.item, function () {
                        refreshItems();
                    });
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
    });
