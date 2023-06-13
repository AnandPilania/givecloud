/* globals adminSpaData, j */

import $ from 'jquery';
import _ from 'lodash';

function numberFromText(text) {
    const charCodes = text
        .split('') // => ["A", "A"]
        .map(char => char.charCodeAt(0)) // => [65, 65]
        .join(''); // => "6565"
    return parseInt(charCodes, 10);
}

export default {
    init() {
        if ($('#supporters').length == 0) return false;

        const colors = ['#00AA55', '#009FD4', '#B381B3', '#939393', '#E3BC00', '#D47500', '#DC2A2A'];
        var members_table = $('#supporters').DataTable({
            "dom": 'rtpi',
            "iDisplayLength": 10,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "sDefaultContent": "",
            "order": [[9, "desc"]],
            "columnDefs": [
                { targets: 0, orderable: false, class: "relative w-12 px-6 sm:w-16 sm:px-8" },
                { targets: 1, orderable: true}, // Name
                { targets: 2, orderable: true}, // Email
                { targets: 3, orderable: true, class: "text-gray-600", visible: adminSpaData.isReferralSourcesEnabled}, // Source
                { targets: 4, orderable: true, class: "text-center"}, // RPPs
                { targets: 5, orderable: true, class: "text-center", visible: adminSpaData.isNpsEnabled}, // NPS
                { targets: 6, orderable: true, class: "text-center", visible: adminSpaData.isGivecloudPro}, // Has Login
                { targets: 7, orderable: true, class: "text-center"}, // Payments
                { targets: 8, orderable: true, class: "text-center"}, // Total $
                { targets: 9, orderable: true}, // Created at
                { targets: 10, orderable: false, class: "text-center"}, // View
            ],
            "ajax": {
                "url": "/jpanel/supporters.listing",
                "type": "POST",
                "data": function (d) {
                    var filters = {};
                    _.forEach($('.datatable-filters').serializeArray(), function (field) {
                        filters[field.name] = filters[field.name] ? filters[field.name] + ',' + field.value : field.value;
                    });
                    _.forEach(filters, function (value, key) {
                        d[key] = value;
                    });
                    j.filtersToQueryString(filters);
                }
            },
            "drawCallback": function() {
                j.ui.datatable.formatRows($('#supporters'));

                document.querySelectorAll('.avatar').forEach(avatar => {
                    const text = avatar.dataset.initials; // => "AA"
                    avatar.style.backgroundColor = colors[numberFromText(text) % colors.length]; // => "#DC2A2A"
                });
            },
            "initComplete" : function(){
                j.ui.datatable.formatTable($('#supporters'));
            }
        });

        j.ui.datatable.enableFilters(members_table);
    }
};
