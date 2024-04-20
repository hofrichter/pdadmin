(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('addresses', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/addresses', {
            templateUrl: 'views/addresses/addresses.html',
            controller: 'AddressesCtrl',
            resolve: {
                data: function(AddressesDao) {
                    return AddressesDao.load();
                },
                accounts: function(AccountsDao) {
                    return AccountsDao.load();
                }
            }
        });
}])
.factory('AddressesDao', ['$http', '$q', function($http, $q) {
    var dao = {};
    dao.data = [];
    dao.load = function() {
        var promise = $http({'method':'get', 'url':'addresses'});
        promise.success(function (data, status, headers, config) {
            if (data.length > 0 && (typeof(data[0].account) === 'undefined' || typeof(data[0].emailpattern) === 'undefined')) {
                data = [];
            }
            dao.data = data || [];
            return data;
        });
        return promise;
    };
    dao.save = function(data) {
        var method = typeof(data.id) === 'undefined' ? 'post' : 'put';
        var promisses = [];
        data.email = data.emailpattern;
        promisses.push($http({'method':method, 'url':'addresses', 'data':data }));
        promisses.push($http({'method':method, 'url':'tests',     'data':data }));
        var promiseAll = $q.all(promisses);
        promiseAll.then(function(data) {
            if (typeof(data) !== 'undefined' && data.length > 1) {
                dao.data = data[0].data || [];
                return dao.data;
            }
            return [];
        }, function (error) {
            throw error;
        });
        return promiseAll;
    };
    dao.delete = function(data) {
        var promise = $http({'method':'delete', 'url':'addresses', 'data':data });
        promise.success(function(data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    }
    return dao;
}])
.controller('AddressesCtrl', ['$scope','$filter','modalService','AddressesDao','AccountsDao'
                    , function($scope,  $filter,  modalService,  AddressesDao,  AccountsDao) {
 
    $scope.data = AddressesDao.data;
    $scope.accounts = [];

    for (var i = 0; i < AccountsDao.data.length; i++) {
        $scope.accounts.push(AccountsDao.data[i].email);
    }
    var nothingSelected = {'account' : $filter('i18n')('select.chooseplease')};

    var backup = {};
    $scope.create = function() {
        for (var i in $scope.data) {
            delete $scope.data[i].editModeEnabled;
        }
        var row = {'isNew':true,'editModeEnabled':true,'emailpattern':'','account':nothingSelected};
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
                if (row.account != backup.account) {
                    row.oldaccount = backup.account;
                }
                if (row.account == nothingSelected) {
                    row.oldaccount = backup.account;
                }
                AddressesDao.save(row).then(function(data) {
                    $scope.data = AddressesDao.data;
                });
                console.log('save(row):', row);
                return;
            }
        }
        backup = {};
        console.log('save(row) canceled. There were no changes.');
    };
    $scope.cancel = function(row) {
        delete row.editModeEnabled;
        if (typeof(row.isNew) !== 'undefined' && row.isNew) {
            $scope.data.pop();
            console.log('adding a new row was cancled.');
        } else {
            angular.copy(backup, row);
            console.log('cancel(row):', row);
        }
    };
    $scope.delete = function(row) {
        delete row.editModeEnabled;
        delete row.isNew;
        modalService.open({}, row).then(function(data) {
            AddressesDao.delete(data).then(function(data) {
                $scope.data = data.data;
            });
            console.log('remove data: ', data)
        });
    };
    $scope.toLabel = function(addresses) {
        addresses = addresses || [];
        var label = addresses.join(', ');
        if (label.length > 23) {
            return label.substring(0, 20) + '...';
        }
        return label;
    }
    $scope.toString = function(account) {
        return account;
    }
    $scope.toModel = function(account) {
        return account;
    }
}])

})();
