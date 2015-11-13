(function() {
'use strict';

// initialize the ng-app:
var app = angular.module('app', modules);
//alert(window.location.href);
app.constant('BASE_URL', window.location.href);
app.config(['$routeProvider', function( $routeProvider) {
    $routeProvider.otherwise({redirectTo: '/home'});
}]);
app.run(    ['$rootScope', '$http', '$cookies', '$sessionStorage',
    function ($rootScope,   $http,   $cookies,   $sessionStorage) {

    $rootScope.$on('$stateChangeStart', function(event, toState){
        $rootScope.showMobileNav = false;
    });
    $rootScope.$on('$routeChangeSuccess', function(event, current, previous, rejection){
        var url = current && current.$$route && current.$$route.originalPath ? current.$$route.originalPath : '';
        $rootScope.viewId = url.replace(/[^0-0a-zA-Z]*/, ':').replace(/^:+/, '').replace(/:+$/, '');
    });

    // see what's going on when the route tries to change
    //event.preventDefault();
    $rootScope.$evalAsync(function() {
        var sn = $sessionStorage.getItem('session_name');
        var si = $sessionStorage.getItem('session_id');
        $rootScope.username = $sessionStorage.getItem('username');
        $rootScope.isAdmin = $sessionStorage.getItem('isAdmin');
        if (typeof($rootScope.isAdmin) == 'string') {
            $rootScope.isAdmin = $rootScope.isAdmin.toLowerCase() == 'true';
        }
        if (sn && si) {
            $http.defaults.headers.common[sn] = si;
        }
        $http({url:'init', method:'get'});
    });
}]);
app.controller('NavCtrl', ['$scope', function($scope) {
    $scope.collapse = !$scope.collapse;
    $scope.close = function() {
        $scope.collapse = false;
    }
}]);

})();
