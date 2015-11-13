(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-17 - initial version
 */
var app = angular.module('login', ['ngRoute', 'session']);
app.controller('LoginCtrl', ['$rootScope', '$scope', '$http', '$location', '$sessionStorage', '$modalStack', '$interval', 'Session',
                    function( $rootScope,   $scope,   $http,   $location,   $sessionStorage,   $modalStack,   $interval,   Session) {

    var self = this;
    $scope.apply = function() {
        $rootScope.isAdmin = false;

        var shaObj = new jsSHA("SHA-512", "TEXT");
        shaObj.update($scope.password); // == doveadm pw -s SHA512 -p <password>
        var password = $scope.username + ':{SHA512}' + shaObj.getHash("B64");

        $http({method:'post', url:'login', data:{username:$scope.username, password:password}})
        .success(function(responseData, responseStatus) {
            if (responseStatus === 200 && responseData && responseData.username) {
                Session.enable(responseData);
                $rootScope.isAdmin = $sessionStorage.getItem('isAdmin');
                if (typeof($rootScope.isAdmin) == 'string') {
                    $rootScope.isAdmin = $rootScope.isAdmin.toLowerCase() == 'true';
                }
                $modalStack.dismissAll();
                if (typeof(responseData.isAdmin) == 'undefined' || !responseData.isAdmin) {
                    $location.path('/passwords');
                } else {
                    $rootScope.isAdmin = true;
                    if ($location.path() == '/passwords') {
                        $location.path('/home');
                    } else if ($location.path() == '' || $location.path() == '/') {
                        $location.path('/home');
                    }
                }
            }
        });
    }
    return self;
}]);
})();
