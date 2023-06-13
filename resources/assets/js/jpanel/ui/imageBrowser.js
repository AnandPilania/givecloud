/* globals j */

import $ from 'jquery';

export default {
    init:function(){
        $('.image-browser').on('click', j.ui.imageBrowser.browse);
        $('.image-browser-clear').on('click', j.ui.imageBrowser.clear);

        $('.clear-field').on('click', function(e) {
            e.preventDefault();
            $($(this).data('target')).val('');
        });
    },
    browse:function(ev){
        ev.preventDefault();
        var that = $(this);
        j.images.chooseOne(false, function(media){ j.ui.imageBrowser.onChoose(media.public_url, that, media); });
    },
    clear:function(ev){
        ev.preventDefault();
        var element = $(this).siblings('.image-browser');

        if (element.data('image-browser-output')) {
            $('#' + element.data('image-browser-output')).val('');
        }
        if (element.data('filename')) {
            $(element.data('filename')).val('');
        }
        if (element.data('input')) {
            $(element.data('input')).val('');
        }
        if (element.data('media-filename')) {
            $(element.data('media-filename')).val('');
        }
        if (element.data('media-id')) {
            $(element.data('media-id')).val('');
        }
        if (element.data('preview')) {
            $(element.data('preview')).css('background-image','none');
        }
    },
    onChoose:function(url, element, media){
        element = $(element);
        if (element.data('image-browser-output')) {
            $('#' + element.data('image-browser-output')).val(url);
        }
        if (element.data('filename')) {
            $(element.data('filename')).val(media.filename);
        }
        if (element.data('input')) {
            $(element.data('input')).val(media.id);
        }
        if (element.data('media-filename')) {
            $(element.data('media-filename')).val(media.filename);
        }
        if (element.data('media-id')) {
            $(element.data('media-id')).val(media.id);
        }
        if (element.data('preview')) {
            $(element.data('preview')).css('background-image',"url('"+url+"')");
        }
        $(element).trigger('onImageChosen', [url, element, media]);
    }
};
