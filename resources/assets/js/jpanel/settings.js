import $ from 'jquery';
import toastr from 'toastr';

export default {
    init() {
        if ($('#settings_form').length === 0) return;

        // scroll pane for customizations
        $(window).on('resize', () => {
            var $c = $('#customizations-pane');
            if ($c.length) $c.height($(window).height()-$c.offset().top-2);
        }).trigger('resize');

        // saving
        $('#settings_form').ajaxForm({
            'beforeSubmit' : function() {
                for(var i = 1; i <= 6; i++) {
                    if($('#bucket_p2p_page_image_' + i).val()) {
                        $('#save-button').button('loading');
                        return true;
                    }
                }

                // If we get here, it means that there's no default images provided.
                const $allowImageUpload = $('input[name="settings[p2p_page_allow_image_upload]"].switch');
                if ($allowImageUpload.length && ! $allowImageUpload.prop('checked')) {
                    toastr['error']('You must provide default images if you don’t allow custom image uploads');
                    return false;
                }

                $('#save-button').button('loading');
                return true;
            },
            'success' : function(){
                $('#save-button').button('reset');
                toastr['success']('Updates saved.');
            },
            'error' : function() {
                $('#save-button').button('reset');
                toastr['error']('Updates failed to save.');
            }
        });

        // custom code for active/inactive tabs because the tab buttons are in different places (advanced is separate)
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            //e.target // newly activated tab
            //e.relatedTarget // previous active tab

            $('a[data-toggle="tab"]').each(function(i, el){
                $(el).removeClass('active');
            });

            $(e.target).addClass('active');
        })

        // autoselect first tab
        $('*[data-tabs="tabs"] a:first').tab('show');

        $('.show-edit-setting').click(function(ev){
            ev.preventDefault();
            $('.edit-setting').toggleClass('hide');
        });

        function toggleDescriptionTemplatesVisibility(state) {
            $('#bucket_p2p_description_template_1,#bucket_p2p_description_template_2,#bucket_p2p_description_template_3').each(function(){
                $(this).parents('.form-group').toggleClass(
                    'hidden',
                    state
                )
            });
        }
        var customDescriptionSwitch = $('input[name="settings[p2p_allow_custom_description]"].switch');
        customDescriptionSwitch.on('switchChange.bootstrapSwitch', function(e, state){
            toggleDescriptionTemplatesVisibility(state);
        });

        toggleDescriptionTemplatesVisibility(customDescriptionSwitch.prop('checked'));
    },
};
