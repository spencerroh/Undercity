/*jslint browser: true*/
/*global angular*/
angular.module('undercity')
    .controller('BookmarksCtrl', function ($scope, bookmarkService, bookmarkStoreService, bookmarkSaleService) {
        'use strict';
        $scope.bookmark = {};

        function refresh() {
            bookmarkService.readAll(function (data) {
                $scope.bookmark = data;
            });
        }


        $scope.removeShop = function (id) {
            bookmarkStoreService.delete({
                id: id  // 여기에 들어오는 id는 bookmark 테이블의 id가 아니라 상점의 id임.
            }, {}, function () {
                refresh();
            });
        };

        $scope.removeSale = function (id) {
            bookmarkSaleService.delete({
                id: id // 여기에 들어오는 id는 bookmark 테이블의 id가 아니라 할인이벤트의 id임.
            }, {}, function () {
                refresh();
            });
        };

        refresh();
    });