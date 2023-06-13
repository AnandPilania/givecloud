/* globals j */

import $ from 'jquery';

export default {
    list:{
        filter:function(){

        },
        markAs:function(action){
            if (!confirm('Are you sure you want to mark all the selected items as '+action+'?')) return;

            var ids = j.ui.table.getSelectedValues($('#orderList')).toString();
            if (ids == '') return alert('There are no items selected.');
            var tForm = $('<form />').attr({action:'/jpanel/contributions/batch',method:'post'}).css({display:'none'});
            tForm.append($('<input />').attr({type:'hidden',name:'ids',value:ids}));
            tForm.append($('<input />').attr({type:'hidden',name:'action',value:action}));
            tForm.appendTo(document.body);
            tForm.submit();
        },
        exportSelected:function(file){
            var ids = j.ui.table.getSelectedValues($('#orderList')).toString();
            if (ids == '') return alert('There are no items selected.');
            var tForm = $('<form />').attr({action:file,method:'post',target:'_blank'}).css({display:'none'});
            tForm.append($('<input />').attr({type:'hidden',name:'ids',value:ids}));
            tForm.appendTo(document.body);
            tForm.submit();
        }
    },
    deleteOrder:function(){

        var deleteFn = function(){
            $('#OrderForm')
                .attr({action:'/jpanel/contributions/destroy'})
                .submit();
        };

        $.confirm('Are you sure you want to delete this contribution?', deleteFn, 'danger', 'fa-trash');
    },
};
