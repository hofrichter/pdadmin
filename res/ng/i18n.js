(function() {
'use strict';

/**
 * This implementation is used to translate keys into language specific labels.
 * 
 * @since 12/2014
 * @author hofsve - 2014-12-01 - initial version
 */
var app = angular.module('i18n', []);

app
.value('i18nLocaleValues', {'LOCALE:ID':'-undefined-'})
.value('i18nSelectedLocale', 'de')
.filter('i18n', ['$http', 'i18nSelectedLocale', 'i18nLocaleValues', function ($http, i18nSelectedLocale, i18nLocaleValues) {
    /**
     * The filter implementation itselfs: 
     */
    function doFilter(key, parameters) {
        var resolved = key;
        if (typeof(i18nLocaleValues[key]) != 'undefined' && i18nLocaleValues[key] != '') {
            resolved = i18nLocaleValues[key];
        }
        resolved = (typeof(parameters) === 'undefined') ? resolved : resolved.replace('@{}@', parameters);
        return resolved;
    }
    
    /**
     * The public filter-signature contains a switch between loading the locales
     * and doing the translation as soon as the selected locale is loaded.
     */

    filterStub.loading = false;
    filterStub.$stateful = true;
    function filterStub(key, parameters) {
        if (i18nLocaleValues === null || i18nSelectedLocale != i18nLocaleValues['LOCALE:ID']) {
            if (filterStub.loading === false) {
                filterStub.loading = true;
                var url = 'res/ng/i18n/i18n-' + i18nSelectedLocale + '.json';
                $http({'method':'get', 'url': url}).success(function(result) {
                    i18nLocaleValues = result;
                    i18nSelectedLocale = i18nLocaleValues['LOCALE:ID'];
                    filterStub.loading = false;
                    //return doFilter(key, parameters)
                }).error(function(error) {
                    console.error(error);
                    i18nLocaleValues = {'LOCALE:ID': i18nSelectedLocale};
                    filterStub.loading = false;

                });
            }
            return key;
        } else {
            return doFilter(key, parameters);
        }
    };
    return filterStub;

}]);
})();