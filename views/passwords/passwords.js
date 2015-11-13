(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('passwords', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/passwords', {
            //redirectTo: '/accounts'
            templateUrl: 'views/passwords/passwords.html',
            controller: 'PasswordsCtrl'
        });
}])
.factory('PasswordsDao', ['$http', function($http) {
    var dao = {};
    dao.save = function(data) {
        return $http({'method':'put', 'url':'passwords', 'data':data });
    };
    return dao;
}])
.controller('PasswordsCtrl', ['$rootScope', '$scope', '$sessionStorage', '$location', '$filter', 'modalService', '$modalStack', 'PasswordsDao',
                      function($rootScope,   $scope,   $sessionStorage,   $location,   $filter,   modalService,   $modalStack,   PasswordsDao) {
    $scope.pw = {'old':'', 'new1':'','new2':''};
    $scope.username = $sessionStorage.getItem('username');

    var isAdmin = $sessionStorage.getItem('isAdmin');
    if ($rootScope.isAdmin) {
        $location.path('/home');
    }

    var buildPassword = function (username, password) {
        var shaObj = new jsSHA("SHA-512", "TEXT");
        shaObj.update(password);
        return username + ':{SHA512}' + shaObj.getHash("B64");
    }

    $scope.isValid = function (property) {
        return $scope.pw.old != $scope.pw.new1 && $scope.pw.new1 == $scope.pw.new2
           && (""+$scope.pw.old).length > 0 && (""+$scope.pw.new1).length > 0;
    }

    $scope.apply = function() {
        if (!$scope.isValid()) {
            return;
        }
        var op = buildPassword($scope.username, $scope.pw.old);
        var np = buildPassword($scope.username, $scope.pw.new1);
        var data = {
            'oldaccount': op,  
            'password': np
        }
        var showModal = function(errorType) {
            var modalCfg = {
                title: 'passwords.modal.title.' + errorType,
                body: $filter('i18n')('passwords.modal.body.' + errorType, DEPLOY_INTERVAL),
                type: errorType, //default, primary, success, info, warning, danger
                okButtonLabel: 'button.ok',
                okButtonType: 'btn-' + errorType,
                okButtonGlyphicon: 'glyphicon-ok',
                cancelButton: false
            };
            modalService.open(modalCfg);
        }

        PasswordsDao.save(data).success(function(data) {
            if (data == true || data == 'true') {
                $scope.pw.old = '';
                $scope.pw.new1 = '';
                $scope.pw.new2 = '';
                showModal('success');
            } else {
                showModal('danger');
            }
        }).error(function() {
            showModal('danger');
        });
        //console.log('save(data):', data);
    }
    $scope.ok = function() {
        $modalStack.dismissAll(reason);
    }
}])

})();
