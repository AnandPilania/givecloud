
import jQuery from 'jquery';
import j from '@app/jpanel';

/*
 * Scheduling and tagging grid layout
 * - fixed header option
 * - fixed column option
 */
(function ( $ ) {

    $.fn.timelinify = function(options) {

        // do not recontruct if its constructed
        if (this.hasClass('-timelinified')) return this;

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.

        }, options);

        // store settings
        this.data('_timelinify', settings);

        // lets get started
        _redraw(this);

        // lets get started
        // if we don't have a valid timeline_id, change state
        if (this.data('timeline-id') == '') {
            _state(this, 'disable');

        // otherwise, lets start the party
        } else
            _load(this);

        // mark as 'gridified' and return for chaining
        return this.addClass('-timelinified');
    };

    function _load ($this) {

        // empty timeline
        $this.find('.timelinify-posts ul.timeline').empty();

        // show loading state
        _state($this, 'loading');

        if (!$this.data('timeline-type') || !$this.data('timeline-id')) {
            return;
        }

        $.get('/jpanel/timeline/' + $this.data('timeline-type') + '/' + $this.data('timeline-id'),function(json){

            if (json.length === 0)
                _state($this, 'empty');

            else {
                _state($this, 'show');
                _populate($this, json);
            }
        },'json');

    }

    function _populate ($this, posts) {
        $.each(posts, function(i, post){
            _drawPost($this, post, i);
        });
    }

    function _drawPost ($this, post, ix) {

        // html template
        var el = $('<li id="timeline-post-' + post.id + '">' +
            '<div class="timeline-badge"><i class="fa ' + post.icon_class + '"></i></div>' +
            '<div class="timeline-panel">' +
                '<a href="#" class="pull-right timeline-edit"><i class="fa fa-pencil"></i> Edit</a>' +
                '<div class="timeline-heading">' +
                    '<h4 class="timeline-title">' + post.headline + '</h4>' +
                    '<p><small class="text-muted timeline-date">' + post.posted_by_string + '</small></p>' +
                '</div>' +
                '<div class="timeline-body"><p>' + post.message + '</p></div>' +
                '<div class="timeline-media"></div>' +
            '</div>' +
        '</li>').appendTo($this.find('.timelinify-posts ul.timeline'));

        if (post.is_private) {
            el.addClass('timeline-private');
            el.find('.timeline-title').append('<small class="text-muted">&nbsp;<i class="fa fa-eye-slash"></i> Private</small>');
        }

        var $mediaList = el.find('.timeline-media');

        if (post.media && post.media.length > 0) {
            $.each(post.media, function(i, md){
                if (md.thumbnail_url) {
                    $mediaList.append($('<a href="' + md.public_url + '" target="_blank" title="' + md.caption + '" class="timeline-thumb"><img src="' + md.thumbnail_url + '" style="height:50px; width:auto; margin:0px 1px 1px 0px;"></a>'));
                } else {
                    $mediaList.append($('<a href="' + md.public_url + '" target="_blank" title="' + md.caption + '" class="timeline-placeholder"><i class="fa fa-file-o fa-2x"></i></a>'));
                }
            });
        }

        el.find('.timeline-edit').click(function(ev){
            ev.preventDefault();
            _edit($this, post);
        });

        if (typeof ix !== undefined && !isNaN(ix))
            if ((ix+1) % 2 === 0)
                el.addClass('timeline-inverted');

        return el.data('_post', post);
    }

    function _modal ($this, post) {

        var modal = j.templates.render('timelinifyModalTmpl', window.timelinifyModalTmplData).appendTo('body');

        var postId = ((post.id)?post.id:'newPost');

        modal.attr('id', 'timeline-modal-'+post.id);

        // event - destroy modal
        modal.on('hidden.bs.modal', function () {
            modal.remove();
        });

        // form validation & submission
        modal.find('form').bootstrapValidator({
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            }
        }).on('success.form.bv', function(ev){ ev.preventDefault(); _save($this, modal, post); });

        // date and autofocus
        modal.on('shown.bs.modal', function () {
            modal.find('input.date').datepicker({ format: 'yyyy-mm-dd', autoclose:true });
            modal.find('input[name=headline]').focus();
            modal.find('.media-listing').attr('id', 'media-list-' + postId);
            modal.find('.media-upload')
                .data('media-collection-name', $this.data('timeline-type'))
                .data('media-list', '#media-list-' + postId)
                .data('media-parent-type', 'Ds\\Models\\Timeline')
                .data('media-parent-id', ((post.id)?post.id:null))
                .data('media-upload-container', '#'+modal.attr('id'))
                .medialy();
        });

        return modal;
    }

    function _edit ($this, post) {

        // open a new modal
        var modal = _modal($this, post);

        // populate modal
        modal.find('[name=headline]').val(post.headline);
        modal.find('[name=message]').val(post.message);
        modal.find('[name=tag]').val(post.tag);
        modal.find('[name=posted_on]').val(post.posted_on);

        if (post.is_private)
            modal.find('[name=is_private]').prop('checked', true);

        modal.find('.timeline-delete')
            .removeClass('hide')
            .click(function(ev){ ev.preventDefault(); _delete($this, modal, post); });

        // display modal
        modal.modal();
    }

    function _save ($this, $modal, post) {

        $modal.find('button[type=submit]').addClass('disabled').html('<i class="fa fa-spin fa-spinner"></i>');

        var params = $modal.find('form').serializeArray();

        // INSERT
        if (typeof post.id === 'undefined') {

            // make sure the new record is linked to this timeline
            params.push({name:'parent_type', value:$this.data('timeline-type')});
            params.push({name:'parent_id', value:$this.data('timeline-id')});

            $.ajax({
                url: '/jpanel/timeline',
                type: 'POST',
                data: params,
                success: function(){
                    $modal.modal('hide');
                    _load($this);
                }
            });

        // UPDATE
        } else {
            $.ajax({
                url: '/jpanel/timeline/' + post.id,
                type: 'POST',
                data: params,
                success: function(){
                    $modal.modal('hide');
                    _load($this);
                }
            });
        }

    }

    function _delete ($this, $modal, post) {
        if (confirm('Are you sure you want to delete this timeline update?')) {
            $modal.modal('hide');
            _state($this, 'loading');

            $.ajax({
                url: '/jpanel/timeline/' + post.id,
                type: 'DELETE',
                success: function(){
                    _load($this);
                }
            });
        }
    }

    function _redraw ($this) {

        // clear the contents
        $this.empty();

        // add button
        $('<div class="row text-center"><button type="button" class="btn btn-lg btn-success timelinify-add"><i class="fa fa-plus"></i> Add an Update</button></div>').appendTo($this);
        $this.find('.timelinify-add').click(function(ev){ ev.preventDefault(); _addPost($this); });

        // loading status
        $('<div class="text-muted text-center timelinify-empty hide" style="margin-top:30px;"><i class="fa fa-frown-o fa-5x"></i><br>The Timeline is Empty</div>').appendTo($this);

        // empty status
        $('<div class="text-muted text-center timelinify-loading hide" style="margin-top:30px;"><i class="fa fa-spin fa-spinner fa-5x"></i></div>').appendTo($this);

        // post container
        $('<div class="timelinify-posts hide"><ul class="timeline"></ul></div>').appendTo($this);
    }

    function _state ($this, state) {
        $this.find('.timelinify-empty, .timelinify-loading, .timelinify-posts').addClass('hide');
            $this.find('.timelinify-add').removeClass('hide');

        if (state === 'loading')
            return $this.find('.timelinify-loading').removeClass('hide');

        if (state === 'empty')
            return $this.find('.timelinify-empty').removeClass('hide');

        if (state === 'show')
            return $this.find('.timelinify-posts').removeClass('hide');

        if (state === 'disable') {
            $this.find('.timelinify-add').addClass('hide');
            return $this.find('.timelinify-empty').removeClass('hide');
        }
    }

    function _addPost ($this) {

        // open a new modal
        var modal = _modal($this, {});

        modal.find('[name=posted_on]').val(_today());

        // display modal
        modal.modal();
    }

    function _today () {
        var today = new Date();
        var yyyy = today.getFullYear().toString();
        var mm = (today.getMonth()+1).toString(); // getMonth() is zero-based
        var dd  = today.getDate().toString();
        return yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]); // padding
    }

}( jQuery ));
