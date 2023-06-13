/* globals j */

import $ from 'jquery';
import _ from 'lodash';
import toastr from 'toastr';

export default {
    init: function(){
        $('#hook-deliveries').on('click', '.hook-delivery-guid', j.hooks.toggleDelivery);
        $('#hook-deliveries').on('click', '.ellipsis-expander',  j.hooks.toggleDelivery);
        $('#hook-deliveries').on('click', '.hook-delivery-actions button[name=redeliver]', j.hooks.redeliver);
    },
    add: function(){
        var $form = $('#hook-form');
        var data = {
            payload_url: $form.find('input[name=payload_url]').val(),
            content_type: $form.find('select[name=content_type]').val(),
            events: $form.find('input[name="events[]"]:checked').map(function () {
                return $(this).val();
            }).get(),
            secret: $form.find('input[name=secret]').val(),
            active: $form.find('input[name=active]:checked').val()
        };
        $.post('/jpanel/settings/hooks', data).then(function(res){
            location.href = '/jpanel/settings/hooks/' + res.hook_id;
        }).catch(function(err) {
            toastr.error(_.entries(err.responseJSON.errors)[0][1]);
        });
    },
    save: function(hook_id){
        var $form = $('#hook-form');
        var data = {
            payload_url: $form.find('input[name=payload_url]').val(),
            content_type: $form.find('select[name=content_type]').val(),
            events: $form.find('input[name="events[]"]:checked').map(function () {
                return $(this).val();
            }).get(),
            secret: $form.find('input[name=secret]').val(),
            active: $form.find('input[name=active]:checked').val()
        };
        $.put('/jpanel/settings/hooks/' + hook_id, data).then(function(){
            location.href = '/jpanel/settings/hooks/' + hook_id;
        }).catch(function(err) {
            toastr.error(_.entries(err.responseJSON.errors)[0][1]);
        });
    },
    delete: function(hook_id, payload_url){
        var $modal = $('#hook-delete-modal');
        if (payload_url) {
            $modal.find('.payload-url').text('('+payload_url+')');
        } else {
            $modal.find('.payload-url').empty();
        }
        if (!$modal.data('bs.modal')) {
            $modal.on('click', 'button[name=delete]', function(){
                $modal.modal('hide');
                $.delete('/jpanel/settings/hooks/' + hook_id + '/destroy').then(function(){
                    location.href = '/jpanel/settings/hooks';
                });
            });
        }
        $modal.modal('show');
        $modal.data('bs.modal').$backdrop.css('background', '#690202');
    },
    enableInsecureSSL: function(hook_id){
        $.put('/jpanel/settings/hooks/' + hook_id, {insecure_ssl: 0}).then(function(){
            location.href = '/jpanel/settings/hooks/' + hook_id;
        });
    },
    disableInsecureSSL: function(hook_id){
        var $modal = $('#hook-ssl-modal');
        if (!$modal.data('bs.modal')) {
            $modal.on('click', 'button[name=delete]', function(){
                $modal.modal('hide');
                $.put('/jpanel/settings/hooks/' + hook_id, {insecure_ssl: 1}).then(function(){
                    location.href = '/jpanel/settings/hooks/' + hook_id;
                });
            });
        }
        $modal.modal('show');
        $modal.data('bs.modal').$backdrop.css('background', '#690202');
    },
    toggleDelivery: function(event){
        event.preventDefault();
        var $parent = $(this).parents('.list-group-item');
        var $container = $parent.find('.hook-delivery-details');
        if ($container.children().length) {
            $container.toggle();
        } else {
            var delivery_id = $parent.data('delivery-id');
            var $spinner = $parent.find('.hook-delivery-loading').show();
            $.get('/jpanel/settings/hook-deliveries/' + delivery_id, function(res){
                $spinner.hide();
                $container.html(res).show().find('a[data-toggle="tab"]').tab();
            });
        }
    },
    redeliver: function(event){
        event.preventDefault();
        var $parent = $(this).parents('.list-group-item');
        var $modal = $('#hook-redeliver-modal');
        if (!$modal.data('bs.modal')) {
            $modal.on('click', 'button[name=redeliver]', function(){
                $modal.modal('hide');
                var $container = $parent.find('.hook-delivery-details');
                $container.empty();
                var $spinner = $parent.find('.hook-delivery-loading').show();
                var delivery_id = $parent.data('delivery-id');
                $.post('/jpanel/settings/hook-deliveries/' + delivery_id + '/redeliver').then(function(res){
                    $spinner.hide();
                    $container.html(res).show().find('a[data-toggle="tab"]').tab();
                });
            });
        }
        $modal.modal('show');
    }
};
