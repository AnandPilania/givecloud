
import Sugar from 'sugar';

function formatMoney(amount, precision) {
    return Sugar.Number.format(amount, typeof precision === 'undefined' ? 2 : precision);
}

Number.prototype.formatMoney = function(precision) {
    return formatMoney(this, precision);
};

function formatTime(time) {
    const h = Math.floor(time / 3600);
    const m = Math.floor((time % 3600) / 60);
    const s = Math.floor(time % 60);
    return [
        h,
        m > 9 ? m : (h ? '0' + m : m || '0'),
        s > 9 ? s : '0' + s,
    ].filter(a => a).join(':');
}

function rand(limit) {
    return Math.floor(Math.random() * (limit || 1000001));
}

export default {
    formatMoney,
    formatTime,
    rand,
};
