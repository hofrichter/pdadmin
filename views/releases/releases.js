(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('releases', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/releases', {
            redirectTo: '/tests'
        });
}])
})();
