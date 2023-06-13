import $ from 'jquery';

$.fn.gc_serializeArray = function() {
    var fields = $.fn.serializeArray.apply(this);
    $.each(this.find('input'), function (i, element) {
        if (element.type == "checkbox" && !element.checked) {
            fields.push({ name: element.name, value: '' })
        }
    });
    return fields;
};
