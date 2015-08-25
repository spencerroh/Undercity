/*jslint browser: true*/
/*global angular*/
angular.module('undercity')
    .controller('IntroShopsCtrl', function ($scope, $q, imageService, introShopService, introShopReplyService, IMAGE_ENDPOINT) {
        'use strict';
        $scope.intro = {};
        $scope.reply = {};
        $scope.imageSrc = IMAGE_ENDPOINT;

        function refreshIntroShop() {
            introShopService.readAll({
                id: -1, count: -1
            }, function (data) {
                $scope.introShops = data;
            });
        }

        $scope.removeCurrentIntro = function () {
            $scope.intro = {};
            $scope.introImages.length = 0;
        };

        $scope.submitCurrentIntro = function () {
            if ($scope.introImages !== undefined) {
                var $imageKey = [];
                var $imagePromise = [];
                $scope.introImages.forEach(function (data) {
                    $imagePromise.push(
                        imageService.create(data).success(function (data) {
                            $imageKey.push(data.image);
                        })
                    );
                });

                $q.all($imagePromise).then(function () {
                    $scope.intro.Images = $imageKey;

                    introShopService.create($scope.intro, function () {
                        refreshIntroShop();
                    });
                });
            }
        };

        refreshIntroShop();

        $scope.removeItem = function (id) {
            introShopService.delete({
                id: id
            }, function () {
                refreshIntroShop();
            });
        };

        $scope.getImageURL = function (imageId) {
            return IMAGE_ENDPOINT + imageId;
        };

        $scope.submitReply = function (id) {
            if ('Id' in $scope.reply) {
                introShopReplyService.modify({
                    id: $scope.reply.Id
                }, $scope.reply, function () {
                    refreshIntroShop();
                });
            } else {
                introShopReplyService.create({
                    id: id
                }, $scope.reply, function () {
                    refreshIntroShop();
                });
            }

        };

        $scope.removeReply = function (id) {
            introShopReplyService.delete({
                id: id
            }, function () {
                refreshIntroShop();
            })
        };

        $scope.setReply = function (reply) {
            $scope.reply = reply;
        };

        $scope.clearReply = function () {
            $scope.reply = {};
        };
    });