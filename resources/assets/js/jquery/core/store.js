import $ from 'jquery';

$.store = function($k, $v) {
    if (typeof(Storage) !== "undefined") {
        if (typeof($v) === "undefined") {
            return localStorage.getItem($k);
        } else {
            return localStorage.setItem($k, $v);
        }
    }
    return null;
};
