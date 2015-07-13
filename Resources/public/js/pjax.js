(function ($, _, cookie, exports) {
    'use strict';
    
    var PJAX_PUSH = 'pjax-push';

    var app = exports.application = {
        PJAX_REDIRECT_TARGET_PARAMETER: 'pjax-redirect-target',
        ROOT_CONTAINER_NAME: 'main',

        linkSelector: 'a[data-pjax],' +
        'a:not([data-toggle]):not([data-behavior]):not([data-skip-pjax]):not([href^="http://"]):not([href^="/_profiler/"])',

        formSelector: 'form:not([w]):not([data-skip-pjax])',

        domInitializers: [],
        params: {},

        initializeDom: function (changesRoot) {
           _.each(this.domInitializers, function (initFn) {
                initFn(changesRoot);
            });
        },

        /**
         * Добавление колбэка для инициализации добавляемых узлов DOM
         * @param {Function} initFn принимает на вход добавленный узел DOM
         */
        registerDomInitializer: function (initFn) {
            this.domInitializers.push(initFn);
        },

        getPage: function (route, params, target) {
            var req_params = this.params;
            for (var i in params) {
                req_params[i] = params[i];
            }

            return this.getUrl(Routing.generate(route, req_params), target);
        },

        getPjaxContainer: function (target) {
            return findTargetContainer(target || app.ROOT_CONTAINER_NAME);
        },

        getUrl: function (url, target) {
            var container = this.getPjaxContainer(target);
            var url = url || '';

            return $.pjax({
                url: url,
                container: container,
                method: 'get',
                push: false,
                replace: false
            });
        },

        reload: function (target, url) {
            return this.getUrl(url, target);
        },


        /**
         * Генерируем события приложения
         * @param eventName
         * @param params
         */
        trigger: function (eventName, params) {
            window.dispatchEvent(new CustomEvent(eventName, {detail: params}));
        },

        /**
         * Одноразовая обработка события (после удаляем обработчик)
         * @param context
         * @param eventName
         * @param cb
         */
        listenOnce: function (context, eventName, cb) {
            // IE >= 9
            window.addEventListener(eventName, function (event) {
                cb.call(context, event);
                window.removeEventListener(eventName, cb);
            });
        }
    };


    $(function () {
        app.initializeDom(document.documentElement);

        if ($.support.pjax) {
            initializePjax();
        }
    });
    function initializePjax() {
        $.pjax.defaults.timeout = 50000;

        $(document).on('click', app.linkSelector, onPjaxLinkClick);
        $(document).on('submit', app.formSelector, onPjaxFormSubmit);
        $(document).on('pjax:complete', onPjaxComplete);

        $(document).on('pjax:beforeSend', onPjaxBeforeSend);
        $(document).on('pjax:beforeReplace', onPjaxBeforeReplace);
    }

    function onPjaxLinkClick(event) {
        if (event.isDefaultPrevented()) {
            return;
        }
        var target = findPjaxTargetFor(this);
        var container = findTargetContainer(target);
        var redirectTarget = $(this).data(app.PJAX_REDIRECT_TARGET_PARAMETER);

        $.pjax.click(event, container, {
            target: target,
            redirectTarget: redirectTarget,
            push: toPush(target, 'GET', $(this).data(PJAX_PUSH)),
            replace: false
        });
    }

    function onPjaxFormSubmit(event) {
        if (event.isDefaultPrevented()) {
            return;
        }
        var $form = $(this);
        var target = findPjaxTargetFor(this);
        var targetContainer = findTargetContainer(target);
        /**
         * Если пытаемся отправить форму с файлами,
         * но браузером не поддерживается FormData,
         * тогда просто штатно отправляем форму.
         * Пока так.
         */
        if ($form.attr('')
            && window.FormData != undeifned
            && $.fn.serializeMultipart != undefined) {
            event.stopPropagation();
            $form.submit();
            return;
        }

        var params = {
            target: target,
            redirectTarget: targetContainer.data(app.PJAX_REDIRECT_TARGET_PARAMETER),
            push: toPush(target, $form.attr('method'), $(this).data(PJAX_PUSH)),
            replace: false
        };

        if ($form.attr('enctype') === 'multipart/form-data') {
            _.extend(params, {
                contentType: false,
                processData: false,
                cache: false,
                data: $form.serializeMultipart()
            })
        }

        $.pjax.submit(event, targetContainer, params);
    }

    /**
     * Пушить стейт или нет
     *
     * @param target
     * @param method
     * @param option bool значение атрибута data-pjax-push
     * @returns bool
     */
    function toPush(target, method, option) {
        if (option == undefined) {
            if (method == undefined) {
                method = 'GET';
            }
            method = method.toUpperCase();

            return method == 'GET' && target == app.ROOT_CONTAINER_NAME;
        }

        return option;
    }
    

    function onPjaxComplete(event, content, status, options) {
        if (options.push) {
            var title = $(event.target).find('[data-title]');
            if (title) {
                $('title').text(title.data('title'));
            }
        }
        app.initializeDom(event.target);
    }

    function onPjaxBeforeReplace(event, contents, options) {
        var redirectedTo,
            redirectCookieTargetName,
            redirectCookieName,
            optionsTransformer,
            generateStateParams,
            redirectTarget = options.redirectTarget;

        if (redirectTarget) {
            redirectCookieTargetName = parsePjaxContainerSelector(options.context.selector);
            optionsTransformer = function (options) {
                return _.extend(options, {
                    context: findTargetContainer(redirectTarget)
                });
            };
            generateStateParams = function (options) {
                return {
                    container: options.context.selector
                };
            };
        } else {
            optionsTransformer = function (options) {
                return options;
            };
            generateStateParams = function (options) {
                return {};
            };
            redirectTarget = redirectCookieTargetName = findPjaxTargetFor(event.target);
        }

        redirectCookieName = 'pjax_redirect_' + redirectCookieTargetName;

        if (undefined !== (redirectedTo = cookie.get(redirectCookieName))) {
            cookie.expire(redirectCookieName);

            options = optionsTransformer(options);

            if (toPush(redirectTarget, 'GET')) {
                _.extend(event.state, {
                    push: true,
                    url: redirectedTo
                }, generateStateParams(options));
                window.history.pushState(event.state, event.state.title, event.state.url);
            }
        }
    }

    function onPjaxBeforeSend(event, xhr, settings) {
        xhr.setRequestHeader('X-PJAX-Target', settings.target);
        settings.redirectTarget && xhr.setRequestHeader('X-PJAX-Redirect-Target', settings.redirectTarget);
    }

    function findPjaxTargetFor(elem) {
        var $elem = $(elem);

        return $elem.data('pjax')
            || $elem.closest('[data-pjax-container]').data('pjax-container')
            || app.ROOT_CONTAINER_NAME;
    }

    function findTargetContainer(target) {
        var container = $(generatePjaxContainerSelector(target));

        return container.length ? container : null;
    }

    function generatePjaxContainerSelector(name) {
        // TODO сделать экранирование символов name для селектора
        return '[data-pjax-container="' + name + '"]';
    }

    /**
     * @param {string} selector
     * @returns {string}
     */
    function parsePjaxContainerSelector(selector) {
        // TODO сделать разэкранирование символов извлеченного имени, если оно экранировано
        return selector.match(/^\[data-pjax-container="(.+?)"\]$/)[1];
    }

    $.fn.serializeMultipart = function () {
        var obj = $(this);
        /* ADD FILE TO PARAM AJAX */
        var formData = new FormData();
        _.each($(obj).find("input[type='file']"), function (i, tag) {
            _.each($(tag)[0].files, function (i, file) {
                formData.append(tag.name, file);
            });
        });
        var params = $(obj).serializeArray();
        _.each(params, function (i, val) {
            formData.append(val.name, val.value);
        });
        return formData;
    };

})(jQuery, _, Cookies, window);
