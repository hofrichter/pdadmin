(function() {
  'use strict';

angular.module('custom-directives', ['session', 'http-auth-interceptor'])
    .directive('waitingForAngular', ['$rootScope', 'Session', function($rootScope, Session) {
        return {
            restrict: 'C',
            link: function(scope, elem, attrs) {
                //once Angular is started, remove class:
                elem.removeClass('waiting-for-angular ');
                scope.$on('event:auth-loginRequired', function() {
                    Session.create();
                    $rootScope.sessionIsValid = false;
                });
                scope.$on('event:auth-loginConfirmed', function() {
                    $rootScope.sessionIsValid = true;
                    //main.show();
                    //login.slideUp();
                });
            }
        }
    }])
    .directive('ngClick', ['$timeout', function($timeout) {
        return {
            restrict: 'A',
            priority: 100, // give it higher priority than built-in ng-click
            link: function(scope, element, attr) {
                element.bind('click', function() {
                    //console.log('Adding css-class "clicked" to a ' + element[0].tagName + '-tag...');
                    element.addClass('clicked');
                    $timeout(function() {
                        //console.log('Removing css-class "clicked"...');
                        element.removeClass('clicked');
                    }, 1000);
                })
            }
        }
    }])
    .directive('markActive', ['$location', function($location) {
        return {
            restrict: 'A',
            link: function(scope, elem) {
                function mark() {
                    var path = $location.path();
                    if (path) {
                        angular.forEach(elem.find('li'), function(parent) {
                            var a = parent.querySelector('a');
                            var li = angular.element(parent);
                            a.href.match('#' + path + '(?=\\?|$)') ?
                                li.addClass('active') : li.removeClass('active');
                        });
                    }
                }
                mark();
                scope.$on('$locationChangeSuccess', mark);
            }
        }
    }])
    .directive('checkeddropdown', function () {
        return {
            restrict: 'A',
            scope: {
                // @  Used to pass a string value into the directive
                // =    Used to create a two-way binding to an object that is passed into the directive
                // &    Allows an external function to be passed into the directive and invoked
                items: '=',
                selectedItems: '=',
                customToString: '=toString',
                customToModel: '=toModel',
                customToLabel: '=toLabel',
                customIsSelected: '=isSelected',
                customNothingSelectedLabel: '@nothingSelectedLabel',
                readonly: '='
            },
            template: function() {
                var template = 
                      '<span ng-show="readonly">{{dropDownLabel}}</span>'
                    + '<div ng-hide="readonly" class="btn-group" dropdown is-open="status.isopen">'
                       + '<button id="single-button" type="button" class="btn btn-primary" dropdown-toggle>'
                          + '{{dropDownLabel}} <span class="caret"></span>'
                       + '</button>'
                       + '<ul class="dropdown-menu" role="menu" aria-labelledby="single-button">'
                           + '<li ng-repeat="item in items">'
                              + '<div class="checkbox" ng-click="click($event, item)"><label><span class="glyphicon {{isSelected(item) ? \'glyphicon-ok\' : \'\'}}"></span> {{toString(item)}}</label></div></a>'
                          + '</li>'
                       + '</ul>'
                   + '</div>';
                return template;
            },
            link: function ($scope, $element, $attrs) {
                $scope.readonly = $scope.readonly || false;
                $scope.nothingSelectedLabel = $scope.customNothingSelectedLabel || '...';
                $scope.toLabel = $scope.customToLabel || function(selectedItems) {
                    var items = [];
                    if (typeof(selectedItems) === 'string') {
                        return selectedItems;
                    } else  {
                        for (var i = 0; i < 3 && i < selectedItems.length; i++) {
                            if (typeof(selectedItems[i]) !== 'undefined') {
                                items.push(selectedItems[i]);
                            }
                        }
                        if (selectedItems.length > 3) {
                            items.push('...');
                        }
                        return items.join(', ');
                    }
                };
                $scope.toString = $scope.customToString || function(item) {
                    return item;
                };
                $scope.toModel = $scope.customToModel || function(item) {
                    return item;
                };
                $scope.click = function($event, item) {
                    var enable = true;
                    for (var i = 0; i < $scope.checkedState.length; i++) {
                        if ($scope.checkedState[i] == $scope.toModel(item)) {
                            $scope.checkedState.splice(i, 1);
                            enable = false;
                            break;
                        }
                    }
                    if (enable || $event.target.checked) {
                        $scope.checkedState.push($scope.toModel(item));
                    }
                    $scope.selectedItems = [];
                    for (var i = 0; i < $scope.checkedState.length; i++) {
                        $scope.selectedItems.push($scope.checkedState[i]);
                    }
                    $scope.selectedItems.sort();
                    $scope.dropDownLabel = $scope.toLabel($scope.selectedItems) || $scope.nothingSelectedLabel;
                }
                $scope.isSelected = $scope.customIsSelected || function(item) {
                    for (var i = 0; i < $scope.checkedState.length; i++) {
                        if ($scope.checkedState[i] == $scope.toModel(item)) {
                            return true;
                        }
                    }
                    return false;
                }
                $scope.checkedState = [];
                $scope.selectedItems = $scope.selectedItems || [];
                for (var i = 0; i < $scope.selectedItems.length; i++) {
                    $scope.checkedState.push($scope.selectedItems[i]);
                }
                $scope.dropDownLabel = $scope.toLabel($scope.selectedItems) || $scope.nothingSelectedLabel;
            }
        };
    })

    .directive('messages', function () {
        return {
            restrict: 'EA',
            scope: {},
            template: function() {
                var template =
                     '<alert ng-repeat="message in messages" type="{{message.type}}"'
                         + 'close="remove($index)">{{message.text | i18n}}'
                    + '</alert>';
                return template;
            },
            controller: 'messagesCtrl'
        };
    })
    .factory('messages', ['$timeout', function($timeout) {
        var self = {};
        self.messages = [];
        self.add = function(type, text, autoCloseDelay) {
            if (type && text) {
                self.messages.push({type:type, text:text});
                var index = self.messages.length - 1;
                if (autoCloseDelay) {
                    $timeout(function() {
                        self.remove(index);
                    }, autoCloseDelay);
                }
            }
            return -1;
        };
        self.remove = function(index) {
            if (index > -1 && index < self.messages.length) {
                self.messages.splice(index, 1);
            }
        };
        self.clear = function(index) {
            self.messages = [];
        };
        return self;
    }])
    .controller('messagesCtrl', ['$scope', 'messages', function ($scope, messagesService) {
        $scope.add = function(type, text) {
            messagesService.add(type, text);
        };
        $scope.remove = function(index) {
            messagesService.remove(index);
        };
        $scope.clear = function() {
            messagesService.clear();
        };
        $scope.messages = messagesService.messages;
    }])

})();
