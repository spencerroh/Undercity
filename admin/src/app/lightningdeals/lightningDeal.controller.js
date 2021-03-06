/**
 * Created by 영태 on 2015-08-13.
 */

angular.module('undercity')
    .controller('LightningDealCtrl', function ($scope, $q, imageService, IMAGE_ENDPOINT, lightningDealService, lightningDealReplyService, lightningDealImageService) {
        'use strict';

        var service = lightningDealService;
        $scope.item = {
            Title: '이것은 샘플입니다',
            NickName: '관리자',
            EndDate: new Date(Date.now()),
            Description: '날마다 오는 딜이 아닙니다'
        };
        $scope.reply = {};
        $scope.items = [];

        function refreshItems() {
            service.readAll({
                id: -1,
                count: -1
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

        $scope.submitReply = function (id) {
            if ('Id' in $scope.reply) {
                lightningDealReplyService.modify({
                    id: $scope.reply.Id
                }, $scope.reply, function () {
                    refreshItems();
                });
            } else {
                lightningDealReplyService.create({
                    id: id
                }, $scope.reply, function () {
                    refreshItems();
                });
            }

        };

        $scope.removeReply = function (id) {
            lightningDealReplyService.delete({
                id: id
            }, function () {
                refreshItems();
            });
        };

        $scope.setReply = function (reply) {
            $scope.reply = reply;
        };

        $scope.clearReply = function () {
            $scope.reply = {};
        };

        $scope.deleteImage = function ($pid, $iid) {
            lightningDealImageService.delete({
                pid: $pid,
                iid: $iid
            });
        };
        /*
        $scope.addImage = function ($pid, $iid) {
            console.log($pid, $iid);
            lightningDealImageService.add({
                pid: $pid,
                iid: $iid
            });
        };*/
    });