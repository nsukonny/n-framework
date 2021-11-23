/**
 * Extend N-Framework helper for ajax upload images to library
 */
(function ($) {
    "use strict";

    if (!$ || $.fn.nframework.ajax_loader) {
        return;
    }

    var loader = {
        init: function (options) {
            this.draw_form();
            this.events(options);
        },
        draw_form: function () {
            let form = $('#n-framework-ajax-loader');

            if (0 === form.length) {
                let formHtml = '<form action="#" method="post" encType="multipart/form-data" ' +
                    ' id="n-framework-ajax-loader" style="display: none;" >' +
                    '               <input type="file" name="nframework_ajax_loader" >' +
                    '           </form>';

                $('body').prepend(formHtml);
            }
        },
        events: function (options) {
            let loaderInput = $('#n-framework-ajax-loader input[name="nframework_ajax_loader"]'),
                ajax_loader = this;

            $('body').on('click', options.selectors, function () {
                loaderInput.data('target', $(this).data('target'));
                loaderInput.trigger('click');

                return false;
            });

            loaderInput.change(function () {
                ajax_loader.loader($(this), options);
            });
        },
        loader: function (loaderInput, options) {

            let files = loaderInput[0].files;

            if (0 < files.length) {
                let formData = new FormData();
                formData.append("file", files[0]);
                formData.append("action", "nframework_ajax_loader");
                formData.append("_ajax_nonce", nframework_ajax_loader._ajax_nonce);

                $.ajax({
                    type: "POST",
                    url: nframework_ajax_loader.ajax_url,
                    processData: false,
                    contentType: false,
                    data: formData,
                    beforeSend: function () {
                        if ($.isFunction(options.callbacks.beforeSend)) {
                            options.callbacks.beforeSend();
                        }
                    },
                    success: function (response) { //TODO move it to callback function
                        if (response.success) {
                            let input = $('#' + loaderInput.data('target')),
                                targets = $('.' + input.data('target'));

                            input.val(response.data.url);
                            input.trigger('change');

                            targets.each(function () {
                                let target = $(this);

                                target.html('<img src="' + response.data.url + '">');

                                target.removeClass('fas');
                                target.removeClass('fa');

                                for (let i = 0; i < target[0].classList.length; i++) {
                                    if (target[0].classList[i].startsWith("fa")) {
                                        target.removeClass(target[0].classList[i]);
                                    }
                                }
                            });
                        } else {
                            alert('Something wrong, try again');
                        }
                    },
                    error: function (request, status, error) {
                        if ($.isFunction(options.callbacks.error)) {
                            options.callbacks.error(request, status, error);
                        } else {
                            console.log(error);
                            alert(request.responseText);
                        }
                    },
                });
            }

        }
    }

    $.fn.nframework.ajax_loader = function (options) { //Load images to WP library without reloading the page

        loader.init(options);

        return this;
    };

})(jQuery);