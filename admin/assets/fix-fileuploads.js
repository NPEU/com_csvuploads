!function ($) {
    var $file_input = jQuery('.btn-file input[type=file]');

    console.log($file_input);

    $file_input.parent().contents().filter(function() {
            return this.nodeType === 3 && this.textContent.trim() != '';
        })
        .wrap('<span />')
        .end();

    $file_input.nextAll().appendTo($file_input.parent().parent().parent());
}(window.jQuery);
