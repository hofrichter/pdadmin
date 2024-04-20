(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('accounts', ['ngRoute', 'accounts', 'domains'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/accounts', {
            templateUrl: 'views/accounts/accounts.html',
            controller: 'AccountsCtrl',
            resolve: {
                data: function(AccountsDao) {
                    return AccountsDao.load();
                },
                domains: function(DomainsDao) {
                    return DomainsDao.load();
                }
            }
        });
}])
.factory('AccountsDao', ['$http', '$q', function($http, $q) {
    var dao = {};
    dao.data = [];
    
    var prepareLoadedData = function(data) {
        dao.data = data || [];
        for (var i = 0; i < data.length; i++) {
            var splitted = data[i]['email'].split('@');
            data[i]['emailPrefix'] = splitted[0];
            data[i]['emailSuffix'] = splitted[1];
        }
        return data;
    }
    dao.load = function() {
        var promise = $http({'method':'get', 'url':'accounts'});
        promise.success(function (data, status, headers, config) {
            if (data.length > 0 && (typeof(data[0].account) === 'undefined' || typeof(data[0].email) === 'undefined')) {
                data = [];
            }
            return prepareLoadedData(data);
        });
        return promise;
    };
    dao.save = function(data) {
        var method = typeof(data.id) === 'undefined' ? 'post' : 'put';
        var promisses = [];
        var idx = 0;
        if (typeof(data.password) !== 'undefined' && data.password.length > 0) {
            promisses.push($http({'method':method, 'url':'passwords', 'data':data }));
            idx = 1;
        }
        var testsData = {};
        for (var key in data) {
            testsData[key] = data[key];
        }
        testsData['account'] = testsData['account'].replace(/\/$/, '');

        promisses.push($http({'method':method, 'url':'accounts', 'data':data }));
        promisses.push($http({'method':method, 'url':'tests',    'data':testsData }));
        var promiseAll = $q.all(promisses);
        promiseAll.then(function(data) {
            if (typeof(data) !== 'undefined' && data.length > idx) {
                dao.data = data[idx].data || [];
                for (var i = 0; i < dao.data.length; i++) {
                    var splitted = dao.data[i]['email'].split('@');
                    dao.data[i]['emailPrefix'] = splitted[0];
                    dao.data[i]['emailSuffix'] = splitted[1];
                }
                return dao.data;
            }
            return [];
        }, function (error) {
            throw error;
        });
        return promiseAll;
    };
    dao.delete = function(data) {
        var promise1 = $http({'method':'delete', 'url':'accounts', 'data':data });
        var promise2 = $http({'method':'delete', 'url':'passwords', 'data':data });
        var promiseAll = $q.all([promise1, promise2]);
        promiseAll.then(function(data) {
            dao.data = data[0].data || [];
            dao.data = prepareLoadedData(dao.data);
            //return data;
        });
        return promiseAll;
    }
    return dao;
}])
.controller('AccountsCtrl', ['$scope','$filter','modalService','AccountsDao','DomainsDao'
                   , function($scope,  $filter,  modalService,  AccountsDao,  DomainsDao) {
    
    $scope.data = AccountsDao.data;
    $scope.domains = DomainsDao.data;
    var nothingSelected = $filter('i18n')('select.chooseplease');
    $scope.pw = {raw:'', retyped: ''};
    var backup = {};
    $scope.create = function() {
        for (var i in $scope.data) {
            delete $scope.data[i].editModeEnabled;
        }
        var row = {'isNew':true,'editModeEnabled':true,'account':'','emailPrefix':'','emailSuffix':nothingSelected};
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
                if (typeof($scope.pw.raw) === 'string' && $scope.pw.raw.length > 0 && $scope.pw.raw == $scope.pw.raw) {
                    var shaObj = new jsSHA("SHA-512", "TEXT");
                    shaObj.update($scope.pw.raw); // == doveadm pw -s SHA512 -p <password>
                    row.password = row.account + ':{SHA512}' + shaObj.getHash("B64");
                }
                row.email = row.emailPrefix + '@' + row.emailSuffix;
                AccountsDao.save(row).then(function(data) {
                    $scope.data = AccountsDao.data;
                });
                break;
            }
        }
        $scope.pw = {raw:'', retyped: ''};
        backup = {};
        console.log('save(row):', row);
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
            AccountsDao.delete(data).then(function(data) {
                $scope.data = AccountsDao.data;
            });
        });
    };
}])

})();
