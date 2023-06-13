import $ from 'jquery';

$.modal = function(settings) {
    var opts = $.extend({}, {
            'id'       : 'modal_' + Math.floor((Math.random() * 100000) + 1),
            'title'    : 'Loading...',
            'class'    : '',
            'body'     : '<div style="padding:20px; text-align:center;"><i class="fa fa-spinner fa-spin"></i></div>',
            'buttons'  : [
                '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'
            ],
            'backdrop' : true,
            'onOpen'   : null,
            'size'     : 'lg'
        }, settings);

    // load modal
    var $modal = $('<div class="modal fade '+opts.class+'" id="' + opts.id + '">' +
            '<div class="modal-dialog modal-'+opts.size+'">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '<h4 class="modal-title">'+ opts.title +'</h4>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        opts.body +
                    '</div>' +
                    '<div class="modal-footer">' +
                        opts.buttons.join('') +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>');

    $('body').append($modal);

    $modal.on('hidden.bs.modal', function () {
        $modal.remove();
    });

    $modal.on('show.bs.modal', function () {

        // velocity animation
        //$modal.find('.modal-dialog').velocity('transition.flipBounceXIn');
        $modal.find('.modal-dialog').velocity('callout.tada', {duration:300});
    });

    $modal.on('shown.bs.modal', function () {

        // autofocus
        $('#' + opts.id).find('*[autofocus]').first().focus();

        // onopen callback
        if (typeof opts.onOpen === 'function') opts.onOpen($modal);
    });

    $modal.modal({
        backdrop: opts.backdrop
    });

    if (opts.backdrop_colour) {
        $modal.data('bs.modal').$backdrop.css('background', opts.backdrop_colour);
    }

    return $modal;
};
