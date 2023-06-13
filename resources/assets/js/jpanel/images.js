/* globals j */

import $ from 'jquery';
import { debounce } from 'lodash';

let searchQuery = ''

export default {
    init:function(){
        j.images.loadDirList();

        var imageLibrary = $('#imageLibrary');
        if (imageLibrary.length) {
            j.images.setupUploader(imageLibrary);
        }
    },
    setupUploader: function($container){
        $container.find('.dropzone').fileuploader({
            dropzone: true,
            previewParent: $container.find('.imageThumbWrp'),
            previewTemplate: j.templates.html('imagePreviewTmpl'),
            onUpload: function(upload) {
                upload.previewElement.find('.waiting').hide();
                upload.previewElement.find('.transfer').show();
            },
            onComplete: function(upload, media) {
                upload.previewElement.attr('id', 'imgThumb-' + media.id);
                upload.previewElement.data('media', media);
                upload.previewElement.find('.transfer').hide();
                upload.previewElement.find('a[data-images-view]').attr('href', media.public_url);
                upload.previewElement.find('.imgThumb-background').css('background-image', 'url(' + media.thumbnail_url + ')');
                upload.previewElement.find('.imgThumb').attr('title', media.filename).show();
            },
            onError: function(upload) {
                if (upload) {
                    upload.previewElement.find('.transfer').hide();
                    upload.previewElement.find('.waiting').hide();
                    upload.previewElement.find('.imgThumb-error').show().find('span');
                }
            }
        });
    },
    show:function(multiple, callback){

        // load modal
        var modal = j.templates.render('imagesModalTmpl');
        $('body').append(modal);
        var $modal = $(modal).modal();

        $modal.css('z-index',9999999999);

        $modal.on('hidden.bs.modal', function () {
            $modal.remove();
        });

        $modal.find('#imageThumbWrp').height($(window).height()-446);
        $(window).resize(function(){ $modal.find('#imageThumbWrp').height($(window).height()-446); });

        const populateImages = debounce(j.images.populate, 250);

        $modal.find('.img-library-search input').keyup(function(){
            var string = $.trim($(this).val());

            if (string !== searchQuery) {
                searchQuery = string
                populateImages();
            }
        });

        function chooseMedia() {
            var selected = $modal.find('.imgThumb-wrap.selected').toArray().map(function(el){
                return $(el).data('media');
            });
            $modal.modal('hide');
            callback(multiple ? selected : selected[0]);
        }

        var $insertBtn = $modal.find('button[data-insert-into-post]').on('click', chooseMedia);
        var $galleryBtn = $modal.find('button[data-create-a-new-gallery]').on('click', chooseMedia);

        if (callback) {
            $modal.on('click', '.imgThumb', function(){
                $(this).parents('.imgThumb-wrap').toggleClass('selected');
                if (multiple) {
                    switch ($modal.find('.imgThumb-wrap.selected').length) {
                        case 0:  $insertBtn.hide(); $galleryBtn.hide(); break;
                        case 1:  $insertBtn.show(); $galleryBtn.hide(); break;
                        default: $insertBtn.hide(); $galleryBtn.show();
                    }
                } else {
                    chooseMedia();
                }
            });
        }

        $modal.on('click', '[data-images-remove]', function(){
            var media = $(this).parents('.imgThumb-wrap').data('media');
            j.images.onDelete(media.id, media.filename, media.filename);
        });

        j.images.populate();
        j.images.setupUploader($modal);

        return $modal;
    },
    populate:function(page = 1){
        var wrapper = $('#imageThumbWrp').empty().append($('<div class="panel-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>'));
        var imageTemplate = j.templates.compile('imageThumbTmpl');
        var paginationTemplate = j.templates.compile('paginationTmpl');

        $.get('/jpanel/images/directory_listing', { query: searchQuery, page }, function(data) {
            wrapper.empty();
            data.data.forEach((image) => {
                var el = $(imageTemplate(image));
                el.data('media', image);
                wrapper.append(el);
            });

            data.previous.url = data.previous.is_link ? `javascript:j.images.populate(${data.current_page - 1});` : '#';
            data.next.url = data.next.is_link ? `javascript:j.images.populate(${data.current_page + 1});` : '#';

            data.parts.forEach((part) => {
                part.title = part.title === '&hellip;' ? '…' : part.title;
                part.url = part.is_link ? `javascript:j.images.populate(${part.title});` : '#';
            });

            wrapper.append(paginationTemplate({ paginator: data }));
        });
    },
    chooseOne:function(multiple, callback){
        var divalog = j.images.show(multiple, callback);
        divalog.addClass('choose-mode');
        divalog.find('.modal-title').append($('<small>&nbsp;Click the image you want to use.</small>'));
    },
    loadDirList:function(){
        $('#dlg_images div.imgList').load('/jpanel/images/directory_listing');
    },
    onFileClick:function(path){
        $('#dlg_images div.imgPath').html('<strong>URL: </strong><input type="text" style="width:300px" value="'+path+'"/>');
        $('#dlg_images div.imgPreview').css({display:'block'}).html('<img src="'+path+'" />');
    },
    onDelete:function(id,filename){
        if (!confirm('Are you sure you want to delete this image ('+filename+')?')) return;

        // hide image
        $('#imgThumb-' + id).remove();

        j.images.deleteImage(id);
    },
    deleteImage:function(id){
        $.post('/jpanel/images/destroy', {id: id});
    },
    showInIframe() {
        j.ui.iframeModal.show().then(($iframe) => {
            const $modal = $iframe[0].contentWindow.j.images.show()
            $modal.on('hidden.bs.modal', () => $iframe.remove());
        })
    },
};
