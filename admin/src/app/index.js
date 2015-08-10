'use strict';

angular.module('undercity', ['ngAnimate', 'ngCookies', 'ngTouch', 'ngSanitize', 'ngResource', 'ui.router', 'ui.bootstrap', 'ngFileUpload'])
    //    .constant('SERVICE_ENDPOINT', 'http://localhost/undercity/api')
    //    .constant('IMAGE_ENDPOINT', 'http://localhost/undercity/api/images/')
    .constant('SERVICE_ENDPOINT', 'http://222.122.143.163/undercity/api')
    .constant('IMAGE_ENDPOINT', 'http://222.122.143.163/undercity/api/images/');
.config(function ($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state('home', {
                url: '/',
                templateUrl: 'app/main/main.html',
                controller: 'MainCtrl'
            })
            .state('home.banners', {
                url: 'banners',
                templateUrl: 'app/banners/banners.html',
                controller: 'BannersCtrl'
            })
            .state('home.users', {
                url: 'users',
                templateUrl: 'app/users/users.html',
                controller: 'UsersCtrl'
            })
            .state('home.stores', {
                url: 'stores',
                templateUrl: 'app/stores/stores.html',
                controller: 'StoresCtrl'
            });

        $urlRouterProvider.otherwise('/');
    })
    .run(function ($http) {
        $http.defaults.headers.common['X-Device-Id'] = 'ADMIN_DEVICE_UUID';
    });
