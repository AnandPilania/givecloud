import $ from 'jquery';

$.confirm = function(message, action, modal_class, icon, backdrop_colour) {
    if (typeof modal_class === 'undefined') modal_class = 'default';
    if (typeof icon === 'undefined') icon = 'fa-question-circle';

    if (!backdrop_colour) {
        if (modal_class === 'danger') backdrop_colour = '#690202';
        if (modal_class === 'warning') backdrop_colour = '#fcf8e3';
    }

    var modal = $.modal({
        'id' : 'record-delete-modal',
        'class' : 'modal-'+modal_class,
        'size' : 'sm',
        'body' : message,
        'title' : '<i class="fa ' + icon + '"></i> Confirm',
        'buttons' : [
            '<button type="button" class="btn btn-' + modal_class + ' confirm-do" data-dismiss="modal"><i class="fa fa-check"></i> Yes</button>',
            '<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> No</button>'
        ],
        'backdrop_colour': backdrop_colour,
        'onOpen' : function (modalEl) {
            if (typeof action === 'function') {
                $(modalEl).find('.confirm-do').click(function(){
                    action(modal);
                });
            }
        }
    });
};
