/* globals j */

import $ from 'jquery';
import _ from 'lodash';

export default {
    show:function(callback){

        // load modal
        var modal = j.templates.render('contentTemplatesModalTmpl');
        $('body').append(modal);
        var $modal = $(modal).modal();

        $modal.css('z-index',9999999999);

        $modal.on('hidden.bs.modal', function () {
            $modal.remove();
        });

        $modal.on('click', 'a.imgThumb', function(){
            if (callback) {
                callback($(this).data('publicurl'));
            }
            $modal.modal('hide');
        });

        j.contentTemplates.populate();

        return $modal;
    },
    populate:function(){
        $('#templates-pages-wrap, #templates-sections-wrap, #templates-components-wrap, #templates-smart-codes-wrap').empty();

        var $container = $('#templates-pages-wrap');

        $.each(window.Givecloud.settings.tinymce_templates,function(i, data){
            switch (data.title) {
                case 'Pages': $container = $('#templates-pages-wrap'); break;
                case 'Sections': $container = $('#templates-sections-wrap'); break;
                case 'Components': $container = $('#templates-components-wrap'); break;
                case 'Smart Codes': $container = $('#templates-smart-codes-wrap'); break;
                default: $container = $('#templates-components-wrap');
            }
            _.chunk(data.templates, 6).forEach(function(templates) {
                var $row = $('<div class="row"></div>').appendTo($container);
                templates.forEach(function(data) {
                    var $item = j.templates.render('contentTemplatesThumbTmpl',data);
                    $row.append($item);
                });
            });
        });
    },
    chooseOne:function(callback){
        var divalog = j.contentTemplates.show(callback);
        divalog.addClass('choose-mode');
        divalog.find('.modal-title').append($('<small>&nbsp;Click the template you want to use.</small>'));
    },
};
