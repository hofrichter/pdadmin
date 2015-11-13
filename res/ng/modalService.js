(function() {
  'use strict';

angular.module('modalService', [])
.factory('modalService', ['$q', '$modal', '$modalStack', '$filter', function($q, $modal, $modalStack, $filter) {
    return {
        /**
         * data: are the data to show
         * confg: is configuration, that can overwrite all/partial the defaults
         */
        open: function(config, data) {
            var deferred = $q.defer();
            var defaults = {
                title: 'modal.title.default',
                body: 'modal.body.default',
                type: 'danger', //default, primary, success, info, warning, danger
                backdrop: 'static',
                keyboard: false,
                okButton: true,
                okButtonLabel: 'button.delete',
                okButtonType: 'btn-danger',
                okButtonGlyphicon: 'glyphicon-trash',
                cancelButton: true,
                cancelButtonLabel: 'button.cancel',
                cancelButtonType: 'btn-primary',
                cancelButtonGlyphicon: 'glyphicon-close',
                templateUrl: 'res/ng/modalService.html',
                resolve: {
                    data: function () { return data; },
                    config: function () { return defaults; },
                },
                controller: function($scope, $modalInstance, config, data) {
                    $scope.data = data;
                    if (config.title) {
                        config.title = $filter('i18n')(config.title);
                    }
                    if (config.body) {
                        config.body = $filter('i18n')(config.body);
                    }
                    $scope.config = config;
                    $scope.ok = function() {
                        $modalInstance.close($scope.data);
                        deferred.resolve($scope.data);
                    };
                    $scope.cancel = function() {
                        $modalInstance.dismiss('cancel');
                        deferred.reject($scope.data);
                    };
                }
            };
            angular.extend(defaults, config);
            $modal.open(defaults);
            return deferred.promise;
        },
        close: function(reason) {
            $modalStack.dismissAll(reason);
        }
    };
}]);

})();
