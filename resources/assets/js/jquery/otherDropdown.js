import $ from 'jquery';

$.fn.otherDropdown = function() {
    return this.each(function(){
        var $input = $(this);
        $input.data('name', $input.attr('name'));
        $input.append('<option value="other">Other</option>');
        $input.css({
            'display': 'inline-block',
            'margin-right': '3px',
            'vertical-align': 'top'
        });

        var $other = $('<input type="text" class="form-control" />');
        $other.data('name', $input.attr('name'));
        $other.val($input.data('value'));
        $other.hide().insertAfter($input);
        $other.css({
            'width': '200px'
        });

        $input.change(function(e, dontFocus){
            if (this.value === 'other') {
                $other.attr('name', $other.data('name'));
                $input.removeAttr('name');
                $other.css({ display: 'inline-block' });
                if (!dontFocus) $other.focus();
            } else {
                $input.attr('name', $input.data('name'));
                $other.removeAttr('name');
                $other.hide();
            }
        });

        if ($input.val() !== $input.data('value')) {
            $input.val('other').trigger('change', [true]);
        }
    });
};
