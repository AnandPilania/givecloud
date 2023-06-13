import $ from 'jquery';
import Sugar from 'sugar';

$.toNumber = function(number, defaultValue) {
    if (typeof defaultValue === void 0) {
        defaultValue = null;
    }
    if (typeof number === 'number') {
        return number;
    } else if (typeof number === 'string') {
        number = Sugar.String.toNumber(number);
        return isNaN(number) ? defaultValue : number;
    }
    return defaultValue;
};
