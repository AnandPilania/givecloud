/* globals j */

export default {

    color: function(initials, opacity) {
        let color = j.avatar.colors[j.avatar.numberFromText(initials) % j.avatar.colors.length];
        if(opacity) {
            color = color.replace(')', ',' + opacity + ')');
        }
        return color;
    },

    colors :  [
        'rgb(213,0,80)',
        'rgb(130,105,254)',
        'rgb(255,99,13)',
        'rgb(253,165,54)',
        'rgb(240,0,109)',
        'rgb(0,136,60)',
        'rgb(240,0,34)',
        'rgb(38,40,134)',
        'rgb(253,108,111)',
        'rgb(253,196,17)',
        'rgb(38,152,115)',
        'rgb(69,0,179)',
        'rgb(0,196,182)',
        'rgb(93,35,128)',
        'rgb(0,159,189)',
        'rgb(0,72,154)',
    ],

    numberFromText: function(initials) {
        const charCodes = initials
            .split('') // => ["A", "A"]
            .map(char => char.charCodeAt(0)) // => [65, 65]
            .join(''); // => "6565"

        return parseInt(charCodes, 10);
    }
}
