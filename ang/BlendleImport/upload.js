(function (angular, $, _, console) {

    // Directive to store a file in the model as a data-uri
    // From http://stackoverflow.com/questions/17063000/ng-model-for-input-type-file
    angular.module('BlendleImport').directive('crmFilereader', function ($q) {
        var slice = Array.prototype.slice;
        return {
            restrict: 'A',
            require: '?ngModel',
            link: function(scope, element, attrs, ngModel) {
                if (!ngModel) return;
                ngModel.$render = function() {};

                element.bind('change', function(e) {
                    var element = e.target;

                    $q.all(slice.call(element.files, 0).map(readFile))
                        .then(function(values) {
                            if (element.multiple) ngModel.$setViewValue(values);
                            else ngModel.$setViewValue(values.length ? values[0] : null);
                        });

                    function readFile(file) {
                        var deferred = $q.defer();

                        var reader = new FileReader();
                        reader.onload = function(e) {
                            deferred.resolve(e.target.result);
                        };
                        reader.onerror = function(e) {
                            deferred.reject(e);
                        };
                        reader.readAsDataURL(file);

                        return deferred.promise;
                    }

                });
            }
        };
    });

})(angular, CRM.$, CRM._);
