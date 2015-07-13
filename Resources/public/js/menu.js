(function ($, app) {
    'use strict';

    _.extend(app, {
        MENU_CURRENT_CLASS: 'active',
        MENU_ROOT_SELECTOR: '#report_nav'
    });


    $(document).on('pjax:send', function (event, content, options) {
        var currentItem = mathCurrentMenuItem(options.url);
        if (currentItem) {
            setCurrentMenuItem(currentItem);
        }
    });

    /**
     *
     * @param url
     * @returns {*}
     */
    function mathCurrentMenuItem(url) {
        var currentItem;
        $(app.MENU_ROOT_SELECTOR + ' a[data-route]').each(function () {
            //var url = Routing.generate($(this).attr('data-route'), app.params);
            var itemUrl = this.href;
            if (itemUrl === url) {
                currentItem = $(this);
            }
        });

        return currentItem;
    }

    function setCurrentMenuItem(currentItem) {
        $(app.MENU_ROOT_SELECTOR + ' .' + app.MENU_CURRENT_CLASS).removeClass(app.MENU_CURRENT_CLASS);

        if (!currentItem || !currentItem.length){
            return;
        }

        currentItem.parents(app.MENU_ROOT_SELECTOR + ' li').each(function () {
            $(this).addClass(app.MENU_CURRENT_CLASS);
        });

    }
})(jQuery, application);
