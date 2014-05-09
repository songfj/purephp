(function($) {
    $(function() {
        $('.no-backgroundsize .bg-gallery-item-img, .no-backgroundsize .bg-cover').bgsize();
        $(window).load(function() {
            $("html").addClass('window-loaded');
            $(window).trigger('resize');
        });
    });
})(window.jQuery);
