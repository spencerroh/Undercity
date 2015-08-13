/**
 * Created by 영태 on 2015-08-13.
 */

angular.module('undercity')
    .controller('LightningDealCtrl', function ($scope, $q, imageService, IMAGE_ENDPOINT, lightningDealService) {
        'use strict';

        var service = lightningDealService;
        $scope.item = {
            Title: '이것은 샘플입니다',
            NickName: '관리자',
            EndDate: new Date('2015-08-15T12:57:00'),
            Description: '날마다 오는 딜이 아닙니다'
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
