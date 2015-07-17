(function ($, app) {
    'use strict';

    $.extend(app, {
        MENU_CURRENT_CLASS: 'active',
        MENU_ROOT_SELECTOR: '.navbar-nav',
        PJAX_MENU: 'menu-current'
    });


    $(document)
        .on('pjax:end', function (event, content, options) {
            /*if (!options.push) {
                return;
            }*/
            var currentItems = $(event.target).find('[data-' + app.PJAX_MENU + ']');
            if (currentItems) {
                var selectedLinks = currentItems.data(app.PJAX_MENU);
                options.selectedLinks = selectedLinks;
                setCurrentMenuItem(selectedLinks);
            }
        });

    function setCurrentMenuItem(selectedLinks) {
        $(app.MENU_ROOT_SELECTOR + ' li.' + app.MENU_CURRENT_CLASS).removeClass(app.MENU_CURRENT_CLASS);
        if (!selectedLinks || !selectedLinks.length) {
            return;
        }

        $(selectedLinks).each(function (key, currentItem) {
            $(app.MENU_ROOT_SELECTOR + ' li[data-name="' + currentItem + '"]')
                .addClass(app.MENU_CURRENT_CLASS)
                .parents(app.MENU_ROOT_SELECTOR + ' li').each(function () {
                    $(this).addClass(app.MENU_CURRENT_CLASS);
                });
        });
    }
})(jQuery, application);
