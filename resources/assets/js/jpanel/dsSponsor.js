/* globals j */

import $ from 'jquery';
import toastr from 'toastr';

export default {
    init:function($modal){

        $modal.find('.ds-sponsor-save').click(function(ev){
            ev.preventDefault();

            // is the sponsorship being ended
            if($('.end-sponsorship-fields').length){
                var ended_at = $("input[name=ended_at]").val();

                if(!ended_at){
                    toastr['error']('Ended on date required to end a sponsorship');
                    return;
                }
            }

            var sponsor_id = $modal.find('input[name=sponsor_id]').val();
            var member_id = $modal.find('select[name=member_id]').val();

            if ($modal.find('select[name=member_id]').length > 0 && ! member_id){
                toastr['error']('You must select a sponsor.');
                return;
            }

            var data = $modal.find('form').serializeArray();

            var url = sponsor_id ? '/jpanel/sponsor/' + sponsor_id : '/jpanel/sponsor';

            $modal.find('.modal-body, .modal-footer').remove();
            $modal.find('.modal-header').after($('<div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-4x"></i><br>Saving...</div>'));

            $.post(url, data, function (){
                if ($('#sponsors-list').length) {
                    $('#sponsors-list').DataTable().draw();
                    $modal.modal('hide');
                } else {
                    location.reload();
                }
            },'json');
        });

        $modal.find('.ds-sponsor-delete').click(function(ev){
            ev.preventDefault();

            if (!confirm('Are you sure you want to permanently delete this sponsor record?\n\nThis will not delete the supporter - only this link between the supporter and the sponsorship.')) return;

            var sponsor_id = $modal.find('input[name=sponsor_id]').val();

            $.delete('/jpanel/sponsor/'+sponsor_id, function (){
                location.reload(); //j.dsSponsor._load({id:sponsor.id});
            },'json');
        });

        $modal.find('.ds-end-sponsorship').click(function(ev){
            ev.preventDefault();
            $('.end-sponsorship-btn').addClass('hide');
            $('.end-sponsorship').removeClass('hide');
        });

    },
    show:function(params){

        // load modal
        var modal = j.templates.render('sponsorModalTmpl');
        $('body').append(modal);
        $(modal).modal();

        $(modal).on('hidden.bs.modal', function () {
            $(modal).remove();
        });

        j.dsSponsor._load(modal, params);

        return $(modal);
    },
    _load:function(modal, params) {
        var url;
        if (params.id)
            url = '/jpanel/sponsor/'+params.id;
        else if (params.sponsorshipId)
            url = '/jpanel/sponsor/add/'+params.sponsorshipId;
        else
            return;

        $.get(url, function(output) {
            var $body = $(modal).find('.modal-body');
            $body.parent().append($( $.trim(output) ));
            $body.remove();

            j.ui.formatSpecialFields();

            j.dsSponsor.init($(modal));
        });
    }
};
