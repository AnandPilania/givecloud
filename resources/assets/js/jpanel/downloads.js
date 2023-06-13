/* globals j */

import $ from 'jquery';
import axios from 'axios';

export default {
    init:function(){
        j.downloads.loadDirList();
    },
    show:function(){
        var fileTmpl = j.templates.compile('fileTmpl');

        // load modal
        var modal = j.templates.render('filesModalTmpl');
        $('body').append(modal);
        var $modal = $(modal).modal();

        $modal.on('hidden.bs.modal', function () {
            $modal.remove();
        });

        $modal.on('click', '[data-download-choose]', function(){
            var file = $(this).parents('.fileRow-wrap').data('file');
            $('#'+modal.data('value_id')).val(file.id),
            $('#'+modal.data('label_id')).val(file.filename);
            $modal.modal('hide');
        });

        $modal.on('click', '[data-download-remove]', function(){
            var file = $(this).parents('.fileRow-wrap').data('file');
            j.downloads.remove(file.id);
        });

        j.downloads.populate();

        $modal.find('.dropzone').fileuploader({
            signEndpoint: '/jpanel/downloads/cdn/sign',
            doneEndpoint: '/jpanel/downloads/cdn/done',
            dropzone: true,
            previewParent: $modal.find('#filesWrp'),
            previewTemplate: j.templates.html('filePreviewTmpl'),
            onSubmit: function(upload) {
                upload.previewElement.find('[data-download-filename]').text(upload.filename);
            },
            onUpload: function(upload) {
                upload.previewElement.find('.waiting').hide();
                upload.previewElement.find('.transfer').show();
            },
            onComplete: function(upload, file) {
                var el = fileTmpl(file);
                el.data('file', file);
                upload.previewElement.replaceWith(el);
            },
            onError: function(upload) {
                if (upload) {
                    upload.previewElement.find('.transfer').hide();
                    upload.previewElement.find('.waiting').hide();
                    upload.previewElement.find('.error').show();
                }
            }
        });

        return $(modal);
    },
    populate:function(){
        var wrapper = $('#filesWrp').empty().append($('<div class="panel-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>'));
        var template = j.templates.compile('fileTmpl');

        $.get('/jpanel/downloads.json', function(data) {
            wrapper.empty();
            data.forEach(function(file) {
                var el = $(template(file));
                el.data('file', file);
                wrapper.append(el);
            });
        });
    },
    onUploadSuccess:function(){
        j.downloads.populate();
    },
    remove:function(id){
        if (!confirm('Are you sure you want to permanently delete this file?')) return;

        // hide row
        $('#fileRow-'+id).css('display','none');

        axios.delete('/jpanel/downloads/' + id);
    },
    chooseOne:function(params){
        var divalog = j.downloads.show();
        divalog.data(params);
        divalog.addClass('choose-mode');
    },
    onChoose:function(file_id){
        var row = $('#fileRow-'+file_id),
            modal = $('#dlg_downloads'),
            dest_val = $('#'+modal.data('value_id')),
            dest_label = $('#'+modal.data('label_id'));

        dest_val.val(row.data('fileid'));
        dest_label.val(row.data('filename'));

        $(modal).modal('hide');
    },
    showInIframe() {
        j.ui.iframeModal.show().then(($iframe) => {
            const $modal = $iframe[0].contentWindow.j.downloads.show()
            $modal.on('hidden.bs.modal', () => $iframe.remove());
        })
    },
};
