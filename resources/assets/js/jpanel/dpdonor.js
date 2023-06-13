/* globals j */

import $ from 'jquery';

export default {
    init:function($el){
        $el.click(function(ev){
            ev.preventDefault();

            j.dpdonor.show($el);
        });

        $el.addClass('dp-donor-init');
    },
    show:function($el){
        var donor_id = null;

        if (typeof $el.data === 'function') {
            if ($el.data('donor')) donor_id = $el.data('donor');
            if ($el.data('input')) donor_id = $('#'+$el.data('input')).val();
        } else {
            donor_id = $el.id;
        }

        // load modal
        var modal = j.templates.render('donorModalTmpl', { donor_id: donor_id });
        $('body').append(modal);
        $(modal).on('show.bs.modal', function () {
            $(this).find('.modal-dialog').velocity('transition.flipYIn', {duration:500});
        });
        $(modal).modal();

        $(modal).on('hidden.bs.modal', function () {
            $(modal).remove();
        });

        var data = {
            'id'         : donor_id,
            'first_name' : ($el.data('firstName')) ? $el.data('firstName') : null,
            'last_name'  : ($el.data('lastName')) ? $el.data('lastName') : null,
            'email'      : ($el.data('email')) ? $el.data('email') : null
        };

        $.post('/jpanel/donors/view', data, function(output) {
            $(modal).find('.modal-body').html(output);

            if ($el.data('input')) {
                $(modal).find('.donor-selection-tab').removeClass('hide');

                $(modal).find('.donor-chooser').each(function(i, el){
                    $(el).click(function(ev){
                        ev.preventDefault();

                        if ($el.data('input'))
                            $('#'+$el.data('input')).val($(this).data('donorId'));

                        $(modal).modal('hide');
                    });
                });
            }
        });

        return $(modal);
    }
};
