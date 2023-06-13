/* globals adminSpaData, j */

import $ from 'jquery';
import _ from "lodash";

export default {
    init() {
        const contributionsTable = $('#contributions');
        if (contributionsTable.length === 0) {
            return false;
        }

        var contributions = contributionsTable.DataTable({
            "pagingType": 'simple',
            "info": false,
            "dom": 'rtpi',
            "sErrMode":'throw',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 6, "desc" ]],
            "columnDefs": [
                { targets: 0, orderable: false, class : "relative w-12 px-6 sm:w-16 sm:px-8", visible: adminSpaData.isGivecloudPro },
                { targets: 1, orderable: false}, // Avatar
                { targets: 2, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" }, // Supporter
                { targets: 3, orderable: true, class : "whitespace-nowrap px-3 py-4 text-center text-sm text-gray-500" }, // Recurring
                { targets: 4, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" }, // Payment
                { targets: 5, visible: window.adminSpaData.isGivecloudExpress,  orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" }, // Net
                { targets: 6, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" }, // Date
                { targets: 7, orderable: false, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500"}, // Status
                { targets: 8, orderable: false, class : "whitespace-nowrap px-3 py-4 text-center text-sm text-gray-500"} // View
            ],
            "stateSave": false,
            "ajax": {
                "url": "/jpanel/contributions-v2.listing",
                "type": "POST",
                "data": function (d) {
                    // Reset query filters
                    var allFilters = {}
                    _.forEach($('.datatable-filters [name]'), (elem) => allFilters[elem.name]= $(elem).val());
                    j.filtersToQueryString(allFilters, true);

                    var filters = {};
                    _.forEach($('.datatable-filters').serializeArray(), function (field) {
                        var $field = $('.datatable-filters').find('[name='+ field.name+']');
                        filters[field.name] = $field.val()
                    });

                    _.forEach(filters, function (value, key) {
                        if($.isArray(value))
                            value = value.filter(n=>n);

                        d[key] = value;
                    });

                    j.filtersToQueryString(filters, true);
                }
            },
            createdRow: function(row, data) {
                row.dataset.href = data.pop();
            },
            drawCallback : function(){
                j.ui.datatable.formatRows($('#contributions'));

                document.querySelectorAll('#contributions .avatar').forEach(avatar => {
                    avatar.style.backgroundColor = j.avatar.color(avatar.dataset.initials, 0.1);
                    avatar.querySelector('span').style.color = j.avatar.color(avatar.dataset.initials);
                });
            },
            initComplete : function(){
                j.ui.datatable.formatTable($('#contributions'));
            }
        });

        contributions.on('click', 'tbody tr',  function(e) {
            if(e.target.type === 'checkbox'){
                return;
            }
            this.dataset.href && (window.location = this.dataset.href);
        })

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        j.ui.datatable.enableFilters(contributions);
    }
};
