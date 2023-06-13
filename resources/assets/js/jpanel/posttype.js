/* globals j */

import $ from 'jquery';

export default {
    init:function () {
        j.posttype.onPodcastChange();
    },
    toggleMore:function(){
        if ($('.moreOptions_wrap').css('display') == 'none') {
            $('.moreOptions_wrap').slideDown();
            $('#moreOptions_button').slideUp();
        }
    },
    onPodcastChange:function(){
        var val = $('#isitunes').val();

        if (val === '1') {
            $('#podcast_wrp').removeClass('hidden');
        } else {
            $('#podcast_wrp').addClass('hidden');
        }
    }
};
