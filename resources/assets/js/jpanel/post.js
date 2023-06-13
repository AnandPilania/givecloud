/* globals j */

import $ from 'jquery';

export default {
    options:{
        all:['embedcode','filepath','body','location','sequence','url','expirydatetime','fineprint','misc1','misc2','misc3','author','length_milliseconds','length_formatted'],
        video:['embedcode','body'],
        audio:['filepath','body','author','length_milliseconds','length_formatted'],
        blog:['body'],
        event:['location','body','misc1','expirydatetime'], //misc1=speaker
        slide:['sequence','filepath','url','expirydatetime'],
        coupon:['expirydatetime','fineprint']
    },
    init:function(){
        var o = $('#type').val();
        j.post.configureFields(o);
    },
    configureFields:function(option){
        if (typeof j.post.options[option] == 'undefined') option = 'blog';
        $.each(j.post.options.all,function(i,v){ $('#wrp_'+v).css({display:'none'}); });
        $.each(j.post.options[option],function(i,v){ $('#wrp_'+v).css({display:'block'}); });
    },
    onTypeChange:function(){
        var o = $('#type').val();
        j.post.configureFields(o);
    },
    removeFromList:function(id){
        if (!confirm('Are you sure you want to delete this post?')) return;

        var post_element = $('#feed_list-post-'+id);

        post_element.append($('<div class="feed_list-cover" />')).animate({opacity:0.5});

        var error_fn = function () {
            alert('Delete failed.  Please try again.');
            post_element.clearQueue().animate({opacity:1});
            $('#feed_list-post-'+id+' .feed_list-cover').remove();
        }

        $.ajax({
            type:'post',
            url:'/jpanel/feeds/posts/destroy',
            dataType:'json',
            data:{
                id:id
            },
            success:function(d){
                if (d.success) {
                    post_element.slideUp(function(){
                        $(this).remove();
                    });
                } else
                    error_fn();
            },
            error:function(){
                error_fn();
            }
        });
    }
};
