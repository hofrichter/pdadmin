(function() {
'use strict';
/**
 * @since 09/2015
 * @author hofsve - 2015-09-22 - initial version
 */
angular.module('tests', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/tests', {
            templateUrl: 'views/tests/tests.html',
            controller: 'TestsCtrl',
            resolve: {
                data: function(TestsDao) {
                    return TestsDao.load();
                },
                deployments: function(TestsDao) {
                    return TestsDao.deployments();
                }
            }
        });
}])
.factory('TestsDao', ['$http', '$q', function($http, $q) {
    var dao = {};
    dao.data = [];
    dao.domains = [];
    dao.accounts = [];
    dao.released = false;
    dao.nextDeployment = '';
    var parseBoolean = function (value) {
        if (typeof(value) == 'boolean') {
            return value;
        } else if (typeof(value) == 'string') {
            return value.toLowerCase() == 'true';
        } else if (typeof(value) == 'number') {
            return value != 0;
        }
        return false;
    }
    dao.load = function() {
        var promisses = [
                $http({'method':'get', 'url':'tests'}),
                $http({'method':'get', 'url':'domains'}),
                $http({'method':'get', 'url':'accounts'}),
                $http({'method':'get', 'url':'releases'})
            ];
        var promiseAll = $q.all(promisses);
        promiseAll.then(function(data) {
            if (typeof(data) !== 'undefined' && data.length >= 3) {
                dao.data = data[0].data || [];
                dao.domains = data[1].data || [];
                dao.accounts = data[2].data || [];
                dao.released = parseBoolean(data[3].data || false);
                return data[0].data;
            }
            return [];
        }, function (error) {
            throw error;
        });
        return promiseAll;
    };
    dao.deployments = function() {
        var promise = $q.all([
            $http({'method':'get', 'url':'deployments' }),
            $http({'method':'get', 'url':'releases'})
        ]);
        promise.then(function(data) {
            dao.nextDeployment = data[0].data || '';
            dao.released = parseBoolean(data[1].data || false);
            return dao.nextDeployment;
        });
        return promise;

    }
    dao.save = function(data) {
        var method = typeof(data.id) === 'undefined' ? 'post' : 'put';
        var promise = $http({'method':method, 'url':'tests', 'data':data });
        promise.success(function(data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    };
    dao.delete = function(data) {
        var promise = $http({'method':'delete', 'url':'tests', 'data':data });
        promise.success(function(data, status, headers, config) {
            dao.data = data || [];
            return data;
        });
        return promise;
    };
    dao.deploy = function(data) {
        var promise = $http({'method':'post', 'url':'releases'});
        promise.success(function(data, status, headers, config) {
            dao.released = parseBoolean(data || false);
            return data;
        });
        return promise;
    };
    dao.undeploy = function() {
        var promise = $http({'method':'delete', 'url':'releases'});
        promise.success(function(data, status, headers, config) {
            dao.released = parseBoolean(data || false);
            return data;
        });
        return promise;
    };
    dao.runTests = function() {
        var promise = $http({'method':'get', 'url':'tests'}).success(function(data) {
            if (typeof(data) !== 'undefined' && data.length > 1) {
                dao.data = data || [];
                return data;
            }
            return false;
        });
        return promise;
    };
    return dao;
}])
.controller('TestsCtrl', ['$scope','$filter','$interval','modalService','TestsDao','AccountsDao'
               , function( $scope,  $filter,  $interval,  modalService,  TestsDao,  AccountsDao) {
 
    $scope.data = TestsDao.data;
    $scope.released = TestsDao.released;
    $scope.nextDeployment = TestsDao.nextDeployment;
    $scope.testsSuccess = false;
    $scope.accounts = TestsDao.accounts;
    if ($scope.nextDeployment && $scope.nextDeployment.countdown && $scope.nextDeployment.countdown.match('^[0-9]+$')) {
        
        var timer = function(fn, delay) {
            var timer = $interval(fn, delay);
            $scope.$on('$destroy', function(evt) {
                if (typeof(timer) !== 'undefined') {
                    $interval.cancel(timer);
                    timer = undefined;
                }
            });
        }

        timer(function() {
                $scope.nextDeployment.countdown -= ($scope.nextDeployment.countdown > 0 ? 1 : 0);
            }, 1000);
        timer(function() {
                TestsDao.deployments().then(function() {
                    $scope.nextDeployment = TestsDao.nextDeployment;
                    $scope.released = TestsDao.released;
                });
            }, 30000); // at least every 5min (30000ms = 1000ms*60s*5m)
        
    }
    var test = function($scope) {
        $scope.testsSuccess = true;
        for (var i = 0; i < $scope.data.length; i++) {
            $scope.testsSuccess = $scope.testsSuccess && $scope.data[i].testresult && $scope.data[i].testresult == true;
        }
    }

    var nothingSelected = $filter('i18n')('select.chooseplease');
    var backup = {};
    test($scope);

    $scope.testStatus = function (value) {
        if (typeof(value) == 'undefined') {
            return 'untestet';
        } else if (typeof(value) == 'object') {
            return 'failed';
        } else if (typeof(value) == 'boolean') {
            return value ? 'success' : 'failed';
        }
    };
    $scope.create = function() {
        $scope.testsSuccess = false;
        var deleteRowId = -1;
        for (var i in $scope.data) {
            delete $scope.data[i].editModeEnabled;
            if ($scope.data[i].isNew) {
                deleteRowId = i;
                continue;
            }
        }
        if (deleteRowId > -1) {
            $scope.data.splice(deleteRowId, 1);
        }
        var row = {'isNew':true,'editModeEnabled':true,'email':'','account':nothingSelected};
        $scope.data.push(row);
        angular.copy(row, backup);
        $scope.data[$scope.data.length-1].readonly = false;
    };
    $scope.edit = function(row) {
        $scope.testsSuccess = false;
        backup = {}
        angular.copy(row, backup);
        var deleteRowId = -1;
        for (var i in $scope.data) {
            delete $scope.data[i].editModeEnabled;
            if ($scope.data[i].isNew) {
                deleteRowId = i;
                continue;
            }
        }
        if (deleteRowId > -1) {
            $scope.data.splice(deleteRowId, 1);
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
                if (typeof(row.oldaccount) == 'object') {
                    row.oldaccount = row.oldaccount[0];
                }
                if (typeof(row.account) == 'object') {
                    row.account = row.account[0];
                }
                TestsDao.save(row).then(function(data) {
                    $scope.data = data.data;
                });
                break;
            }
        }
        backup = {};
    };
    $scope.cancel = function(row) {
        delete row.editModeEnabled;
        if (typeof(row.isNew) !== 'undefined' && row.isNew) {
            $scope.data.pop();
        } else {
            angular.copy(backup, row);
        }
    };
    $scope.delete = function(row) {
        delete row.editModeEnabled;
        delete row.isNew;
        modalService.open({}, row).then(function(data) {
            TestsDao.delete(data).then(function(data) {
                $scope.data = data.data;
                $scope.testsSuccess = false;
            });
        });
    };

    $scope.runTests = function() {
        TestsDao.runTests().then(function(data) {
            $scope.data = TestsDao.data;
            $scope.released = TestsDao.released;
            test($scope);
        });
    };
    $scope.deploy = function() {
        TestsDao.deploy().then(function(data) {
            $scope.released = TestsDao.released;
        });
    };
    $scope.undeploy = function() {
        TestsDao.undeploy().then(function(data) {
            $scope.released = TestsDao.released;
        });
    };
    $scope.toString = function(account) {
        return account.account;
    }
}])

})();
