(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('home', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/home', {
            templateUrl: 'views/home/home.html'
        });
}])

})();
