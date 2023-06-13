/* globals j */

import $ from 'jquery';
import toastr from 'toastr';

export default {
    init:function(){
        if ($('.ds-tribute').length == 0) return;

        $('.ds-tribute').not('.-ds-tribute-complete').each(function(i,el){
            $(el).click(function(ev){
                ev.preventDefault();

                // load modal
                var modal = j.templates.render('tributeModalTmpl');
                $('body').append(modal);
                $(modal).on('show.bs.modal', function () {
                    $(this).find('.modal-dialog').velocity('transition.flipYIn', {duration:500});
                });
                $(modal).modal();
                var $modal = $(modal);

                $modal.on('hidden.bs.modal', function () {
                    $modal.remove();
                });

                var receiptId = $(el).data('tribute-id');

                j.tribute.populate(receiptId, $modal);

                return $modal;
            });

            $(el).addClass('-ds-tribute-complete');
        });
    },
    populate:function(id, $modal){
        $.post('/jpanel/tributes/'+id+'/modal', null, function(output) {
            $modal.find('.modal-container').html(output);
            $modal.find('.tribute-edit-btn').bind('click',    {tribute_id:id}, j.tribute.onEdit);
            $modal.find('.tribute-notify-btn').bind('click',  {tribute_id:id}, function(e) { j.tribute.onNotify(e, $modal); });
            $modal.find('.tribute-destroy-btn').bind('click', {tribute_id:id}, function(e) { j.tribute.onDestroy(e, $modal); });
            $modal.find('.tribute-save-btn').bind('click',    {tribute_id:id}, function(e) { j.tribute.onSave(e, $modal); });
            $modal.find('.tribute-cancel-btn').bind('click',  {tribute_id:id}, j.tribute.onCancel);
        });
    },
    onDestroy:function(ev, $modal){

        var doDelete = function(){
            $modal.find('.modal-container').html('<div class="text-muted text-center top-gutter bottom-gutter"><i class="fa fa-spin fa-4x fa-circle-o-notch"></i></div>');

            $.post('/jpanel/tributes/'+ev.data.tribute_id+'/destroy', {}, function(tribute){
                toastr['success']('The tribute has been deleted successfully.');
                j.tribute.populate(tribute.id, $modal);
                $('#tributesDataTable').DataTable().draw();
            });
        };

        if (confirm('Are you sure you want to delete this tribute?'))
            doDelete();
    },
    onNotify:function(ev, $modal){

        $modal.find('.modal-container').html('<div class="text-muted text-center top-gutter bottom-gutter"><i class="fa fa-spin fa-4x fa-circle-o-notch"></i></div>');

        $.post('/jpanel/tributes/'+ev.data.tribute_id+'/notify', {}, function(tribute){
            toastr['success'](tribute.notify_email + ' has been notified successfully.');
            j.tribute.populate(tribute.id, $modal);
            $('#tributesDataTable').DataTable().draw();
        });

    },
    onEdit:function(){
        $('.tribute-edit, .tribute-details').toggleClass('hide');
    },
    onSave:function(ev, $modal){

        var data = $('.tribute-edit-form').serializeArray();

        $modal.find('.modal-container').html('<div class="text-muted text-center top-gutter bottom-gutter"><i class="fa fa-spin fa-4x fa-circle-o-notch"></i></div>');

        $.post('/jpanel/tributes/'+ev.data.tribute_id+'/edit', data, function(tribute){
            toastr['success']('Tribute for ' + tribute.name + ' has been successfully revised.');
            j.tribute.populate(tribute.id, $modal);
        });

    },
    onCancel:function(){
        $('.tribute-edit, .tribute-details').toggleClass('hide');
    }
};
