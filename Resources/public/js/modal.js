(function ($, _, app) {
    'use strict';

    _.extend(app, {
        PJAX_MODAL: '#myModal'
    });

    $(function () {
        $(app.PJAX_MODAL)
            .on('pjax:send', onModalPjaxSend)
            .on('hidden.bs.modal', function () {
                $(this).find('.modal-content').html('');
            });
    });

    $(document).on('pjax:beforeSend', closeModalIfNeeded);

    function closeModalIfNeeded(xhr, options, settings) {
        // надо ли закрывать модальное окно после завершения запроса?
        var $modal = $(app.PJAX_MODAL),
            closeModal;

        closeModal = settings.target != "modal";
        // закрываем модальное окно
        if (closeModal) {
            $modal.modal('hide');
        }
    }

    function onModalPjaxSend(event, xhr, options) {
        $(app.PJAX_MODAL)
            .modal('show')
            .find('[data-pjax-container="modal"]')
            .data(app.PJAX_REDIRECT_TARGET_PARAMETER, options.redirectTarget);
    }

})(jQuery, _, application);
