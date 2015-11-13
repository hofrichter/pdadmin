(function() {
  'use strict';

angular.module('http-status-interceptor', [])
    .config(['$httpProvider', function($httpProvider) {
        $httpProvider.interceptors.push(['$rootScope', '$timeout', function($rootScope, $timeout) {
            var hideStatus = function(delay, status, httpMethod, httpUrl) {
                $rootScope.httpstatus = {
                    status: status,
                    method: (httpMethod ? httpMethod.toLowerCase() : 'get'),
                    url:    (httpUrl ? httpUrl : '')
                };
                $timeout(function() {
                    angular.element(document).find('body').removeClass('request response success error');
                }, delay);
            }
            return {
                'request': function(config) {
                    var target = angular.element(document).find('body').removeClass('request response success error').addClass('request');
                    hideStatus(1000, 'request', config.method, config.url);
                    return config;
                },
               'requestError': function(rejection) {
                    var target = angular.element(document).find('body').removeClass('request response success error').addClass('request error');
                    hideStatus(10000, 'requestError', rejection.config.method, rejection.config.url);
                    return rejection;
                },
                'response': function(response) {
                    var target = angular.element(document).find('body').removeClass('request response success error').addClass('response success');
                    hideStatus(1000, 'response', response.config.method, response.config.url);
                    return response;
                },
               'responseError': function(rejection) {
                    var target = angular.element(document).find('body').removeClass('request response success error').addClass('response error');
                    hideStatus(10000, 'responseError', rejection.config.method, rejection.config.url);
                    return rejection;
                }
            };
        }]);
    }])
})();
