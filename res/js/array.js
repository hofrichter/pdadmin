/**
 * @since 14.04
 * @author Sven Hofrichter - 14.04 - initial version
 */

/**
 * Extension for the JavaScript-Arrays. This function adds all values of the
 * given object, array or simple datatype to the array.
 * 
 * @param mixed, items are the item to add
 * @return void
 */
if (typeof(Array.prototype.addAll) !== 'function') {
    Object.defineProperty(Array.prototype, 'addAll', {
          enumerable: false
        , configurable: true
        , writable: false
        , value:  function(items) {
            if (typeof(items) === 'object') {
                for (var i in items) {
                    this.push(items[i]);
                }
            } else {
                this.push(items);
            }
            return this;
        }
    });
};
/**
 * Extension for the JavaScript-Arrays. This function returns <code>true</code>
 * in those cases, when needle was found in the current array instance. The
 * function uses 'indexOf(needle)' and checks, whether the result was -1 or not.
 * 
 * @param mixed, needle is the item to look for 
 * @return Boolean, true when needle was found, otherwise false
 */
if (typeof(Array.prototype.contains) !== 'function') {
    Object.defineProperty(Array.prototype, 'contains', {
          enumerable: false
        , configurable: true
        , writable: false
        , value:  function(needle) {
            return this.indexOf(needle) != -1;
            //return false;
        }
    });
};
/**
 * Extension for the JavaScript-Arrays. This function returns <code>-1</code>
 * in those cases, when needle was NOT found in the current array instance,
 * otherwise the index of the element will be returned.
 * 
 * @param mixed, needle is the item to look for 
 * @return int, the item index or -1 when no array item is equals to needle
 */
if (typeof(Array.prototype.indexOf) !== 'function') {
    Array.prototype.indexOf = function(needle) {
        for (var key in this) {
            if (this[key] === needle) {
                return key;
            }
        }
        return -1;
    };
};

/**
 * This function implements a value change listener on an array structure.
 *
 * Example usage:
 * var arr = [1,2,3,4,5,6,7];
 * arr.watch(function(name, args) { console.log('>>> ' + name + ': ' + args); return args; }); // define a callback for all array-functions
 * arr.watch('push', function(name, args) { console.log('>>> push: ' + args); return args; }); // define a callback for a specific array-function (in this case: 'push')
 * arr.push(8);
 *
 * @param function, the callback function that should be called before the
 *        underlying array-function get called
 */
if (typeof(Array.prototype.watch) !== 'function') {
    Object.defineProperty(Array.prototype, "watch", {
          enumerable: false
        , configurable: true
        , writable: false
        , value:  function(name, handler) {
            var self = this;
            if (typeof(handler) === 'undefined') {
                handler = name;
                name = undefined;
            }
            var _redefine = function (name) {
                Object.defineProperty(self, name, {
                      enumerable: false
                    , configurable: true
                    , writable: false
                    , value:  function(arguments) {
                        var _arguments = handler.call(self, name, arguments);
                        return Array.prototype[name].call(self, _arguments);
                    }
                });
            }
            if (typeof(name) === 'undefined') {
                _redefine('push');
                _redefine('pop');
                _redefine('shift');
                _redefine('unshift');
            } else {
                _redefine(name);
            }
        }
    });
};

/**
 * This function removes the value change listener from the object structure.
 *
 * Example usage:
 * var arr = [1,2,3,4,5,6,7];
 * arr.unwatch('push'); // disable watching for a specific array-function (in this case: 'push')
 * arr.unwatch(); // disable watching for all array-functions
 */
if (typeof(Array.prototype.unwatch) !== 'function') {
    Object.defineProperty(Array.prototype, "unwatch", {
              enumerable: false
            , configurable: true
            , writable: false
            , value:  function(name) {
                var self = this;
                var _restore = function (name) {
                    Object.defineProperty(self, name, {
                          enumerable: false
                        , configurable: true
                        , writable: false
                        , value:  function(arguments) {
                            return Array.prototype[name].call(self, arguments);
                        }
                    });
                }
                if (typeof(name) === 'undefined') {
                    _restore('push');
                    _restore('pop');
                    _restore('shift');
                    _restore('unshift');
                } else {
                    _restore(name);
                }
            }
    });
};


