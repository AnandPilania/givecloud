import $ from 'jquery';
import FileUploader from '@app/cdn/uploader';

$.fn.fileuploader = function(opts) {
    return this.each(function() {
        if (! $(this).data('fileuploader')) {
            $(this).data('fileuploader', new FileUploader(this, opts));
        }
    });
};
