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
         * Добавление колбэка для инициализации добавляемых узлов DOM
         * @param {Function} initFn принимает на вход добавленный узел DOM
         */
        register: function (initFn) {
            this.initializers.push(initFn);
        }
    }
})(jQuery, window);
