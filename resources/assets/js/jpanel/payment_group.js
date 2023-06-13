/* globals j */

import $ from 'jquery';

export default {
    init : function () {
        j.payment_group.options.populate($.parseJSON($('#paymentOptionsJSON').html()));
    },
    options:{
        populate:function(data){
            var table = $('#payment_options-table');

            // loop through each
            $.each(data,function(i,e){
                var newRow = null,
                    data = $.extend({}, e); // create a object that will populate the template

                // correct the data for the template
                data['_dom_id'] = e.id;
                data['_isnew'] = 0;

                // populate row
                newRow = j.templates.render('paymentOptionsTmpl',data); // create the new row using the template and data
                newRow.appendTo(table); // append new row to form

                j.payment_group.options.onRowChange(e.id);
            });

            j.payment_group.options.onRowCountChange();
        },
        add:function(){
            var table = $('#payment_options-table'),
                newRow = null,
                data = {}; // create a object that will populate the template

            // correct the data for the template
            data['_dom_id'] = j.util.rand();
            data['id'] = '';
            data['_isnew'] = '1';
            data['sequence'] = '';
            data['is_recurring'] = '0';
            data['is_custom'] = '0';
            data['recurring_frequency'] = 'monthly';
            data['recurring_day'] = '';
            data['recurring_day_of_week'] = '';
            data['amount'] = '';

            // populate row
            newRow = j.templates.render('paymentOptionsTmpl',data); // create the new row using the template and data
            newRow.appendTo(table); // append new row to form
            j.payment_group.options.onRowChange(data['_dom_id']);
            j.payment_group.options.onRowCountChange();
        },
        onRowChange:function(id){
            var type = $('#payment_group_option_'+id+'_type').val(),
                recurring_options_wrap_el = $('#payment_group_option_'+id+'_recurring_options_wrap'),
                is_recurring_el = $('#payment_group_option_'+id+'_is_recurring'),
                is_custom_el = $('#payment_group_option_'+id+'_is_custom'),
                amount_wrap_el = $('#payment_group_option_'+id+'_amount_wrap'),
                amount_na_el = $('#payment_group_option_'+id+'_amount_na');

            if (type === 'fixed_onetime') {
                is_recurring_el.val(0);
                is_custom_el.val(0);
                amount_wrap_el.show();
                amount_na_el.hide();
                recurring_options_wrap_el.hide();
            } else if (type === 'fixed_recurring') {
                is_recurring_el.val(1);
                is_custom_el.val(0);
                amount_wrap_el.show();
                amount_na_el.hide();
                recurring_options_wrap_el.show();
            } else if (type === 'custom_onetime') {
                is_recurring_el.val(0);
                is_custom_el.val(1);
                amount_wrap_el.hide();
                amount_na_el.show();
                recurring_options_wrap_el.hide();
            } else if (type === 'custom_recurring') {
                is_recurring_el.val(1);
                is_custom_el.val(1);
                amount_wrap_el.hide();
                amount_na_el.show();
                recurring_options_wrap_el.show();
            }

            var recurring_frequency = $('#payment_group_option_'+id+'_recurring_frequency').val(),
                recurring_connector_el = $('#payment_group_option_'+id+'_recurring_connector'),
                recurring_day_el = $('#payment_group_option_'+id+'_recurring_day'),
                recurring_day_of_week_el = $('#payment_group_option_'+id+'_recurring_day_of_week');

            if (recurring_frequency === 'weekly' || recurring_frequency === 'biweekly') {
                recurring_day_el.hide();
                recurring_day_of_week_el.show();
                recurring_connector_el.html(' on ');
            } else {
                recurring_day_el.show();
                recurring_day_of_week_el.hide();
                recurring_connector_el.html(' on day ');
            }


        },
        onRowCountChange:function(){
            var table = $('#payment_options-table'),
                rows = table.find('tbody tr');

            // deail with single rows
            if (rows.length === 0) {
                j.payment_group.options.add();
                rows = table.find('tbody tr');
            }

            if (rows.length === 1) {
                table.find('.-hide-if-single-row').css({display:'none'});
            } else {
                table.find('th.-hide-if-single-row').css({display:'table-cell'});
                table.find('td.-hide-if-single-row').css({display:'table-cell'});
                table.find('a.-hide-if-single-row').css({display:'inline'});
            }
        },
        remove:function(id){
            var isnew = $('#payment_group-table-row-'+id+' input[name=\'payment_group_options['+id+'][_isnew]\']').val();

            if (isnew == 0) {
                if (!confirm('Are you sure you want to delete this row?')) { return; }
            }

            $('#payment_group-table-row-'+id).remove();

            if (isnew == 0) {
                $('#payment_group_form').append($('<input type="hidden" name="payment_group_options['+id+'][id]" value="'+id+'" />'));
                $('#payment_group_form').append($('<input type="hidden" name="payment_group_options['+id+'][_isdelete]" value="1" />'));
            }

            j.payment_group.options.onRowCountChange();
        }
    }
};
