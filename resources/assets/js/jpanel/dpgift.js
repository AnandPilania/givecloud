/* globals j */

import $ from 'jquery';

export default {
    init:function(){},
    show:function(params){

        // load modal
        var modal = j.templates.render('giftModalTmpl', { gift_id: params.id });
        $('body').append(modal);
        $(modal).on('show.bs.modal', function () {
            $(this).find('.modal-dialog').velocity('transition.flipYIn', {duration:500});
        });
        $(modal).modal();

        $(modal).on('hidden.bs.modal', function () {
            $(modal).remove();
        });

        $.post('/jpanel/donors/gift', params, function(output) {
            $(modal).find('.modal-body').html(output);
        });

        return $(modal);
    }
};
