import _ from 'lodash';

export default function(filters, withArray = false) {
    var params = new URLSearchParams(location.search);
    _.forEach(filters, function(value, key) {
        if (value === null || value === '' || typeof value === 'undefined' || (Array.isArray(value) && value.length === 0)) {
            params.delete(key);
            params.delete(key + '[]')
            return;
        }

        if(withArray && Array.isArray(value)) {
            params.delete(key + '[]')
            _.forEach(value, function(val) {
                val && params.append(key + '[]', val);
            })
            return;
        }

        params.set(key, value);
    });
    params = params.toString();
    window.history.replaceState({}, '', location.pathname + (params ? '?' + params : ''));
}

