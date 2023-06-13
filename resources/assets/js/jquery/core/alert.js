import $ from 'jquery';

$.alert = function(message, modal_class, icon, backdrop_colour) {
    if (typeof modal_class === 'undefined') modal_class = 'default';
    if (typeof icon === 'undefined') icon = 'fa-question-circle';

    if (!backdrop_colour) {
        if (modal_class === 'danger') backdrop_colour = '#690202';
        if (modal_class === 'warning') backdrop_colour = '#fcf8e3';
    }

    $.modal({
        'id' : 'record-delete-modal',
        'class' : 'modal-'+modal_class,
        'size' : 'sm',
        'body' : message,
        'title' : '<i class="fa ' + icon + '"></i> Alert',
        'buttons' : [
            '<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>'
        ],
        'backdrop_colour': backdrop_colour || '#690202'
    });
};
