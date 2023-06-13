/* globals j */

import $ from 'jquery';

export default {
    filter:function(){

    },
    markAs:function(o){
        if (!confirm('Are you sure you want to mark all the selected items as '+o.action+'?')) return;

        var ids = j.ui.table.getSelectedValues($('#'+o.elementId)).toString();
        if (ids == '') return alert('There are no items selected.');
        var tForm = $('<form />').attr({action:o.file,method:'post'}).css({display:'none'});
        tForm.append($('<input />').attr({type:'hidden',name:'ids',value:ids}));
        tForm.append($('<input />').attr({type:'hidden',name:'action',value:o.action}));
        tForm.appendTo(document.body);
        tForm.submit();
    },
    exportSelected:function(o){
        var ids = j.ui.table.getSelectedValues($('#'+o.elementId)).toString();
        if (ids == '') return alert('There are no items selected.');
        var tForm = $('<form />').attr({action:o.file,method:'post'}).css({display:'none'});
        tForm.append($('<input />').attr({type:'hidden',name:'ids',value:ids}));
        tForm.appendTo(document.body);
        tForm.submit();
    }
};
