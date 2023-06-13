/* globals j */

import $ from 'jquery';

export default {
    init:function(){
        if ($('#page-form').length == 0) return false;

        $('#type').on('change', j.page.toggleCategory);

        j.page.toggleCategory();

        // autosave every 60 seconds
        if ($('#page-form').data('autosave')) {
            setInterval(j.page.autosave, 60000);
        }
    },
    autosave() {
        $.ajax({
            type: 'post',
            url: '/jpanel/pages/autosave',
            dataType: 'json',
            data: $('#page-form').gc_serializeArray(),
        });
    },
    toggleCategory:function(){
        var page_type = $('#type').val();

        if (page_type == 'category')
            $('#category_id_wrap').removeClass('hidden');
        else
            $('#category_id_wrap').addClass('hidden');
    },
    onDelete:function() {
        var f = confirm('Are you sure you want to delete this page?');
        if (f) {
            document.posting.action = '/jpanel/pages/destroy';
            document.posting.submit();
        }
    },
    toggleMore:function(){
        if ($('.moreOptions_wrap').css('display') == 'none') {
            $('.moreOptions_wrap').slideDown();
            $('#moreOptions_button').slideUp();
        }
    },
    validate:function(){
        return true;
    }
};
