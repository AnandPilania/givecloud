/* globals j */

import $ from 'jquery';

export default {
    init:function(){
        j.user.toggleUserType();
    },
    onDelete:function() {
        var f = confirm('Are you sure you want to delete this user?');
        if (f) {
            document.user.action = '/jpanel/users/destroy';
            document.user.submit();
        }
    },
    toggleUserType:function(){
        if ($('#isAdminUser').attr('checked'))
            $('#permission_wrap').slideDown();
        else
            $('#permission_wrap').slideUp();
    },
    regenerateKey:function(id){
        var $wrap = $('#userApiToken .input-wrap');
        var $input = $('#userApiToken input[name=api_token]');
        var $button = $('#userApiToken button');
        $.post('/jpanel/users/' + id + '/regenerate-key').then(function(data) {
            $input.val(data.api_token).prop('type', 'text');
            $button.text('Regenerate key');
            $wrap.removeClass('hide');
        });
    }
};
