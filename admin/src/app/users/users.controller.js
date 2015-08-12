/*jslint browser: true*/
/*global angular*/
angular.module('undercity')
    .controller('UsersCtrl', function ($scope, authenticationService) {
        'use strict';

        $scope.isCollapsed = false;
        $scope.user = {};
        $scope.toggleCollapse = function () {
            $scope.isCollapsed = !$scope.isCollapsed;
        };

        $scope.setTestDevice = function () {
            $scope.user = {
                'DeviceUUID': 'TEST_DEVICE_UUID',
                'DeviceToken': 'TEST_DEVICE_TOKEN',
                'DeviceOS': 'TEST_DEVICE_OS'
            };
        };

        $scope.setAdminDevice = function () {
            $scope.user = {
                'DeviceUUID': 'ADMIN_DEVICE_UUID',
                'DeviceToken': 'ADMIN_DEVICE_TOKEN',
                'DeviceOS': 'ADMIN_DEVICE_OS'
            };
        };

        $scope.removeCurrentUser = function () {
            $scope.user = {};
        };

        $scope.submitCurrentUser = function () {
            authenticationService.login($scope.user);
        };
    });
