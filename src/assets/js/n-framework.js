/**
 * N-Framework helper.
 * Extend the plugin for needed functionality
 */
(function ($) {
    "use strict";

    if (!$) {
        return;
    }

    if ($.fn.nframework) {
        console.info("Waning, trying to initiate NFramework twice or more times.");

        return;
    }

    $.fn.nframework = {}

})(jQuery);