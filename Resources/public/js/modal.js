(function ($, app) {
    'use strict';

    _.extend(app, {
        PJAX_MODAL_SELECTOR: '#pjaxModal',
        PJAX_MODAL_CONTAINER: 'modal'
    });

    $(function () {
        $(app.PJAX_MODAL_SELECTOR)
            .on('pjax:send', onModalPjaxSend)
            .on('hidden.bs.modal', function () {
                $(this).find('.modal-content').html('');
            });
    });

    $(document).on('pjax:beforeSend', closeModalIfNeeded);

    function closeModalIfNeeded(xhr, options, settings) {
        // надо ли закрывать модальное окно после завершения запроса?
        var $modal = $(app.PJAX_MODAL_SELECTOR),
            closeModal;

        closeModal = settings.target != app.PJAX_MODAL_CONTAINER;
        // закрываем модальное окно
        if (closeModal) {
            $modal.modal('hide');
        }
    }

    function onModalPjaxSend(event, xhr, options) {
        $(app.PJAX_MODAL_SELECTOR)
            .modal('show')
            .find('[data-pjax-container="' + app.PJAX_MODAL_CONTAINER + '"]')
            .data(app.PJAX_REDIRECT_TARGET_PARAMETER, options.redirectTarget);
    }

})(jQuery, application);
