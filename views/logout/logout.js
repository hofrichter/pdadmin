(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-17 - initial version
 */
var app = angular.module('logout', ['ngRoute', 'session']);
app.controller('LogoutCtrl', ['$rootScope', '$scope', '$http', 'modalService', '$interval', 'Session',
                     function( $rootScope,   $scope,   $http,   modalService,   $interval,   Session) {
    var self = this;

    $scope.logout = function() {
        var modalCfg = {
            title: 'modal.title.logout',
            body: 'modal.body.logout',
            type: 'danger', //default, primary, success, info, warning, danger
            okButtonLabel: 'button.logout',
            okButtonType: 'btn-danger',
            okButtonGlyphicon: 'glyphicon-logout',
            cancelButtonLabel: 'button.cancel',
            cancelButtonType: 'btn-primary',
            cancelButtonGlyphicon: 'glyphicon-close',
            templateUrl: 'res/ng/modalService.html',
        };

        modalService.open(modalCfg).then(function(data) {
            $http({'method':'get', 'url':'logout'})
            .success(function() {
                Session.disable();
                $rootScope.$broadcast('event:auth-loginRequired');
            });
        });
    };
    return self;
}]);
})();
