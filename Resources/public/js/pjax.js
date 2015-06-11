(function ($, _, cookie, exports) {
    'use strict';

    var PJAX_URL = 'pjax-url'; // TEST: урл для контейнера (чтоб нормально делать reload)

    var PJAX_ROOT_CONTAINER_NAME = 'main';
    var PJAX_REDIRECT_TARGET_PARAMETER = 'pjax-redirect-target';

    var PJAX_MODAL = '#myModal'; // идентификатор модального окна
    var PJAX_CONTAINER_EVENT = 'pjax-event';
    var PJAX_PUSH = 'pjax-push';

    var app = exports.application = {
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
            return findTargetContainer(target || PJAX_ROOT_CONTAINER_NAME);
        },

        /**
         * Адрес для контейнера
         * @param $container
         * @returns {*}
         */
        getPjaxContainerUrl: function ($container) {
            return $container.data(PJAX_URL);
        },

        getUrl: function (url, target) {
            var container = this.getPjaxContainer(target);
            var url = url || this.getPjaxContainerUrl(container) || '';

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

        $(PJAX_MODAL).on('hidden.bs.modal', function () {
            $(this).find('.modal-content').html('');
        });

        if ($.support.pjax) {
            initializePjax();
        }
    });

    function initializePjax() {
        $.pjax.defaults.timeout = 50000;

        $(function () {
            $('#myModal').on('pjax:send', onModalPjaxSend);
        });

        $(document).on('click', app.linkSelector, onPjaxLinkClick);
        $(document).on('submit', app.formSelector, onPjaxFormSubmit);
        $(document).on('pjax:complete', onPjaxComplete);

        $(document).on('pjax:beforeSend', onPjaxBeforeSend);
        $(document).on('pjax:beforeReplace', onPjaxBeforeReplace);
    }

    function onModalPjaxSend(event, xhr, options) {
        $('#myModal')
            .modal('show')
            .find('[data-pjax-container="modal"]')
            .data(PJAX_REDIRECT_TARGET_PARAMETER, options.redirectTarget);
    }

    function onPjaxLinkClick(event) {
        var target = findPjaxTargetFor(this);
        var container = findTargetContainer(target);
        var redirectTarget = $(this).data(PJAX_REDIRECT_TARGET_PARAMETER);

        processSubmit($(this), container, event);

        $.pjax.click(event, container, {
            target: target,
            redirectTarget: redirectTarget,
            push: toPush(target, 'GET', $(this).data(PJAX_PUSH)),
            replace: false
        });
    }

    function onPjaxFormSubmit(event) {
        if (!event.isDefaultPrevented()) {
            var $form = $(this);
            var target = findPjaxTargetFor(this);
            var targetContainer = findTargetContainer(target);

            processSubmit($form, targetContainer, event);

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
                redirectTarget: targetContainer.data(PJAX_REDIRECT_TARGET_PARAMETER),
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
    }

    /**
     * Пушить или нет
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

            return method == 'GET' && target == PJAX_ROOT_CONTAINER_NAME;
        }

        return option;
    }


    /**
     * Дополнительная обработка pjax-запроса
     *
     * data-pjax-close-modal="false"
     * По-умолчанию модал не закрывается. Если надо закрывать
     * (например, при выборе элемента из списка)
     * установите атрибут data-close-modal="true"
     *
     * data-pjax-related="main"
     * Контейнер который нужно обновить после закрытия модала
     * Это чтоб не следить за кучей зависимостей при операциях в модале
     *
     * data-pjax-event="event:name"
     * Генерируем событие после выполнения pjax-запроса (pjax:complete) через application.trigger
     *
     * data-pjax-redirect-close-modal="true"
     * Нужно закрыть модальное окно после редиректа
     * Например, после сохранения элемента в окне
     *
     * @param $element - pjax-элемент (form или a.href)
     * @param target - pjax-контейнер, куда будем пихать ответ
     */
    function processSubmit($element, target, event) {
        if (target == undefined) {
            return;
        }

        // надо ли закрывать модальное окно после завершения запроса?
        var $modal = $(PJAX_MODAL),
            closeModal;

        closeModal = target.data('pjax-container') != "modal";
        // закрываем модальное окно
        if (closeModal) {
            $modal.modal('hide');
        }

        // события после выполнения pjax-запроса
        var pjaxEvent = $element.data(PJAX_CONTAINER_EVENT);
        // открываем модальное окно?
        var isModal = $element.data('pjax') != undefined && $element.data('pjax') === 'modal';


        // после выполненной операции
        target.one('pjax:complete', function (event, xhr, status, request) {
            if (pjaxEvent) {
                app.trigger(pjaxEvent, request);
            }
        });
    }

    function onPjaxComplete(event, content, status, options) {
        if (options.push) {
            var title = $(event.target).find('[data-title]');
            if (title) {
                $('title').text(title.data('title'));
            }
        }

        /*var actions = $(event.target).find('[data-pjax-actions]');
         if (actions) {
         $('#actions').html(actions.data('pjax-actions'));
         }*/

        /*var breadcrumbs = $(event.target).find('[data-pjax-breadcrumbs]');
         if (breadcrumbs) {
         $('#breadcrumbs').html(actions.data('pjax-breadcrumbs'));
         }*/

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
        || PJAX_ROOT_CONTAINER_NAME;
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

})(jQuery, _, Cookies, window);
