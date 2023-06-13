theme.ProductTributeFields = (function (t) {
    function ProductTributeFields(element) {
        return new Vue({
            el: element,
            delimiters: ['${', '}'],
        });
    }

    return Array.from(document.querySelectorAll('.product-tribute-fields-app')).map(function (ptfa) {
        new ProductTributeFields(ptfa);
    });
})(theme);
