/* globals j */

import $ from 'jquery';

export default {
    init:function(){
        $('input[name=discount_type]').bind('change', j.promo.onDiscountTypeChange);

        j.promo.onDiscountTypeChange();
    },
    onDiscountTypeChange:function(){
        var type = $('input[name=discount_type]:checked').val();

        if (type == 'bxgy_dollar') {
            $('.discount-desc').html('$ off the entire contribution');
            $('.bxgy-only').removeClass('hide');
        } else if (type == 'bxgy_percent') {
            $('.discount-desc').html('% off the entire contribution');
            $('.bxgy-only').removeClass('hide');
        } else if (type == 'percent') {
            $('.discount-desc').html('% off each item');
            $('.bxgy-only').addClass('hide');
        } else {
            $('.discount-desc').html('$ off each item');
            $('.bxgy-only').addClass('hide');
        }
    }
};
