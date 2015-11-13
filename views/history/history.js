(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('history', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/history', {
            templateUrl: 'views/history/history.html',
            controller: 'HistoryCtrl',
            resolve: {
                data: function(HistoryDao) {
                    return HistoryDao.load();
                }
            }
        });
}])
.factory('HistoryDao', ['$http', function($http) {
    var dao = {};
    dao.data = [];
    dao.load = function() {
        var promise = $http({'method':'get', 'url':'history'});
        promise.success(function (data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    };
    dao.restore = function(data) {
        var method = typeof(data.id) === 'undefined' ? 'post' : 'put';
        var promise = $http({'method':method, 'url':'history', 'data':data });
        promise.success(function(data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    }
    dao.delete = function(data) {
        var promise = $http({'method':'delete', 'url':'history', 'data':data });
        promise.success(function(data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    }
    return dao;
}])
.controller('HistoryCtrl', ['$scope', '$http', '$filter', 'modalService', 'HistoryDao',
                    function($scope,   $http,   $filter,   modalService,   HistoryDao) {
    $scope.data = HistoryDao.data;
    var backup = {};
    var modalInstance = undefined;
    $scope.format = function(row) {
        //                   $1        $2    $3     $4    $5    $6     $7
        var timestamp = row.replace(/^(\d\d\d\d)(\d\d)(\d\d)_(\d\d)(\d\d)(\d\d)(.*)$/, '$3.$2.$1 - $4.$5');
        return timestamp + ' ' + $filter('i18n')('history.oclock');
    }
    $scope.statusText = function(row) {
        var status = $scope.status(row);
        if (status == 'ok') {
            status = $filter('i18n')('history.status.text.ok');
        } else if (status == 'pw_only') {
            status = $filter('i18n')('history.status.text.pw_only');
        } else if (status == '') {
            status = $filter('i18n')('history.status.text.failed');
        } else {
            status = $filter('i18n')('history.status.text.unknown');
        }
        // '-'.length == 1
        if (status.length > 1) {
            return '(' + status + ')'; 
        }
        return '';
    }
    $scope.log = function(row) {
        var promise = $http({'method':'get', 'url':'history', 'params':{'timestamp':row}});
        promise.success(function (data, status, headers, config) {
            if (!data) {
                data = $filter('i18n')('history.log.not-available');
            }
            var modalCfg = {
                title: 'modal.title.history',
                body: data,
                timestamp: row,
                type: 'success',
                cancelButton: false,
                templateUrl: 'views/history/history-logs-dlg.html',
                windowClass: 'log-dialog'
            };
            console.log(modalCfg.windowClass);
            modalService.open(modalCfg);
        });
        return promise;
    }
    $scope.status = function(row) {
        var match = row.match(/^[_\d]+([\_a-z]+)$/);
        return match ? match[1] : '';
    }
    $scope.restore = function(row) {
        delete row.editModeEnabled;
        delete row.isNew;
        var modalCfg = {
            title: 'modal.title.history',
            body: 'modal.body.history',
            type: 'warning',
            okButtonLabel: 'button.ok',
            okButtonType: 'btn-warning',
            okButtonGlyphicon: ' glyphicon-ok',
            cancelButtonLabel: 'button.cancel'
        };
        modalService.open(modalCfg, {'timestamp':row}).then(function(data) {
            HistoryDao.restore(data).then(function(data) {
                console.log('restore data: ', data.timestamp)
                $scope.data = data.data;
            });
        })
    }
    $scope.delete = function(row) {
        delete row.editModeEnabled;
        delete row.isNew;
        modalService.open({}, {'timestamp':row}).then(function(data) {
            HistoryDao.delete(data).then(function(data) {
                console.log('remove data: ', data)
                $scope.data = data.data;
            });
        })
    }
}])

})();
