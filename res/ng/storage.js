(function() {
'use strict';

/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
var app = angular.module('storage', [])
.factory('$localStorage', ['$cookies', function($cookies) {
    if (window.localStorage) {
        window.localStorage || function() {
            var self = this;
            var expireDate = new Date();
            expireDate.setDate(expireDate.getDate() + 1);
            self.setItem = function(key, value) {
                $cookies.put(key, value, {'expires': expireDate});
            };
            self.getItem = function(key) {
                return $cookies.get(key);
            }
            return self;
        };
    }
    return window.localStorage;
}])
.factory('$sessionStorage', ['$cookies', function($cookies) {
    if (!window.sessionStorage) {
        window.sessionStorage = function() {
            var self = this;
            var data = {};
            self.setItem = function(key, value) {
                data[key] = value;
            };
            self.getItem = function(key) {
                return data[key];
            }
            self.clear = function() {
                data = {};
            }
            return self;
        };
    }
    return window.sessionStorage;

}])


;
})();
