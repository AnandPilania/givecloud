/* globals adminSpaData, j */

import $ from 'jquery';
import _ from 'lodash';
import toastr from 'toastr';

export default function() {

    //Loads the correct sidebar on window load,
    //collapses the sidebar on window resize.
    $(window).bind("load resize", function() {
        var width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse')
        } else {
            $('div.navbar-collapse').removeClass('collapse')
        }
    });

    $('.hide-on-page-ready').remove();

    // fix datatable error reporting
    $.fn.dataTableExt.sErrMode = 'throw';

    $('[data-toggle="tooltip"]').tooltip();

    $('#side-menu').metisMenu({ toggle: false });

    $(".switch").bootstrapSwitch({
        //offColor:'danger',
        onColor:'success',
        onText:'<i class="fa fa-check fa-fw"><i>',
        offText:'<i class="fa fa-times fa-fw"><i>'
    });

    $(".yes-no-switch").bootstrapSwitch({
        //offColor:'danger',
        onColor:'success',
        onText:'YES',
        offText:'NO'
    });

    // toggle password visibility
    $('input.password + .glyphicon').on('click', function() {
        var $input = $(this).prev();
        $(this).toggleClass('glyphicon-eye-close').toggleClass('glyphicon-eye-open');
        $input.attr('type', $input.attr('type') === 'text' ? 'password' : 'text');
    });

    $('body').on('click', '.copy-to-clipboard', function() {
        var $el = $(this);
        var $input = $('<input>').appendTo('body');
        $input.val($el.is(':input') ? $el.val() : $el.text()).select();
        document.execCommand('copy');
        $input.remove();
        toastr['success']('Copied to clipboard.');
    });

    $(".timelinify").timelinify();

    j.ui.init();
    j.post.init();
    j.page.init();
    j.hooks.init();
    j.settings.init();
    j.supporters.init();
    j.contributions.init();

    // stop the youtube video when you close the video dialog
    $(".modal-backdrop, #tourVideoModal .close, #tourVideoModal .btn").on("click", function() {
        $("#tourVideoModal iframe").attr("src", $("#tourVideoModal iframe").attr("src"));
    });

    if ($('#productForm').length > 0) {
        j.product.init();
    }

    if ($('#promo-code-form').length > 0) {
        j.promo.init();
    }

    if ($('#feed-form').length > 0) {
        j.posttype.init();
    }

    if ($('#payment_group_form').length > 0) {
        j.payment_group.init();
    }

    if ($('#segment-form').length > 0) {
        j.segment.init();
    }

    if ($('#user_form').length > 0) {
        j.user.init();
    }

    if ($('#imageLibrary').length > 0) {
        j.images.init();
    }

    // generic bootstrap validation
    $('.bs-validate').bootstrapValidator({
        exclude: ['.bs-ignore'],
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        }
    });

    $('.dpo-codes-refresh').on('click', function(ev){
        ev.preventDefault();
        $('.dpo-codes').each(function(i, el){
            if (el.selectize)
                el.selectize.destroy();

            $(el).removeClass('dpo-codes-init');
        });
        $('.dpo-codes-warning').remove();
        $.get('/jpanel/donor/codes/clearCache',function(){ $.dpoCodes(); });
    });

    (function(){
        // changing states
        var setState = function($_state){
            if ($_state == '1') {
                $('.-hidden-offline').css('display','list-item');
                $('.hidden-offline-toggle-btn').html('<i class="fa fa-fw fa-check-square-o"></i> Show Offline/Hidden Pages');//
            } else {
                $('.-hidden-offline').css('display','none');
                $('.hidden-offline-toggle-btn').html('<i class="fa fa-fw fa-square-o"></i> Show Offline/Hidden Pages');
            }
            $.store('show-hidden-offline-state', $_state);
        };

        // bind click event
        $('.hidden-offline-toggle-btn').bind('click', function(ev){
            ev.preventDefault();
            setState(($.store('show-hidden-offline-state') == '1') ? '0' : '1');
        });

        // set initial state
        setState($.store('show-hidden-offline-state'));
    })();

    if ($('#order-list').length > 0) {
        var orders_table = $('#order-list').DataTable({
            "pagingType": 'simple',
            "info": false,
            "dom": 'rtpi',
            "sErrMode":'throw',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 7, "desc" ]],
            "columnDefs": [
                { targets: 0, orderable: false, class : "relative w-12 px-6 sm:w-16 sm:px-8", visible: adminSpaData.isGivecloudPro },
                { targets: 1, orderable: false},
                { targets: 2, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" },
                { targets: 3, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" },
                { targets: 4, orderable: false, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" },
                { targets: 5, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" },
                { targets: 6, visible: window.adminSpaData.isGivecloudExpress, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500" },
                { targets: 7, orderable: true, class : "whitespace-nowrap px-3 py-4 text-sm text-gray-500"},
                { targets: 8, orderable: false, class : "whitespace-nowrap px-3 py-4 text-center text-sm text-gray-500"}
            ],
            "stateSave": false,
            "ajax": {
                "url": "/jpanel/contributions.listing",
                "type": "POST",
                "data": function (d) {
                    var filters = {
                        c: $('select[name=c]').val(),
                        fO: $('input[name=fO]').val(),
                        fd1: $('input[name=fd1]').val(),
                        fd2: $('input[name=fd2]').val(),
                        fU: $('input[name=fU]').val(),
                        fs: $('select[name=fs]').val(),
                        fp: $('select[name=fp]').val(),
                        fR: $('select[name=fR]').val(),
                        fv: $('select[name=fv]').val(),
                        fc: $('select[name=fc]').val(),
                        fg: $('select[name=fg]').val(),
                        fat: $('select[name=fat]').val(),
                        fit: $('select[name=fit]').val(),
                        promo: $('input[name=promo]').val(),
                        fundraising_page_id: $('input[name=fundraising_page_id]').val(),
                        membership_id: $('select[name=membership_id]').val(),
                        fots: $('select[name=fots]').val(),
                        fotm: $('select[name=fotm]').val(),
                        fotc: $('select[name=fotc]').val(),
                        fott: $('select[name=fott]').val(),
                        df: $('select[name=df]').val()
                    };

                   _.forEach(filters, function(value, key) {
                       if($.isArray(value))
                           value = value.filter(n=>n);

                        d[key] = value;
                    });

                    j.filtersToQueryString(filters);
                }
            },
            createdRow: function(row, data) {
                row.dataset.href = data.pop();
            },
            "drawCallback" : function(){
                j.ui.datatable.formatRows($('#order-list'));

                document.querySelectorAll('.avatar').forEach(avatar => {
                    avatar.style.backgroundColor = j.avatar.color(avatar.dataset.initials);
                });
            },
            "initComplete" : function(){
                j.ui.datatable.formatTable($('#order-list'));
            }
        });

        orders_table.on('click', 'tbody tr',  function(e) {
            if(e.target.type === 'checkbox'){
                return;
            }
            this.dataset.href && (window.location = this.dataset.href);
        })

        $('.datatable-filters input, .datatable-filters select').each(function(i, input){
            if ($(input).data('datepicker'))
                $(input).on('changeDate', function () {
                    orders_table.draw();
                });

            else
                $(input).change(function(){
                    orders_table.draw();
                });
        });

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        $('.datatable-export').on('click', function(ev){
            ev.preventDefault();

            var data = j.ui.datatable.filterValues('#transactionHistory');
            window.location = '/jpanel/reports/transactions.csv?'+$.param(data);
        });

        j.ui.datatable.enableFilters(orders_table);
    }

    if ($('#transactionHistory').length > 0) {
        var transaction_history_table = $('#transactionHistory').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "sDefaultContent" : "",
            "order": [[ 1, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0},
                { "orderable": true, "class":"text-center",  "targets": 7},
                { "orderable": true, "class":"text-right",  "targets": 8},
                { "visible": false, "orderable": false, "targets": 9},
                { "visible": false, "orderable": false, "targets": 10},
                { "visible": false, "orderable": false, "targets": 11},
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/reports/transactions.ajax",
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

            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var isUnsynced = aData[10];
                var is_payment_success = aData[11];
                var refunded_amt = aData[9];

                var $nRow = $(nRow); // cache the row wrapped up in jQuery

                if (isUnsynced == 1)
                    $nRow.addClass('danger')

                if (is_payment_success == 0)
                    $nRow.addClass('text-danger')

                if (refunded_amt > 0)
                    $nRow.addClass('text-muted')

                return nRow;
            },

            "drawCallback" : function(){
                j.ui.table.init();
                return true;
            }
        });

        $('.datatable-filters input, .datatable-filters select').not(':hidden').each(function(i, input){
            if ($(input).data('datepicker'))
                $(input).on('changeDate', function () {
                    transaction_history_table.draw();
                });

            else
                $(input).change(function(){
                    transaction_history_table.draw();
                });
        });

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        $('.datatable-export').on('click', function(ev){
            ev.preventDefault();

            var data = j.ui.datatable.filterValues('#transactionHistory');
            window.location = '/jpanel/reports/transactions.csv?'+$.param(data);
        });

        j.ui.datatable.enableFilters(transaction_history_table);
    }

    if ($('#payments-list-new').length > 0) {
        var $paymentTable = $('#payments-list-new');
        var payments_table_ajax_route = $paymentTable.data('ajax-route');

        var payments_table = $paymentTable.DataTable({
            "pagingType": 'simple',
            "info": false,
            "dom": 'rtpi',
            "sErrMode":'throw',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 0, "desc" ]],
            "columnDefs": [
                { "orderable": true, "targets": 0, "class" : "text-left" },
                { "orderable": true, "targets": 1, "class" : "text-left" },
                { "orderable": true, "targets": 2, "class" : "text-left" },
                { "orderable": true, "targets": 3, "class" : "text-right" },
                { "orderable": true, "targets": 4, "class" : "text-left" },
                { "orderable": true, "targets": 5, "class" : "text-left" },
                { "orderable": true, "targets": 6, "class" : "text-left" },
                { "orderable": true, "targets": 7, "class" : "text-left" },
                { "orderable": true, "targets": 8, "class" : "text-left" },
                { "orderable": true, "targets": 9, "class" : "text-left" },
            ],
            "stateSave": false,
            "ajax": {
                "url": payments_table_ajax_route,
                "type": "POST",
                "data": function (d) {
                    var filters = {};
                    _.forEach($('.datatable-filters').serializeArray(), function(field) {
                        filters[field.name] = filters[field.name] ? filters[field.name] + ',' + field.value : field.value;
                    });
                    _.forEach(filters, function(value, key) {
                        d[key] = value;
                    });

                    j.filtersToQueryString(filters);
                }
            },
            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var captured = aData[1],
                    $nRow = $(nRow);

                if (!captured) {
                    $nRow.addClass('text-muted');
                }

                return nRow;
            }
        });

        var payments_table_ajax_aggregate_route = $paymentTable.data('ajax-aggregate-route');

        payments_table.on('draw', function () {
            var params = {};
            payments_table.settings()[0].oInit.ajax.data(params);
            // Only reload the aggregate data if params change
            if (payments_table.params_checksum === JSON.stringify(params)) {
                return;
            }
            payments_table.params_checksum = JSON.stringify(params);

            $('#aggregate_html').html('Loading...');
            $.ajax({
                type: 'post',
                url: payments_table_ajax_aggregate_route,
                data: payments_table.ajax.params(),
                success: function(d){
                    $('#aggregate_html').html(d);
                }
            });
        });

        $('.datatable-export').on('click', function(ev){
            ev.preventDefault();

            var data = _.omitBy(j.ui.datatable.filterValues('#payments-list-new'), function(value) {
                return !value;
            });

            window.location = '/jpanel/reports/payments.csv?'+$.param(data);
        });

        j.ui.datatable.enableFilters(payments_table);
    }

    if ($('#payments-by-item-list').length > 0) {
        var payments_by_item_list = $('#payments-by-item-list').DataTable({
            "dom": 'rtpi',
            "sErrMode":'throw',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 9, "desc" ]],
            "columnDefs": [
                { "orderable": true, "targets": 0, "class" : "text-left" },
                { "orderable": true, "targets": 1, "class" : "text-left" },
                { "orderable": true, "targets": 2, "class" : "text-center" },
                { "orderable": true, "targets": 3, "class" : "text-right" },
                { "orderable": true, "targets": 4, "class" : "text-left" },
                { "orderable": true, "targets": 5, "class" : "text-left" },
                { "orderable": true, "targets": 6, "class" : "text-right" },
                { "orderable": true, "targets": 7, "class" : "text-center" },
                { "orderable": true, "targets": 8, "class" : "text-left" },
                { "orderable": true, "targets": 9, "class" : "text-left" },
                { "orderable": true, "targets": 10, "class" : "text-left" },
                { "orderable": true, "targets": 11, "class" : "text-left" },
            ],
            "stateSave": false,
            "ajax": {
                "url": "/jpanel/reports/payments-by-item.ajax",
                "type": "POST",
                "data": function (d) {

                    function getSelectValue(values) {
                        if (values.length == 0 || (values.length === 1 && values[0] === '')) {
                            return null;
                        }
                        return values;
                    }

                    d.i = getSelectValue($('select#itemfilter').val());
                    d.c = getSelectValue($('select#categoryfilter').val());
                    d.s = $('select[name=s]').val();
                    d.fd1 = $('input[name=fd1]').val();
                    d.fd2 = $('input[name=fd2]').val();
                    d.fga = $('select[name=fga]').val();
                    d.fc1 = $('input[name=fc1]').val();
                    d.fc2 = $('input[name=fc2]').val();
                    d.fat = $('select[name=fat]').val();
                    d.foi = $('select[name=foi]').val();
                    d.fmm = $('select[name=fmm]').val();
                    d.fg = $('select[name=fg]').val();
                    d.cc = $('select[name=cc]').val();
                }
            },
            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var refundAmt = aData[12],
                    captured = aData[10],
                    $nRow = $(nRow);

                if (refundAmt > 0) {
                    $nRow.addClass('text-danger');
                }

                if (!captured) {
                    $nRow.addClass('text-muted');
                }

                return nRow;
            },
            "drawCallback" : function(){
                j.ui.datatable.formatRows($('#order-list'));
                return true;
            },
            "initComplete" : function(){
                j.ui.datatable.formatTable($('#order-list'));
            }
        });

        $('.datatable-filters input, .datatable-filters select').each(function(i, input){
            if ($(input).data('datepicker'))
                $(input).on('changeDate', function () {
                    payments_by_item_list.draw();
                });

            else
                $(input).change(function(){
                    payments_by_item_list.draw();
                });
        });

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        $('.datatable-export').on('click', function(ev){
            ev.preventDefault();

            var data = j.ui.datatable.filterValues('#payments-by-item-list');
            window.location = '/jpanel/reports/payments-by-item.csv?'+$.param(data);
        });

        j.ui.datatable.enableFilters(payments_by_item_list);
    }

    if ($('#sponsors-list').length > 0) {
        var sponsors_list = $('#sponsors-list').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 1, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0, "class": 'text-center'},
                { "orderable": false, "targets": 2, "class": 'text-left'},
                //{ "orderable": true, "targets": 3, "class": 'text-center', 'width':'90px'},
                //{ "orderable": true, "targets": 4, "class": 'text-center', 'width':'90px'},
                //{ "orderable": true, "targets": 5, "class": 'text-left', 'width':'170px'},
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/sponsors.ajax",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields, function (i, field) {
                        if ($.isArray(d[field.name])) {
                            d[field.name].push(field.value);
                        } else if (typeof d[field.name] == 'string') {
                            var v = d[field.name]+'';
                            delete d[field.name];
                            d[field.name] = [v];
                            d[field.name].push(field.value);
                        } else {
                            d[field.name] = field.value;
                        }
                    });
                }
            },

            // colors/styles
            "fnDrawCallback": function() {
                $.dsSponsor();
            }
        });

        j.ui.datatable.enableFilters(sponsors_list);
    }

    if ($('#sponsorship-list').length > 0) {
        var sponsorship_list = $('#sponsorship-list').DataTable({
            "dom": 'rtpi',
            "autoWidth": false,
            "iDisplayLength" : 50,
            "processing": true,
            "serverSide": true,
            "order": [[ 2, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0, "class": 'text-center'},
                { "class": 'text-center', "targets": 3, "width": "100px"},
                { "class": 'text-left', "targets": 4, "width": "120px"},
                { "class": 'text-center', "targets": 5, "width":"80px"},
                { "class": 'text-center', "targets": 6},
                { "class": 'text-center', "targets": 7},
                { "class": 'text-center', "targets": 8},
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/sponsorship.ajax",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();

                    $.each(fields,function(i, field){
                        if ($.isArray(d[field.name])) {
                            d[field.name].push(field.value);

                        } else if (typeof d[field.name] == 'string') {
                            var v = d[field.name]+'';

                            delete d[field.name];

                            d[field.name] = [v];
                            d[field.name].push(field.value);

                        } else {
                            d[field.name] = field.value;
                        }
                    });

                    return d;
                }
            },

            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var is_sponsored = aData[6];

                var $nRow = $(nRow); // cache the row wrapped up in jQuery

                if (is_sponsored === 'Y')
                    $nRow.addClass('text-success')

                return nRow;
            }
        });

        j.ui.datatable.enableFilters(sponsorship_list);
    }

    if ($('#rpp-list').length > 0) {
        var recurringPaymentProfiles_table = $('#rpp-list').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 6, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0, "class": "text-left" },
                { "orderable": false, "targets": 1, "class": "text-left" },
                { "orderable": false, "targets": 2, "class": "text-left" },
                { "orderable": true, "targets":  3, "class": "text-left" },
                { "orderable": true, "targets":  4, "class": "text-left" },
                { "orderable": true, "targets":  5, "class": "text-left" },
                { "orderable": true, "targets":  6, "class": "text-left" },
                { "orderable": true, "targets":  7, "class": "text-right" },
                { "orderable": false, "targets": 8, "class": "text-center" },
                { "orderable": true, "targets":  9, "class": "text-left" },
                { "orderable": true, "targets": 10, "class": "text-right" },
                { "orderable": true, "targets": 11, "class": "text-left" },
            ],
            //"stateSave": true,
            "ajax": {
                "url": "/jpanel/recurring_payments.ajax",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields,function(i, field){
                        d[field.name] = field.value;
                    })
                }
            },

            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var status = aData[9];

                var $nRow = $(nRow); // cache the row wrapped up in jQuery

                if (status === 'Suspended')
                    $nRow.addClass('text-warning')

                if (status === 'Expired')
                    $nRow.addClass('text-warning')

                if (status === 'Cancelled')
                    $nRow.addClass('text-danger')

                return nRow;
            }
        });

        j.ui.datatable.enableFilters(recurringPaymentProfiles_table);
    }

    if ($('#taxReceiptsDataTable').length > 0) {
        j.taxReceipt.dataTable = $('#taxReceiptsDataTable').DataTable({
            "dom": 'rtpi',
            "select": {
                "info": false,
                "style": "multi+shift"
            },
            "language": {
                "select": {"rows": { "_": "", "0": "", "1": "" }}
            },
            "iDisplayLength" : 50,
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "order": [[ 1, "desc" ]],
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": 0,
                    "checkboxes": {
                        "selectRow": true,
                        "selectCallback": j.taxReceipt.onSelectionChange,
                        "selectAllCallback": j.taxReceipt.onSelectionChange,
                        "stateSave": false
                    }
                },
                { "class": "text-right", "targets": 4}
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/tax_receipts.ajax",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields,function(i, field){
                        d[field.name] = field.value;
                    })
                }
            },
            "drawCallback":function(){
                j.taxReceipt.init();
            },

            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var voided = aData[6];

                var $nRow = $(nRow); // cache the row wrapped up in jQuery

                if (voided)
                    $nRow.addClass('text-danger')

                return nRow;
            }
        });

        j.taxReceipt.dataTable.on('draw.dt.dtCheckboxes', function(){
            j.taxReceipt.onSelectionChange();
        });

        j.ui.datatable.enableFilters(j.taxReceipt.dataTable, function(){
            j.taxReceipt.clearSelection();
        });
    }

    if ($('#ordersByProductTable').length > 0) {
        var ordersByProductTable = $('#ordersByProductTable').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[7, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0},
                { "class": "text-left", "targets": 1},
                { "class": "text-left", "targets": 2},
                { "class": "text-left", "targets": 3},
                { "class": "text-left", "targets": 4},
                { "class": "text-center", "targets": 5},
                { "class": "text-center", "targets": 6},
                { "class": "text-right", "targets": 7},
                { "class": "text-center", "targets": 8, "orderable": false}
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/reports/products.ajax",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields,function(i, field){
                        d[field.name] = field.value;
                    })
                }
            }
        });

        j.ui.datatable.enableFilters(ordersByProductTable);
    }

    if ($('#transactionFeesTable').length > 0) {
        var transactionFeesTable = $('#transactionFeesTable').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[0, "desc" ]],
            "columnDefs": [
                { "class": "text-left",   "targets": 0 },
                { "class": "text-left",   "targets": 1, "orderable": false },
                { "class": "text-left",   "targets": 2, "orderable": false },
                { "class": "text-right",  "targets": 3, "orderable": false },
                { "class": "text-center", "targets": 4, "orderable": false },
                { "class": "text-right",  "targets": 5, "orderable": false },
                { "class": "text-right",  "targets": 6, "orderable": false },
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/reports/platform-fees.json",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields,function(i, field){
                        d[field.name] = field.value;
                    })
                }
            },
            "drawCallback" : function(d){
                const summaryTemplate = _.template(`
                    <div class="inline-block mr-5">
                        <table class="table mb-0" style="max-width:340px;border:1px solid #ddd;">
                            <tr class="active">
                                <th></th>
                                <th class="text-right">COUNT</th>
                                <th class="text-right">TOTAL</th>
                            </tr>
                            <tr>
                                <td>Payments</td>
                                <td class="text-right"><%= payments_count %></td>
                                <td class="text-right"><%= payments_amount %> <%= currency %></td>
                            </tr>
                            <tr>
                                <td>Refunds</td>
                                <td class="text-right"><%= refunds_count %></td>
                                <td class="text-right"><%= refunds_amount %> <%= currency %></td>
                            </tr>
                            <tr class="default">
                                <td><strong>Total Fees</strong> (<%= fees_rate %>%)</td>
                                <td class="text-right"></td>
                                <td class="text-right"><strong><%= total_amount %> <%= currency %></strong></td>
                            </tr>
                        </table>
                    </div>
                `);

                let summaryHtml = '';
                d.json.summary_data.forEach((data) => summaryHtml += summaryTemplate(data))

                $('#summary-panel').html(summaryHtml);
                $('#dcc-panel').html(d.json.dcc_total);

                j.ui.table.init();
                return true;
            }
        });

        j.ui.datatable.enableFilters(transactionFeesTable);
    }

    if ($('#donorImpactTable').length > 0) {
        var donorImpactTable = $('#donorImpactTable').DataTable({
            "dom": 'rtpi',
            "iDisplayLength": 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[2, "desc"]],
            "columnDefs": [
                { "class": "text-left", "targets": 1 },
                { "class": "text-right", "targets": 2 },
                { "class": "text-right", "targets": 3 },
                { "class": "text-right", "targets": 4 },
                { "class": "text-right", "targets": 5 },
                { "class": "text-right", "targets": 6 },
                { "class": "text-right", "targets": 7 },
                { "class": "text-right", "targets": 8 },
                { "class": "text-right", "targets": 9 },
                { "class": "text-right", "targets": 10 },
                { "class": "text-right", "targets": 11 }
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/reports/impact-by-supporter.json",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields, function (i, field) {
                        d[field.name] = field.value;
                    })
                }
            },
            "drawCallback": function () {
                j.ui.table.init();
                return true;
            }
        });

        j.ui.datatable.enableFilters(donorImpactTable);
    }

    if ($('#donorCoversCostsTable').length > 0) {
        var donorCoversCostsStats = $('#donorCoversCostsStats');
        var $totals = donorCoversCostsStats.find('[data-stats="totals"] > [data-loaded]');
        var $average = donorCoversCostsStats.find('[data-stats="average"] > [data-loaded]');
        var $conversions = donorCoversCostsStats.find('[data-stats="conversions"] > [data-loaded]');

        var donorCoversCostsTable = $('#donorCoversCostsTable').DataTable({
            "pagingType": 'simple',
            "info": false,
            "dom": 'rtpi',
            "sErrMode": 'throw',
            "iDisplayLength": 50,
            "processing": true,
            "serverSide": true,
            "order": [[7, "desc"]],
            "columnDefs": [
                { "class": "text-left", "targets": 0, "orderable": true },
                { "class": "text-left", "targets": 1, "orderable": true },
                { "class": "text-left", "targets": 2, "orderable": true },
                { "class": "text-left", "targets": 3, "orderable": true },
                { "class": "text-left", "targets": 4, "orderable": true },
                { "class": "text-right", "targets": 5, "orderable": true },
                { "class": "text-right", "targets": 6, "orderable": true },
                { "class": "text-left", "targets": 7, "orderable": true }
            ],
            "stateSave": false,
            "ajax": {
                "url": "/jpanel/reports/donor-covers-costs.json",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields, function (i, field) {
                        d[field.name] = field.value;
                    })
                }
            },
            preDrawCallback: function(){
                donorCoversCostsStats.find('[data-loaded]').addClass('hidden');
                donorCoversCostsStats.find('[data-loading]').removeClass('hidden');
                return true;
            },
            "drawCallback": function (d) {
                $totals.html(d.json.stats.totals);
                $average.html(d.json.stats.average);
                $conversions.html(d.json.stats.conversions);

                donorCoversCostsStats.find('[data-loaded]').removeClass('hidden');
                donorCoversCostsStats.find('[data-loading]').addClass('hidden');
                j.ui.datatable.formatRows($('#order-list'));
                return true;
            }
        });

        j.ui.datatable.enableFilters(donorCoversCostsTable);
    }

    if ($('#tributesDataTable').length > 0) {
        var tributes_table = $('#tributesDataTable').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 40,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 8, "desc" ]],
            "columnDefs": [
                { "orderable": false, "class":"text-center", "targets": 0},
                { "orderable": false, "class":"text-center", "targets": 1},
                { "class": "text-center", "targets": 5},
                { "class": "text-right", "targets": 7}
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/tributes.ajax",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields,function(i, field){
                        d[field.name] = field.value;
                    })
                }
            },

            "drawCallback" : function(){
                j.ui.datatable.formatRows($('#tributesDataTable'));
                j.tribute.init();
                return true;
            },

            "initComplete" : function(){
                j.ui.datatable.formatTable($('#tributesDataTable'));
            },

            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var sent_at = aData[10];
                var notification = aData[5];

                var $nRow = $(nRow); // cache the row wrapped up in jQuery

                if (notification != '' && !sent_at)
                    $nRow.addClass('text-bold')

                return nRow;
            }
        });

        j.ui.datatable.enableFilters(tributes_table);
    }

    if ($('#productOrdersDatatable').length > 0) {
        var productOrders_table = $('#productOrdersDatatable').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 1, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0},
                { "class": "text-left", "targets": 4},
                { "class": "text-left", "targets": 5},
                { "class": "text-center", "targets": 6},
                { "class": "text-right", "targets": 7},
                { "class": "text-right", "targets": 8}
            ],
            "stateSave": true,
            "ajax": {
                "url": "/jpanel/products/" + $('#productOrdersDatatable').data('product-id') + "/contributions.ajax",
                "type": "POST",
                "data": function (d) {
                    var fields = $('.datatable-filters').serializeArray();
                    $.each(fields,function(i, field){
                        d[field.name] = field.value;
                    })
                }
            },
            "drawCallback": function (d) {
                $('#stats_total_quantity_sold').html(d.json.stats.total_qty ? d.json.stats.total_qty : '0');
                $('#stats_total_orders').html(d.json.stats.order_count ? d.json.stats.order_count : '0');
                $('#stats_total_sales span').html(parseFloat(d.json.stats.total_sales).formatMoney());
                return true;
            },
            // colors/styles
            "fnRowCallback": function( nRow, aData ) {
                var refundAmt = aData[10];
                var iscomplete = aData[13];

                var $nRow = $(nRow); // cache the row wrapped up in jQuery

                if (iscomplete)
                    $nRow.addClass('success');

                if (refundAmt > 0)
                    $nRow.addClass('text-danger');

                return nRow;
            }
        });

        $('.datatable-filters input, .datatable-filters select').not(':hidden').each(function(i, input){
            if ($(input).data('datepicker')) {
                $(input).on('changeDate', function () {
                    productOrders_table.draw();
                });

            } else {
                $(input).change(function(){
                    productOrders_table.draw();
                });
            }
        });

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        j.ui.datatable.enableFilters(productOrders_table);
    }

    if ($('#abandonedCartsTable').length > 0) {
        var abandoned_carts_table = $('#abandonedCartsTable').DataTable({
            "dom": 'rtpi',
            "iDisplayLength" : 50,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "order": [[ 8, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 0},
                { "class": "text-center", "targets": 3},
                { "class": "text-right", "targets": 4}
            ],
            "stateSave": false,
            "ajax": {
                "url": "/jpanel/contributions/abandoned_carts.ajax",
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
            }
        });

        $('.datatable-filters input, .datatable-filters select').not(':hidden').each(function(i, input){
            if ($(input).data('datepicker'))
                $(input).on('changeDate', function () {
                    abandoned_carts_table.draw();
                });

            else
                $(input).change(function(){
                    abandoned_carts_table.draw();
                });
        });

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        j.ui.datatable.enableFilters(abandoned_carts_table);
    }

    // product list image preview
    $(".productImagePreview").each(function(i, el){
        $(el).popover({
            html : true,
            placement : 'right',
            content: function() {
                return '<img src="' + $(el).data('imgsrc') + '" style="height:120px; width:auto;" />';
            },
            trigger : 'hover'
        });

        $(el).on('show.bs.popover', function () {
            $(".imgThumb").popover('hide');
        });
    });

    // ctrl+s can only be enabled on one form PER PAGE
    if ($('form.enable-ctrl-s').length == 1) {
        $('form.enable-ctrl-s').each(function(i, form){
            $(document).bind('keydown', function(e) {
                if(e.ctrlKey && (e.which == 83)) {
                    e.preventDefault();
                    $(form).submit();
                    return false;
                }
            });
        });
    }

    // hide flash messages
    $('.flash_message').not('.static').each(function(i,element){
        setTimeout(function(){
            $(element).slideUp(function(){
                $(this).remove();
            });
        },3000);
    });

    $('#feed_list-slide').sortable({
        revert: true,
        items: 'li',
        placeholder: "sortable-placeholder",
        //connectWith: 'ul.list_of_posts',
        handle:'.feed_list_li-move_anchor',
        start:function(){
            //$('ul.list_of_posts').prepend('<li class="post_item -temp">&nbsp;</li>');
        },
        stop:function(){
            //$('.post_item.-temp').remove();
        },
        update:function(){
            var sequence = [];
            $('#feed_list-slide li').each(function(i,li){
                sequence.push($(li).attr('data-post_id'));
            });

            var error_fn = function(){ alert('Sequence failed. Please try again.'); }

            $.ajax({
                type:'post',
                url:'/jpanel/feeds/posts/sequence',
                data:{sequence:sequence.toString()},
                success:function(d){ if (d.success === false) error_fn(); },
                error:function(){ error_fn() }
            });
        }
    });

    $('table.datatable').dataTable({
        "dom": 'frtpi',
        "pageLength": 50,
        "autoWidth": false,
        "order": [[ 1, "asc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 0 } // disable ordering the first column in all cases
        ],
        "oLanguage": {
            "sSearch": "Quick Search: "
        }
    });

    var unique_colors = {};
    $('.color-picker').each(function(i, el){
        if (typeof unique_colors[$(el).val()] == 'undefined') {
            unique_colors[$(el).val()] = $(el).val();
        }
    });

    $('.color-picker').minicolors({
        'theme'    : 'bootstrap',
        'swatches' : Object.keys(unique_colors)
    });

    j.charts.init();

    $('[data-toggle="popover"]').popover({
        'html'     : true
    });


    // all modals
    $('.modal').on('show.bs.modal', function () {

        // velocity animation
        //$(modal).find('.modal-dialog').velocity('transition.flipBounceXIn');
        $(this).find('.modal-dialog').velocity('transition.flipYIn', {duration:500});
    });

    /* setting search */
    $(function(){
        var settingSearch = function(val){
            val = $.trim(val.toLowerCase());

            $('.setting-tab').css('display','none');
            $('.setting-panel').css('display','none');
            $('.setting-tab .setting').css('display','none');
            $('.list-group-item.active').removeClass('active');

            if (val == '') {
                $('.search-status').removeClass('hide');
            } else {
                $('.search-status').addClass('hide');
                $('.setting-tab .setting[data-search*="'+val+'"]').each(function(i, el){
                    $(el).css('display','block');
                    $(el).parents('.setting-tab, .setting-panel').css('display','block');
                });
            }
        }

        var resetSearch = function(){
            $('.setting-search').val('');
            $('.setting-search').focus();
            settingSearch('');
        }

        var stopSearch = function(){
            $('.setting-search').val('');
            $('.setting-tab').css('display','');
            $('.setting-panel').css('display','');
            $('.setting-tab .setting').css('display','');
            $('.search-status').addClass('hide');
        }

        $('.setting-search').bind('keyup', function(ev){
            settingSearch($(ev.target).val());
        });
        $('.setting-search').bind('focus', function(ev){
            settingSearch($(ev.target).val());
        });
        $('.stop-search').bind('click', function(){
            stopSearch();
        });
        $('.reset-search').bind('click', function(){
            resetSearch();
        });
    });

    $('.toastify .alert').each(function(i, el){
        if ($(el).hasClass('alert-success')) {
            toastr['success']($(el).text());
        } else {
            toastr['error']($(el).text());
        }
    });

    $('.font-form-control').each(function(){
        var $input = $(this).find('input[type=text]');
        var $select = $(this).find('select');
        var $hidden = $(this).find('input[type=hidden]');
        var $preview = $(this).find('.font-preview');
        var font = $hidden.val();
        $input.on('change', function(){
            $hidden.val(this.value);
        });
        $select.on('change', function(){
            var font = $select.val();
            var sampleText = $select.data('sample-text');
            if (font) {
                $hidden.val(font);
                $input.val('').hide();
                $preview.empty().append([
                    '<div style="height:30px; max-height:30px; font-size:16px; line-height:22px;">',
                        '<link href="https://fonts.googleapis.com/css?family=' + encodeURIComponent(font) + '" rel="stylesheet">',
                        '<span style="font-family:\'' + font + '\';">' + sampleText + '</span>',
                    '</div>'
                ].join(''));
            } else {
                $hidden.val('');
                $input.show();
                $preview.empty();
            }
        });
        function inDropdown(font) {
            return !!Array.prototype.find.call($select[0].options, function(option){
                return option.value === font;
            });
        }
        if (inDropdown(font)) {
            $select.val(font).change();
        } else if (font) {
            $select.val('');
            $input.val(font).show();
        }
    });
}
