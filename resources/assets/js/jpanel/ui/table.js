/* globals j */

import $ from 'jquery';

export default {
    init:function(){
        j.ui.table.configCheckboxes();
        $.dsTxn();
    },
    configCheckboxes:function(){
        $('table').each(function(i,e){
            var master = $(e).find('input.master');
            var slaves = $(e).find('input.slave');
            master.click(function(){
                slaves.prop('checked', $(this).prop('checked'));
            });
            slaves.click(function(){
                if (!$(this).attr('checked')) { master.removeAttr('checked'); }
            });
        });
    },
    getSelectedValues:function($table){
        var checkboxes = $table.find('input.slave:checked');
        var vals = [];
        checkboxes.each(function(i,e){ vals.push($(e).val()); });
        return vals;
    }
};
