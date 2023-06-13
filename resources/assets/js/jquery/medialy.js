
import jQuery from 'jquery';

/*
 *

(id, parent_type, parent_id, is_private, uid, public_url, file_name, caption, extension, size, mime_type, thumbnail_id, created_at, created_by, updated_at, updated_by)

 * - fixed header option
 * - fixed column option
 */
(function ( $ ) {

    var $this,
        $list,
        $uploadContainer,
        options;

    $.fn.medialy = function(opts) {
        options = opts;

        // do not recontruct if its constructed
        if (this.hasClass('-mediafied')) return this;

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.

        }, options);

        // save references
        $this = this;

        // store settings
        $this.data('_media', settings);

        // lets get started
        init();

        // mark as 'gridified' and return for chaining
        return $this.addClass('-mediafied');
    };

    function init () {

        // draw anonymous file upload form
        $list = $($this.data('media-list'));
        $uploadContainer = $($this.data('media-upload-container'));

        $this.fileuploader({
            collectionName: $this.data('media-collection-name'),
            dropzone: $uploadContainer.get(),
            previewParent: $list,
            previewTemplate: `
                <div class="file">
                    <div class="file-status"></div>
                    <div class="file-filename"><i class="fa fa-file-o fa-fw"></i> <span></span></div>
                </div>
            `,
            onDragEnter() {
                $uploadContainer.addClass('is-dragover');
                if ($list.find('.media-drop-here').length == 0) {
                    $list.prepend($('<div class="media-drop-here"><i class="fa fa-3x fa-download"></i><br>DROP FILES HERE</div>'));
                }
            },
            onDragLeave() {
                $uploadContainer.removeClass('is-dragover');
            },
            onDrop() {
                $uploadContainer.removeClass('is-dragover');
                $list.find('.media-drop-here').remove();
            },
            onSubmit(upload) {
                upload.previewElement.find('.file-filename span').text(upload.filename);
            },
            onUpload: function(upload) {
                upload.previewElement.addClass('--uploading');
                upload.previewElement.find('.file-status').html('0%');
                upload.previewElement.find('.fa').removeClass('fa-file-o').addClass('fa-spin fa-spinner');
            },
            onProgress(upload, progress) {
                if (progress.percentUploaded < 100) {
                    upload.previewElement.find('.file-status').text(progress.percentUploaded +"%");
                } else {
                    upload.previewElement.find('.file-status').text("Processing...");
                }
            },
            onComplete: function(upload, file) {
                upload.previewElement.removeClass('--uploading');
                upload.previewElement.addClass('--uploaded');
                addMedia(file, upload.previewElement);
                upload.previewElement.remove();
            },
            onError(upload, errorReason) {
                if (upload) {
                    upload.previewElement.removeClass('--uploading');
                    upload.previewElement.addClass('--uploaded');
                    upload.previewElement.find('.file-status').html(errorReason || 'Error. Try again.');
                }
            }
        });

        // if we have existing media, load it
        if (typeof options === 'object' && options.media) {
            addAllMedia(options.media);

        // load existing media from server
        } else if ($this.data('media-parent-id')) {
            loadAllMedia();
        }
    }

    function loadAllMedia () {

        $this.append('<span class="--spinner">&nbsp;<i class="fa fa-spin fa-spinner fa-fw"></i></span>');

        $.ajax({
            url: '/jpanel/media/list',
            type: 'post',
            data: {
                'parent_id'   : $this.data('media-parent-id'),
                'parent_type' : $this.data('media-parent-type'),
                'collection_name' : $this.data('media-collection-name'),
            },
            dataType: 'json',
            complete: function() {
                $this.find('.--spinner').remove();
            },
            success: function(allMedia) {
                addAllMedia(allMedia);
            },
            error: function() { }
        });
    }

    function addAllMedia (allMedia) {
        $list.empty();
        $.each(allMedia, function(i, media){
            addMedia(media);
        });
    }

    function addMedia (media, $after_el) {
        var $itm;
        if (typeof $after_el !== 'undefined') {
            $itm = $(_mediaHtml(media)).insertAfter($after_el);
        } else {
            $itm = $(_mediaHtml(media)).appendTo($list);
        }
        $itm.attr('id', 'media-' + media.id);
        $itm.data('media', media);

        var $thumb = $itm.find('.media-thumb');

        if (media.thumbnail_url) {
            $thumb.css({
                'background-image' : "url('"+media.thumbnail_url+"')"
            });
        } else {
            $thumb.html('<i class="fa fa-file fa-2x"><i>');
        }

        $itm.find('.media-delete').on('click', function(){
            deleteMedia(media);
        });
    }

    function deleteMedia (media) {
        $list.find('#media-'+media.id).remove();

        $.ajax({
            url: '/jpanel/media/'+media.id+'/destroy',
            type: 'post',
            dataType: 'json',
            complete: function() { },
            success: function() { },
            error: function() { }
        });
    }

    function _mediaHtml (media) {
        return '<div class="media">' +
            '<input class="media-id-in" type="hidden" name="media[' + media.id + '][id]" value="' + media.id + '">' +
            '<input class="media-public_url-in" type="hidden" name="media[' + media.id + '][public_url]" value="' + media.public_url + '">' +
            '<a href="' + media.public_url + '" target="_blank"><div class="media-thumb"></div></a>' +
            '<div class="media-meta">' +
                '<div class="media-caption"><input type="text" placeholder="Caption (optional)" class="media-caption-in form-control" maxlength="191" name="media[' + media.id + '][caption]" value="' + (media.caption||'') + '"></div>' +
                '<div class="media-bumper"><div class="media-delete text-danger"><i class="fa fa-trash"></i> Delete</div>' + media.filename + ' (' + _fileSize(media.size) + ')</div>' +
            '</div>' +
        '</div>';
    }

    function _fileSize (bytes) {
        var si = true;
        var thresh = si ? 1000 : 1024;
        if(Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        var units = si
            ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
            : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
        var u = -1;
        do {
            bytes /= thresh;
            ++u;
        } while(Math.abs(bytes) >= thresh && u < units.length - 1);
        return bytes.toFixed(1)+' '+units[u];
    }
}( jQuery ));
