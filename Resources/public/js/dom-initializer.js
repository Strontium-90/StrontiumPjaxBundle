(function ($, exports) {
    'use strict';
    var domInitializer = exports.domInitializer = {
        initializers: [],

        initialize: function (changesRoot) {
            $.each(this.initializers, function (i, initFn) {
                initFn(changesRoot);
            });
        },

        /**
         * @param {Function} initFn
         * @return domInitializer
         */
        register: function (initFn) {
            this.initializers.push(initFn);

            return this;
        }
    }

    $(function () {
        domInitializer.initialize(document.documentElement);
    });
})(jQuery, window);
