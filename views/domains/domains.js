(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('domains', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/domains', {
            templateUrl: 'views/domains/domains.html',
            controller: 'DomainsCtrl',
            resolve: {
                data: function(DomainsDao) {
                    return DomainsDao.load();
                }
            }
        });
}])
.factory('DomainsDao', ['$http', function($http) {
    var dao = {};
    dao.data = [];
    dao.load = function() {
        var promise = $http({'method':'get', 'url':'domains'});
        promise.success(function (data, status, headers, config) {
            if (data.length > 0 && (typeof(data[0].domain) === 'undefined')) {
                data = [];
            }
            dao.data = data || [];
            return data;
        });
        return promise;
    };
    dao.save = function(data) {
        var method = typeof(data.id) === 'undefined' ? 'post' : 'put';
        var promise = $http({'method':method, 'url':'domains', 'data':data });
        promise.success(function(data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    };
    dao.delete = function(data) {
        var promise = $http({'method':'delete', 'url':'domains', 'data':data });
        promise.success(function(data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    }
    return dao;
}])
.controller('DomainsCtrl', ['$scope', 'modalService', 'DomainsDao', function($scope, modalService, DomainsDao) {
    $scope.data = DomainsDao.data;
    $scope.pw = {raw:'', retyped: ''};
    var backup = {};
    var modalInstance = undefined;
    $scope.create = function() {
        for (var i in $scope.data) {
            delete $scope.data[i].editModeEnabled;
        }
        var row = {'isNew':true,'editModeEnabled':true,'domain':'','state':'OK'};
        $scope.data.push(row);
        angular.copy(row, backup);
        $scope.data[$scope.data.length-1].readonly = false;
    };
    $scope.edit = function(row) {
        backup = {}
        angular.copy(row, backup);
        for (var i in $scope.data) {
            delete $scope.data[i].editModeEnabled;
        }
        row.editModeEnabled = true;
    };

    $scope.save = function(row) {
        delete row.editModeEnabled;
        delete row.isNew;
        for (var key in row) {
            if (row[key] != backup[key]) {
                row['state'] = 'OK';
                DomainsDao.save(row).then(function(data) {
                    $scope.data = data.data;
                });
                break;
            }
        }
        backup = {};
        console.log('save(row):', row);
    }
    $scope.cancel = function(row) {
        delete row.editModeEnabled;
        if (typeof(row.isNew) !== 'undefined' && row.isNew) {
            $scope.data.pop();
            console.log('adding a new row was cancled.');
        } else {
            angular.copy(backup, row);
            console.log('cancel(row):', row);
        }
    }
    $scope.delete = function(row) {
        delete row.editModeEnabled;
        delete row.isNew;
        modalService.open({}, row).then(function(data) {
            DomainsDao.delete(data).then(function(data) {
                $scope.data = data.data;
            });
            console.log('remove data: ', data)
        })
    }
}])

})();
