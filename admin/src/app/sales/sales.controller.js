/**
 * Created by 영태 on 2015-08-12.
 */
/*global angular*/
angular.module('undercity')
    .controller('SalesCtrl', function ($scope, imageService, $q, saleService, IMAGE_ENDPOINT, bookmarkSaleService) {
        'use strict';
        $scope.sale = {
            Name: '하모니마트',
            Address: '경기도 용인시 기흥구',
            Contact: '031-547-7891',
            Title: '삼겹살 할인 이벤트 1근+1근',
            EventFrom: new Date('2015-08-13'),
            EventTo: new Date('2015-08-15'),
            Description: '날이면 날마다 오는... 삼겹살이에요. 덴마트산 냉동 돼지고기가 1근+1근',
            GPS: '35.456,127.01234'
        };

        function refreshSaleEvents() {
            saleService.readAll({
                id: -1, count: -1
            }, function (data) {
                $scope.sales = data;
            });
        }

        $scope.removeCurrentSale = function () {
            $scope.sale = {};
            $scope.saleImages.length = 0;
        };

        $scope.submitCurrentSale = function () {
            var gps = $scope.sale.GPS.split(',');
            $scope.sale.Latitude = gps[0].trim();
            $scope.sale.Longitude = gps[1].trim();
            
            if ($scope.saleImages !== undefined) {
                var $imageKey = [];
                var $imagePromise = [];

                $scope.saleImages.forEach(function (data) {
                    $imagePromise.push(
                        imageService.create(data)
                    );
                });

                $q.all($imagePromise).then(function (data) {
                    data.forEach(function (obj) {
                        $imageKey.push(obj.data.image);
                    });
                    $scope.sale.Images = $imageKey;

                    saleService.create($scope.sale, function () {
                        refreshSaleEvents();
                        $scope.removeCurrentSale();
                    });
                });
            } else {
                
                saleService.create($scope.sale, function () {
                    refreshSaleEvents();
                    $scope.removeCurrentSale();
                });
            }
        };

        $scope.removeSaleEvent = function (saleId) {
            saleService.delete({
                id: saleId
            }, function () {
                refreshSaleEvents();
            });
        };

        $scope.getImageURL = function (imageId) {
            return IMAGE_ENDPOINT + imageId;
        };

        refreshSaleEvents();

        $scope.bookmarkItem = function (id) {
            bookmarkSaleService.create({
                id: id
            }, {}, function () {
                refreshSaleEvents();
            });
        };
    });