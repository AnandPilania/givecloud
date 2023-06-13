/* globals j */

import $ from 'jquery';
import _ from 'lodash';
import lodashTemplates from '@app/templates';

export default {
    compile(templateName) {
        _.templateSettings.imports = {
            '$': $,
            'j': j,
            'Givecloud': window.Givecloud,
        };
        if (typeof lodashTemplates[templateName] === 'undefined') {
            lodashTemplates[templateName] = _.template($(`#${templateName}`).html());
        }
        return lodashTemplates[templateName];
    },
    html(templateName, data) {
        return j.templates.compile(templateName)(data);
    },
    render(templateName, data) {
        return $(j.templates.html(templateName, data));
    },
};
