(function ($, app) {
    'use strict';

    $(document)
        .on('pjax:send', showLoadingIndicators)
        .on('pjax:error', hideLoadingIndicators)
        .on('pjax:complete', hideLoadingIndicators)
        .on('submit', showSubmitIndicators);

    function showLoadingIndicators(event) {
        if (event.isDefaultPrevented()) {
            return;
        }
        var target = $(event.target).data('pjax-container');

        if (target == app.ROOT_CONTAINER_NAME) {
            $('#ajax-loading').fadeIn(1000);
        } else {
            var loading = $('<div class="contentLoading"><div class="img"></div></div>');
            loading.hide();
            $(event.target).css('position', 'relative');
            $(event.target).append(loading);
            loading.fadeIn(500);
        }
    }

    function hideLoadingIndicators(event) {
        $('#ajax-loading').fadeOut(100);
        $('.contentLoading').fadeOut(100);
        $(document).find('button[data-loading-text]').button('reset');
    }

    function showSubmitIndicators(event) {
        $form.find('button[data-loading-text]').button('loading');
    }
})(jQuery, application);
