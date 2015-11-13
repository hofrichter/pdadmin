(function() {
'use strict';

/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
var app = angular.module('session', ['login'])
.factory('Session', ['$modal', '$modalStack', '$cookies', '$http', '$timeout', '$sessionStorage', 'authService',
            function ($modal,   $modalStack,   $cookies,   $http,   $timeout,   $sessionStorage,   authService) {
    var session = {};
    session.data = undefined;
    session.create = function(deffered) {
        $modalStack.dismissAll();
        var modalInstance = $modal.open({
            type: 'type-success',
            backdrop: 'static',
            keyboard: false,
            controller:'LoginCtrl',
            templateUrl:'views/login/login-dlg.html'
        });
        modalInstance.result.then(function (selectedItem) {
            //console.info('Modal closed at: ' + new Date());
        }, function () {
            //console.info('Modal dismissed at: ' + new Date());
        });
    }
    session.isValid = function() {
        return typeof(session.data) !== 'undefined';
    };
    session.enable = function(sessionData) {
        session.data = sessionData;
        //console.log('saving session into cookie');
        $http.defaults.headers.common[sessionData.session_name] = sessionData.session_id;
        $sessionStorage.setItem('session_name', sessionData.session_name);
        $sessionStorage.setItem('session_id', sessionData.session_id);
        $sessionStorage.setItem('username', sessionData.username);
        $sessionStorage.setItem('isAdmin', typeof(sessionData.isAdmin) != 'undefined');
        authService.loginConfirmed();
    };
    session.disable = function() {
        if (typeof(sessionData) !== 'undefined' && typeof(sessionData.session_name) !== 'undefined') {
            delete ($http.defaults.headers.common[sessionData.session_name]);
            $sessionStorage.clear();
        }
    };
    return session;

}]);
})();
