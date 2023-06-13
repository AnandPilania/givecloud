/* globals j */

import $ from 'jquery';
import toastr from 'toastr';

export default {
    init:function(){
        if ($('.ds-tax-receipt').length == 0) return;

        $('.ds-tax-receipt').not('.-ds-tax-receipt-complete').each(function(i,el){
            $(el).click(function(ev){
                ev.preventDefault();

                // load modal
                var modal = j.templates.render('taxReceiptModalTmpl');
                $('body').append(modal);
                $(modal).on('show.bs.modal', function () {
                    $(this).find('.modal-dialog').velocity('transition.flipYIn', {duration:500});
                });
                $(modal).modal();
                var $modal = $(modal);

                $modal.on('hidden.bs.modal', function () {
                    $modal.remove();
                });

                var receiptId = $(el).data('tax-receipt-id');

                j.taxReceipt.populate(receiptId, $modal);

                return $modal;
            });

            $(el).addClass('-ds-tax-receipt-complete');
        });
    },
    selectionMode:'id',
    selection() {
        if (j.taxReceipt.selectionMode === 'filter') {
            return j.ui.datatable.filterValues('table.dataTable');
        } else {
            var selected = j.taxReceipt.dataTable.rows({ selected: true }).data();
            return {
                ids: selected.map(function(o){ return o[0]; }).toArray()
            };
        }
    },
    selectSearch:function(){
        j.taxReceipt.selectionMode = 'filter';
        $('#taxReceiptsDataTableSearchSelection div').hide().filter('.selected').show();
    },
    clearSelection:function(){
        j.taxReceipt.dataTable.rows().deselect();
    },
    onSelectionChange:function(){
        var $element = $('#taxReceiptsDataTableSearchSelection');
        var selected = j.taxReceipt.dataTable.rows({ selected: true }).data();
        if (selected.length === 50) {
            $element.find('div').hide().filter('.notselected').show();
            $element.show();
        } else {
            j.taxReceipt.selectionMode = 'id';
            $element.hide();
        }
    },
    populate:function(id, $modal){
        $.post('/jpanel/tax_receipt/'+id+'/modal', null, function(output) {
            $modal.find('.modal-container').html(output);
            $modal.find('a[data-toggle="popover"]').popover({
                'container' : $('body'),
                'html' : true
            });
            $modal.find('.tax-receipt-revise-btn').bind('click', {receipt_id:id}, j.taxReceipt.onRevise);
            $modal.find('.tax-receipt-notify-btn').bind('click', {receipt_id:id}, function(e) { j.taxReceipt.onNotify(e, $modal); });
            $modal.find('.tax-receipt-issue-btn').bind('click', {receipt_id:id}, function(e) { j.taxReceipt.onIssue(e, $modal); });
            $modal.find('.tax-receipt-void-btn').bind('click', {receipt_id:id}, function(e) { j.taxReceipt.onVoid(e, $modal); });
            $modal.find('.tax-receipt-save-btn').bind('click', {receipt_id:id}, function(e) { j.taxReceipt.onSaveRevision(e, $modal); });
            $modal.find('.tax-receipt-cancel-btn').bind('click', {receipt_id:id}, j.taxReceipt.onCancelRevision);
        });
    },
    onRevise:function(){
        $('.tax-receipt-revise, .tax-receipt-details').toggleClass('hide');
    },
    onSaveRevision:function(ev, $modal){

        var data = $('.tax-receipt-revise-form').serializeArray();

        $modal.find('.modal-container').html('<div class="text-muted text-center top-gutter bottom-gutter"><i class="fa fa-spin fa-4x fa-circle-o-notch"></i></div>');

        $.post('/jpanel/tax_receipt/'+ev.data.receipt_id+'/revise', data, function(receipt){
            toastr['success']('Receipt ' + receipt.number + ' has been successfully revised.');
            j.taxReceipt.populate(receipt.id, $modal);
        });

    },
    onIssue:function(ev, $modal){

        $modal.find('.modal-container').html('<div class="text-muted text-center top-gutter bottom-gutter"><i class="fa fa-spin fa-4x fa-circle-o-notch"></i></div>');

        $.post('/jpanel/tax_receipt/'+ev.data.receipt_id+'/issue', {}, function(receipt){
            toastr['success']('Receipt ' + receipt.number + ' has been issued successfully.');
            j.taxReceipt.populate(receipt.id, $modal);
        });

    },
    onVoid:function(ev, $modal){

        var data = $('.tax-receipt-revise-form').serializeArray();

        $modal.find('.modal-container').html('<div class="text-muted text-center top-gutter bottom-gutter"><i class="fa fa-spin fa-4x fa-circle-o-notch"></i></div>');

        $.post('/jpanel/tax_receipt/'+ev.data.receipt_id+'/void', data, function(receipt){
            toastr['success']('Receipt ' + receipt.number + ' has been voided successfully.');
            if (!receipt.deleted_at) {
                j.taxReceipt.populate(receipt.id, $modal);
            }
        });

    },
    onNotify:function(ev, $modal){

        var data = $('.tax-receipt-revise-form').serializeArray();

        $modal.find('.modal-container').html('<div class="text-muted text-center top-gutter bottom-gutter"><i class="fa fa-spin fa-4x fa-circle-o-notch"></i></div>');

        $.post('/jpanel/tax_receipt/'+ev.data.receipt_id+'/notify', data, function(receipt){
            if (receipt.email !== null)
                toastr['success'](receipt.email + ' has been notified successfully.');
            else
                toastr['success']('This tribute has been marked as notified.');

            j.taxReceipt.populate(receipt.id, $modal);
        });

    },
    onCancelRevision:function(){
        $('.tax-receipt-revise, .tax-receipt-details').toggleClass('hide');
    }
};
