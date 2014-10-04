(function ($, _, cookie, exports) {
    'use strict';

    // TEST: урл для контейнера (чтоб нормально делать reload)
    var PJAX_URL = 'pjax-url';
    var PJAX_FLASH = 'pjax-flashes';
    var PJAX_FLASH_SELECTOR = '[data-' + PJAX_FLASH + ']';
    var PJAX_FLASH_CONTAINER = '.notification-container';
    var PJAX_SCROLLTO = 'pjax-scrollto';
    var PJAX_ROOT_CONTAINER_NAME = 'main';
    var PJAX_REDIRECT_TARGET_PARAMETER = 'pjax-redirect-target';
    var PJAX_REDIRECT_CLOSE_MODAL = 'pjax-redirect-close-modal';
    // идентификатор модального окна
    var PJAX_MODAL = '#myModal';
    var PJAX_CONTAINER_RELATION = 'pjax-related';
    var PJAX_CONTAINER_EVENT = 'pjax-event';
    var PJAX_CLOSE_MODAL = 'pjax-close-modal';

    var app = exports.application = {
        domInitializers: [],
        params: {},
        menu_current_class: 'active',
        selectors: {
            // выбор элемента из списка
            listitem: '.list-item-selector'
        },

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

        findCurrentMenuItemByUrl: function (a) {
            var currentItem;
            $('#report_nav a[data-route]').each(function () {
                //var url = Routing.generate($(this).attr('data-route'), app.params);
                if ($(this).attr('href') === a.pathname) {
                    currentItem = $(this);
                }
            });
            return currentItem;
        },

        setCurrentMenuItem: function (currentItem) {
            $('#report_nav .' + this.menu_current_class).removeClass(this.menu_current_class);

            if (!currentItem || !currentItem.length) return;

            //currentItem.parent().addClass(this.menu_current_class);
            var self = this;
            currentItem.parents('#report_nav li').each(function () {
                $(this).addClass(self.menu_current_class);
            });

            if (currentItem.hasClass('disabled')) {
                $('#content').html('<p>' + currentItem.attr('title') + '</p>');
            }
        },

        getPage: function (route, params, target) {
            var req_params = this.params;
            for (var i in params) {
                req_params[i] = params[i];
            }

            if ($('#context')) {
                this.setCurrentMenuItem($('#report_nav a[data-route="' + route + '"]'));
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
                scrollTo: null,
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

        setParametr: function (name, value, reload) {
            this.params[name] = value ? value.toString() : null;

            if (reload) {
                this.reload();
            }
        },

        setParametrs: function (params) {
            for (var name in params) {
                this.params[name] = params[name] ? params[name].toString() : null;
            }
        },

        getParametr: function (name) {
            return this.params[name];
        },

        getParametrs: function () {
            return this.params;
        },

        MESSAGE_SUCCESS: 0,
        MESSAGE_ERROR: 1,
        MESSAGE_WARNING: 2,
        MESSAGE_NOTICE: 3,
        message: function (text, status) {
            this.clearMessage();
            if (!status) {
                status = this.MESSAGE_SUCCESS;
            }

            var msgbox = $('<div></div>').appendTo(PJAX_FLASH_CONTAINER);
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
            $('.notification-container div').each(function () {
                var time = $(this).data("time");
                if (!time || (new Date() - time >= 5 * 1000)) {
                    $(this).fadeOut(1000, function () {
                        $(this).html("");
                    });
                }
            });
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

    app.registerDomInitializer(function genericControlsInit() {
        $("select").chosen({
            disable_search_threshold: 11,
            width: '100%',
            no_results_text: "Упс, ничего не найдено!",
        });
        //allow_single_deselect: true

        $('.crm-model-selector').modelSelector();

        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover();

        $("input").inputmask();
    });

    $(window).on('add.mopa-collection-item', function ($collection, row, index) {
        app.initializeDom(row);
    });

    $(function () {
        app.initializeDom(document.documentElement);

        app.setCurrentMenuItem($('#report_nav li.' + app.menu_current_class + ' a'));

        $('#myModal').on('hidden.bs.modal', function () {
            // при закрытии модала обновляем зависимые контейнеры (если заданы)
            var related = $(this).data(PJAX_CONTAINER_RELATION);
            if (related) {
                app.reload(related);
                $(this).removeData(PJAX_CONTAINER_RELATION);
            }

            $(this).find('.modal-content').html('');
        });
    });

    if ($.support.pjax) {
        initializePjax();
    }

    function initializePjax() {
        $.pjax.defaults.timeout = 50000;

        $(function () {
            $('#myModal').on('pjax:send', onModalPjaxSend);
        });

        $(document).on('pjax:send', onPjaxSend);
        $(document).on('pjax:error', onPjaxError);
        $(document).on('click',
            '#report_nav a,' +
            'a[data-pjax],' +
            'a:not([data-toggle]):not([data-behavior]):not([data-skip-pjax]):not([href^="http://"]):not([href^="/_profiler/"])',
            onPjaxLinkClick
        );
        $(document).on('submit', 'form:not([w]):not([data-skip-pjax])', onPjaxFormSubmit);
        $(document).on('pjax:complete', onPjaxComplete);

        $(document).on('pjax:beforeSend', onPjaxBeforeSend);
        $(document).on('pjax:beforeReplace', onPjaxBeforeReplace);
    }

    function onPjaxError(event) {
        hideLoadingIndicators();
        app.message('Произошла ошибка', app.MESSAGE_ERROR);
    }

    function onPjaxSend(event) {
        var target = $(event.target).data('pjax-container');

        app.clearMessage();

        if (target == 'main') {
            $('#ajax-loading').fadeIn(1000);
        } else {
            var loading = $('<div class="contentLoading"><div class="img"></div>');
            loading.hide();
            $(event.target).css('position', 'relative');
            $(event.target).append(loading);
            loading.fadeIn(500);
        }
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

        var currentItem = app.findCurrentMenuItemByUrl(event.currentTarget);

        if (currentItem) {
            app.setCurrentMenuItem(currentItem);
        }

        processSubmit($(this), container, event);

        $.pjax.click(event, container, {
            scrollTo: getPjaxScroll($(this)),
            target: target,
            redirectTarget: redirectTarget,
            push: target == PJAX_ROOT_CONTAINER_NAME,
            replace: false
        });
    }

    function onPjaxFormSubmit(event) {
        if (!event.isDefaultPrevented()) {
            var $form = $(this);
            var target = findPjaxTargetFor(this);
            var targetContainer = findTargetContainer(target);

            $form.find('button[data-loading-text]').button('loading');

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
                scrollTo: getPjaxScroll($form),
                target: target,
                redirectTarget: targetContainer.data(PJAX_REDIRECT_TARGET_PARAMETER),
                push: $form.attr('method').toLowerCase() === 'get',
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
     * После запроса прокручиваем к заданному контейнеру (или к началу страницы)
     * Контейнер задается дата-атрибутом (PJAX_SCROLLTO)
     * data-pjax-scrollto="container"
     *
     * @param $element
     * @returns {number}
     */
    function getPjaxScroll($element) {
        // по-умолчанию крутим к началу страницы
        // Но нужно скроллить к элементу, если он вне экрана
        var scroll = $element.offset().top;
        var name = $element.data(PJAX_SCROLLTO);
        var $container = null;
        scroll = $element.offset().top;
        if (name && ($container = findTargetContainer(name))) {
            scroll = $container.offset().top;
        }

        return scroll;
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
        var $modal = $element.closest(PJAX_MODAL);
        var closeModal = ($element.data(PJAX_CLOSE_MODAL) != undefined
                ? $element.data(PJAX_CLOSE_MODAL)
                : false)
            && $modal.length > 0;

        // события после выполнения pjax-запроса
        var pjaxEvent = $element.data(PJAX_CONTAINER_EVENT);
        // открываем модальное окно?
        var isModal = $element.data('pjax') != undefined && $element.data('pjax') === 'modal';
        // есть зависимости (контейнера) от модала?
        var related = $element.data(PJAX_CONTAINER_RELATION);
        // нужно закрывать модальное окно после редиректа?
        var closeRedirect = $element.data(PJAX_REDIRECT_CLOSE_MODAL);
        // нужно прокрутить к контейнеру после закрытия модала
        var scrollTo = $element.data(PJAX_SCROLLTO);

        // после выполненной операции
        target.one('pjax:complete', function (event, xhr, status, request) {
            if (pjaxEvent) {
                app.trigger(pjaxEvent, request);
            }
            // запрос в пределах модального окна
            if (isModal) {
                // устанавливаем зависимости модального окна
                related && $(PJAX_MODAL).data(PJAX_CONTAINER_RELATION, related);
                // позже закроем модал после редиректа
                closeRedirect && $(PJAX_MODAL).data(PJAX_REDIRECT_CLOSE_MODAL, true);
                // позже прокрутим к контейнеру после закрытия модала
                scrollTo && $(PJAX_MODAL).data(PJAX_SCROLLTO, scrollTo);
            }
            // закрываем модальное окно
            if (closeModal) {
                $modal.modal('hide');
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

        var flashes = $(event.target).find(PJAX_FLASH_SELECTOR);
        if (flashes) {
            $(PJAX_FLASH_CONTAINER).html(flashes.data(PJAX_FLASH));
        }

        app.initializeDom(event.target);

        hideLoadingIndicators();
    }

    function hideLoadingIndicators()
    {
        $('#ajax-loading').fadeOut(100);
        $('.contentLoading').fadeOut(100);
        $(document).find('button[data-loading-text]').button('reset');
    }

    function onPjaxBeforeReplace(event, contents, options) {
        if (options.redirectTarget) {
            var redirectedTo,
                redirectCookieName = 'autocrm_redirect_' + parsePjaxContainerSelector(options.context.selector);

            if (undefined !== (redirectedTo = cookie.get(redirectCookieName))) {
                cookie.expire(redirectCookieName);
                options.context = findTargetContainer(options.redirectTarget);

                if (PJAX_ROOT_CONTAINER_NAME == options.redirectTarget) {
                    _.extend(event.state, {
                        push: true,
                        url: redirectedTo,
                        container: options.context.selector
                    });
                    window.history.pushState(event.state, event.state.title, event.state.url);
                }

                var $modal = $(PJAX_MODAL);

                // TODO: доделать нормальный скролл после редиректа
                var scrollTo = null;
                if (scrollTo = $modal.data(PJAX_SCROLLTO)) {
                    $container = findTargetContainer(scrollTo);
                    var scroll = getPjaxScroll($container);
                    $('body').attr({scrollTop: scroll});
                    $modal.removeData(PJAX_SCROLLTO);
                }

                // если надо закрыть модальное окно после редиректа
                if ($modal.data(PJAX_REDIRECT_CLOSE_MODAL)) {
                    $modal.removeData(PJAX_REDIRECT_CLOSE_MODAL);
                    $modal.modal('hide');
                }

                // ищем флешки в ответе при редиректе
                if (Object.keys(contents).length) {
                    // положим флешки сюда
                    var $container = $(PJAX_FLASH_CONTAINER);
                    var items = _.values(_.pick(contents, function (value, key) {
                        return /\d+/.test(key);
                    }));
                    if (items.length) {
                        // смотрим как дата-saатрибут самго элемента
                        var flashes = $(items[0]).data(PJAX_FLASH);
                        if (flashes) {
                            $container.html(flashes);
                        }
                    }
                }
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
