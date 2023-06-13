import $ from 'jquery';

$.upgrade = function(message) {
    $.modal({
        'id' : 'record-delete-modal',
        'class' : 'modal-bright',
        'size' : 'md',
        'body' : message,
        'title' : '<i class="fa fa-upload"></i> Upgrade',
        'buttons' : [
            '<button type="button" class="btn btn-block btn-lg btn-upgrade"><i class="fa fa-upload"></i> Request an Upgrade</button>'
        ],
        'backdrop_colour': '#000000',
        'onOpen' : function (modalEl) {
            $(modalEl).find('button').click(function(){
                window.Intercom('showNewMessage', "Can you please upgrade my account?");
            });
        }
    });
};
