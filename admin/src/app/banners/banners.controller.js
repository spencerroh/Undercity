/*jslint browser: true*/
/*global angular*/
angular.module('undercity')
    .controller('BannersCtrl', function ($scope, bannerService, imageService, IMAGE_ENDPOINT) {
        'use strict';

        function refreshBanners() {
            $scope.banners = [];
            bannerService.readAll({}, function (data) {
                data.forEach(function (currentValue) {
                    var url = '';
                    if (currentValue.ContactType === 1) {
                        url = 'tel:';
                    }

                    url += currentValue.Contact;
                    var img = IMAGE_ENDPOINT + currentValue.ImageId;

                    $scope.banners.push({
                        'url': url,
                        'img': img
                    });
                });

            });
        }

        $scope.banners = [];
        $scope.banner = {};
        $scope.onSubmit = function () {
            if ($scope.banner.addr !== undefined &&
                $scope.banner.linkType !== undefined &&
                $scope.files !== undefined) {
                imageService
                    .create($scope.files)
                    .success(function (data) {
                        bannerService.create({
                            'Contact': $scope.banner.addr,
                            'ContactType': $scope.banner.linkType,
                            'ImageId': data.image
                        }, function () {
                            refreshBanners();
                        });
                    });
            }
        };

        refreshBanners();
    });
