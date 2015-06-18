(function ($, _, app, exports) {
    'use strict';
    var PJAX_FLASH = 'pjax-flashes';
    var PJAX_FLASH_SELECTOR = '[data-' + PJAX_FLASH + ']';


    _.extend(app, {
        PJAX_FLASH_CONTAINER: '.notification-container',
        MESSAGE_SUCCESS: 0,
        MESSAGE_ERROR: 1,
        MESSAGE_WARNING: 2,
        MESSAGE_NOTICE: 3,

        message: function (text, status) {
            this.clearMessage();
            if (!status) {
                status = this.MESSAGE_SUCCESS;
            }

            var msgbox = $('<div></div>').appendTo(app.PJAX_FLASH_CONTAINER);
            msgbox.addClass('alert');
            msgbox.hide();
            msgbox.text(text);
            switch (status) {
                case this.MESSAGE_SUCCESS :
                case 'success' :
                    msgbox.addClass('alert-success');
                    break;
                case this.MESSAGE_NOTICE :
                case 'notice' :
                    msgbox.addClass('alert-info');
                    break;
                case this.MESSAGE_WARNING :
                    msgbox.addClass('alert-warning');
                    msgbox.html('<i class="icon-warning-sign"></i> ' + msgbox.text());
                    break;
                case this.MESSAGE_ERROR :
                case 'error' :
                    msgbox.addClass('alert-danger');
                    msgbox.html('<i class="icon-erroralt"></i> ' + msgbox.text());
                    break;
            }

            msgbox.data("time", new Date());
            msgbox.fadeIn(500);

        },

        clearMessage: function () {
            $(app.PJAX_FLASH_CONTAINER + ' > div').each(function () {
                var time = $(this).data("time");
                if (!time || (new Date() - time >= 5 * 1000)) {
                    $(this).fadeOut(1000, function () {
                        $(this).html("");
                    });
                }
            });
        }
    });

    $(document)
        .on('pjax:send', function () {
            app.clearMessage();
        })
        .on('pjax:error', function () {
            app.message('Error', app.MESSAGE_ERROR);
        })
        .on('pjax:complete', function (event, content, status, options) {
            var flashes = $(event.target).find(PJAX_FLASH_SELECTOR);
            if (flashes) {
                var flashData = flashes.data(PJAX_FLASH);
                if (flashData) {
                    $(app.PJAX_FLASH_CONTAINER).html(flashData);
                }
                flashes.removeData(PJAX_FLASH);
                flashes.removeAttr('data-' + PJAX_FLASH);
            }
        });

})(jQuery, _, application, window);
