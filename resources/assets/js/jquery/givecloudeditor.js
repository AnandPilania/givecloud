import $ from 'jquery';
import createHtmlEditor  from '@app/tinymce/editor';

$.fn.givecloudeditor = function(opts) {
    return this.each(function() {
        if (! $(this).data('givecloudeditor')) {
            $(this).data('givecloudeditor', createHtmlEditor(this, opts));
        }
    });
};
