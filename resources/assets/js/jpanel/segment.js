/* globals j */

import $ from 'jquery';

export default {
    init: function() {
        $('input[name=type]').click(j.segment.onTypeChange);

        j.segment.onTypeChange();
    },
    onTypeChange : function () {
        var type = $('input[name=type]:checked').val();

        if (type == 'text') {
            $('#edit_options_wrap').addClass('hidden');
        } else {
            $('#edit_options_wrap').removeClass('hidden');
        }
    }
};
