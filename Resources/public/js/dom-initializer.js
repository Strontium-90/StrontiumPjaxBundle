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
         * ���������� ������� ��� ������������� ����������� ����� DOM
         * @param {Function} initFn ��������� �� ���� ����������� ���� DOM
         */
        register: function (initFn) {
            this.initializers.push(initFn);
        }
    }
})(jQuery, window);
