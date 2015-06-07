/**
 * Отправляем форму с файлами и блекджеком через аякс
 * Внимание, используется FormData (см. совместимость по браузерам)
 * @link https://developer.mozilla.org/en-US/docs/Web/API/FormData
 *
 * User: terrasoff
 * Date: 22/09/14 12:56
 */
(function($) {
    $.fn.serializeMultipart = function() {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        $.each($(obj).find("input[type='file']"), function(i, tag) {
            $.each($(tag)[0].files, function(i, file) {
                formData.append(tag.name, file);
            });
        });
        var params = $(obj).serializeArray();
        $.each(params, function (i, val) {
            formData.append(val.name, val.value);
        });
        return formData;
    };
})(jQuery);
