/* globals j */

import $ from 'jquery';
import ace from 'ace-builds/src-noconflict/ace';
import flatpickr from 'flatpickr';
import toastr from 'toastr';
import createHtmlEditor  from '@app/tinymce/editor';
import 'daterangepicker'
import 'daterangepicker/daterangepicker.css'
import moment from 'moment'

import updateStateDropdown from './updateStateDropdown';

export default function() {

    // update the state/province dropdowns when country dropdown changes
    updateStateDropdown.init();

    $('input.date').datepicker({ format: 'yyyy-mm-dd', autoclose:true });
    $('input.datePretty').datepicker({ format: 'M d, yyyy', autoclose:true });

    $('input[data-date-range-picker]').daterangepicker({
        alwaysShowCalendars: true,
        autoUpdateInput: false,
        locale: { cancelLabel: 'Clear' },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    $('input[data-date-range-picker]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MMM Do, YYYY') + ' to ' + picker.endDate.format('MMM Do, YYYY'));
    });

    $('input[data-date-range-picker]').on('cancel.daterangepicker', function() {
        $(this).val('');
    });

    $('.selectize-tags').selectize({
        'create' : true,
        'persist' : true,
        'createOnBlur' : true,
        plugins: ['remove_button','drag_drop'],
    });

    $('.selectize-tag').selectize({
        'create' : true,
        'maxItems': 1,
        'persist' : true,
        'createOnBlur' : true,
        plugins: ['remove_button','drag_drop'],
    });

    $('.selectize').each(function() {
        var $input = $(this), plugins = ['remove_button'], maxItems = 1;
        if ($input.prop('multiple') && $input.hasClass('orderable')) {
            plugins.push('drag_drop');
        }

        if ($input.prop('multiple')) {
            maxItems = $input.data('maxItems') || null;
        }

        $input.selectize({
            persist: true,
            maxItems: maxItems,
            plugins,
            onChange: function (values) {
                // ensure that an empty value is passed through when nothing is selected
                if (values.length === 0 && !this.$input.data('allow-empty')) {
                    this.$input.append($('<option selected="selected" value=""></option>'))
                }
            },
            onInitialize: function() {
                if($input.prop('multiple') && $input.hasClass('orderable') && $input.data('ordered')) {
                    this.setValue($input.data('ordered'));
                }
            }
        });
    });

    $('.other-dropdown').otherDropdown();

    $('[data-popover-bottom]').popover({
        'content'   : function(){ return $(this).data('popover-bottom'); },
        'html'      : true,
        'placement' : 'bottom',
        'trigger'   : 'hover',
        'template'  : '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
    });

    $('[data-popover-top]').popover({
        'content'   : function(){ return $(this).data('popover-top'); },
        'html'      : true,
        'placement' : 'top',
        'trigger'   : 'hover',
        'template'  : '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('.input-daterange').each(function(i, el){
        $(el).datepicker({
            format: 'yyyy-mm-dd',
            inputs: $(el).find('input').toArray(),
            autoclose:true,
            clearBtn: true
        });
    });

    $('.input-daterange-pretty').each(function(i, el){
        $(el).datepicker({
            format: 'M d, yyyy',
            inputs: $(el).find('input').toArray(),
            autoclose:true,
            clearBtn: true
        });
    });

    $('.metadata-event-date-control').each(function(i, el) {
        var $el = $(el);
        var $checkbox = $el.find('.checkbox input');
        var $summary = $el.find('.summary');
        var flatpickrs = [];
        function drawFlatpickrs(allDay) {
            var options = {
                enableTime: true,
                altInput: true,
                altFormat: 'Y-m-d h:iK',
                dateFormat: 'Y-m-d H:i',
                onChange: function() {
                    updateSummary();
                }
            };
            if (allDay) {
                options.enableTime = false;
                options.altFormat = 'Y-m-d';
                options.dateFormat = 'Y-m-d';
            }
            flatpickrs.forEach(function(flatpickr) {
                flatpickr.destroy();
            });
            flatpickrs = $el.find('.event-date').flatpickr(options);
            updateSummary();
        }
        function updateSummary() {
            var startDate = flatpickr.formatDate(flatpickrs[0].selectedDates[0], 'F j, Y');
            var startDateShort = flatpickr.formatDate(flatpickrs[0].selectedDates[0], 'F j');
            var startTime = flatpickr.formatDate(flatpickrs[0].selectedDates[0], 'h:iK').toLowerCase();
            var endDate = flatpickr.formatDate(flatpickrs[1].selectedDates[0], 'F j, Y');
            var endTime = flatpickr.formatDate(flatpickrs[1].selectedDates[0], 'h:iK').toLowerCase();
            if ($checkbox.is(':checked')) {
                if (startDate === endDate) {
                    $summary.text('This event is all day on ' + startDate + '.');
                } else {
                    $summary.text('This event is all day starting on ' + startDateShort + ' and ending on ' + endDate + '.');
                }
            } else if (startDate === endDate) {
                $summary.text('This event is from ' + startTime + ' to ' + endTime + ' on ' + startDate + '.');
            } else {
                $summary.text('This event is from ' + startTime + ' on ' + startDateShort + ' and ends at ' + endTime + ' on ' + endDate + '.');
            }
        }
        $checkbox.on('change', function() {
            drawFlatpickrs($checkbox.is(':checked'));
        }).trigger('change');
    });

    if ($.fn.TouchSpin) {
        $('.input-spin').each(function(){
            $(this).TouchSpin({
                buttonup_class: "btn btn-default",
                buttondown_class: "btn btn-default",
                verticalbuttons: $(this).data('verticalbuttons') || false,
                min: $(this).attr('min') || -1000000000,
                max: $(this).attr('max') ||  1000000000
            });
            if (this.style.width) {
                $(this).parents('.input-group').width(this.style.width);
                this.style.width = '';
            }
        });
    }

    $('.input-padding .input-spin').on('change', function(){
        var parent = $(this).parents('.input-padding'), value = [];
        parent.find('.input-spin').each(function(){
            value.push((this.value || 0) + 'px');
        });
        parent.find('input[type=hidden]').val(value.join(' '));
    });

    $('.input-pixel').on('change', function(){
        $(this).parents('.input-group').prev('input[type=hidden]').val((this.value || 0) + 'px');
    });

    $('.code-editor').each(function() {
        var input = $($(this).data('input'));
        if (!input.length) {
            return;
        }
        var mode = $(this).data('mode') || 'html';
        var theme = $(this).data('theme') || 'chrome';
        var editor = ace.edit(this, {
            displayIndentGuides: true,
            enableEmmet: true,
            enableLiveAutocompletion: true,
            fontFamily: '"Operator Mono", "Source Code Pro", Menlo, Consolas, Lucida Console, monospace',
            fontSize: 14,
            highlightActiveLine: true,
            mode: `ace/mode/${mode}`,
            scrollPastEnd: true,
            showPrintMargin: false,
            theme: `ace/theme/${theme}`,
            useWorker: false,
            wrap: false
        });
        editor.setValue(input.val(), 1);
        editor.on('change', function() {
            input.val(editor.getValue());
        });
    });

    $('.givecloudeditor').givecloudeditor({
        body_class: window.Givecloud.settings.tinymce_classes,
        content_css: window.Givecloud.settings.tinymce_css,
        content_style: 'body {margin: 20px; box-shadow:5px 5px 30px rgba(0,0,0,0.5)}'
    });

    $('textarea.html:not(:tinymce)').givecloudeditor({
        body_class: window.Givecloud.settings.tinymce_classes,
        content_css: window.Givecloud.settings.tinymce_css
    });

    var global_mentions = {
        '[[profile_url]]':            "URL to My Profile",
        '[[history_url]]':            "URL to My History",
        '[[sponsorships_url]]':       "URL to My Sponsorships",
        '[[payment_methods_url]]':    "URL to My Payment Methods",
        '[[recurring_payments_url]]': "URL to Recurring Payments",
        '[[login_url]]':              "URL to Login",
        '[[register_url]]':           "URL to Register",
        '[[shop_organization]]':      "Your Organization's Name",
        '[[shop_url]]':               "Your Organization's URL"
    };

    var valid_tinymce_els = "@[id|style|class|title|dir|lang|xml::lang],pre[*],script[*],a[*],strong/b,em/i,div[align],span,br,p,h1,h2,h3,h4,h5,h6,small,img[*],form[*],input[*],select[*],label[*],button[*],iframe[src|height|width|style|allowtransparency|frameborder|scrolling],ul,ol,li,table,tbody,tr,td,th,hr,blockquote,audio[*],video[*],pre";

    $('textarea.simple-html').tinymce({
        base_url: 'https://cdn.givecloud.co/npm/tinymce@5.10.2',
        suffix: '.min',
        theme: "silver",
        skin: 'oxide',
        plugins : "autoresize image imagetools importcss link lists media noneditable paste table template",
        toolbar1 : "bold italic underline forecolor | alignleft aligncenter alignright alignjustify | link image",
        menubar : false,
        statusbar : false,
        height:90,
        document_base_url : window.location.origin,
        convert_urls : false,
        relative_urls : false,
        paste_use_dialog : false,
        paste_auto_cleanup_on_paste : true,
        paste_convert_headers_to_strong : false,
        paste_strip_class_attributes : "all",
        paste_remove_spans : true,
        paste_remove_styles : true,
        valid_elements : valid_tinymce_els,
        formats:{
            'bold':{'inline':'strong'}
        }
    });

    $('textarea.html-doc:not(:tinymce)').each(function(){
        var editor = createHtmlEditor(this, {
            content_css: '/jpanel/assets/css/tinymce-reset.css'
        });
        editor.addMentions(global_mentions);
    });

    $('textarea.html-tribute:not(:tinymce)').each(function(){
        var editor = createHtmlEditor(this, {
            content_css: '/jpanel/assets/css/tinymce-reset.css'
        });
        editor.addMentions(global_mentions);
        editor.addMentions({
            '[[donor_first_name]]': "Donor's First Name",
            '[[donor_last_name]]':  "Donor's Last Name",
            '[[tribute_type]]':     "Type of Tribute",
            '[[name]]':             "Name on Tribute",
            '[[amount]]':           "Tribute Amount",
            '[[message]]':          "Personal Message on Tribute",
            '[[notify_name]]':      "Notification Recipient Name",
            '[[notify_at]]':        "Notification Intended Delivery Date",
            '[[notify]]':           "Notification Type",
            '[[notify_email]]':     "Notification Email",
            '[[notify_address]]':   "Letter Mailing Address",
            '[[notify_city]]':      "Letter Mailing City",
            '[[notify_state]]':     "Letter Mailing State",
            '[[notify_zip]]':       "Letter Mailing ZIP",
            '[[notify_country]]':   "Letter Mailing Country"
        });
    });

    $('textarea.html-tax-receipt:not(:tinymce)').each(function(){
        var editor = createHtmlEditor(this, {
            content_css: '/jpanel/assets/css/tinymce-reset.css'
        });
        editor.addMentions(global_mentions);
        editor.addMentions({
            '[[issued_at]]':      "Issue Date",
            '[[summary_table]]':  "Summary Table",
            '[[ordered_at]]':     "Original Contribution Date",
            '[[amount]]':         "Receiptable Amount",
            '[[number]]':         "Receipt Number",
            '[[changes]]':        "Receipt Changes & Revisions",
            '[[name]]':           "Full Name of Receipient (either the organization or the individual)",
            '[[email]]':          "Email",
            '[[address_01]]':     "Address Line 1",
            '[[address_02]]':     "Address Line 2",
            '[[city]]':           "City",
            '[[state]]':          "State",
            '[[zip]]':            "ZIP",
            '[[country]]':        "Country",
            '[[phone]]':          "Phone",
        });
    });

    $('textarea.simplehtml:not(:tinymce)').givecloudeditor({
        content_css: window.Givecloud.settings.tinymce_css
    });

    $('.dpo-test').not('dpo-test-init').each(function(i, el){
        var $this = $(el);
        var $msg = $('<small style="padding-left:10px;"></small>').insertAfter($this);

        var __verify = function(){
            $msg.removeClass('text-danger text-success').addClass('text-muted').html('<i class="fa fa-spin fa-spinner"></i> Testing...');
            var data = {
                'username' : $('#'+$this.data('username')).val(),
                'password' : $('#'+$this.data('password')).val(),
                'apikey' : $('#'+$this.data('apikey')).val()
            };

            $.post('/jpanel/donor/verify_connection.json', data, function(is_connected){
                if (is_connected)
                    $msg.removeClass('text-muted').addClass('text-success').html('<i class="fa fa-thumbs-up"></i> Works!');
                else
                    $msg.removeClass('text-muted').addClass('text-danger').html('<i class="fa fa-thumbs-down"></i> Failed!');
            },'json');
        }

        $this.click(function(ev){ ev.preventDefault(); __verify(); });

        $this.addClass('dpo-test-init');
    });

    $('.infusionsoft-test').not('infusionsoft-test-init').each(function(i, el){
        var $this = $(el);
        var $msg = $('<small style="padding-left:10px;"></small>').insertAfter($this);

        var __verify = function(){
            $msg.removeClass('text-danger text-success').addClass('text-muted').html('<i class="fa fa-spin fa-spinner"></i> Testing...');

            $.post('/jpanel/settings/infusionsoft/test', null, function(is_connected){
                if (is_connected)
                    $msg.removeClass('text-muted').addClass('text-success').html('<i class="fa fa-thumbs-up"></i> Works!');
                else
                    $msg.removeClass('text-muted').addClass('text-danger').html('<i class="fa fa-thumbs-down"></i> Failed!');
            },'json');
        }

        $this.click(function(ev){ ev.preventDefault(); __verify(); });

        $this.addClass('infusionsoft-test-init');
    });

    $('.paypal-test').not('paypal-test-init').each(function(i, el){
        var $this = $(el);
        var $msg = $('<small style="padding-left:10px;"></small>').insertAfter($this);

        var __verify = function(){
            $msg.removeClass('text-danger text-success').addClass('text-muted').html('<i class="fa fa-spin fa-spinner"></i> Testing...');

            $.get('/jpanel/paypal/verify_connection.json', function(is_connected){
                if (is_connected)
                    $msg.removeClass('text-muted').addClass('text-success').html('<i class="fa fa-thumbs-up"></i> Works!');
                else
                    $msg.removeClass('text-muted').addClass('text-danger').html('<i class="fa fa-thumbs-down"></i> Failed!');
            },'json');
        }

        $this.click(function(ev){ ev.preventDefault(); __verify(); });

        $this.addClass('paypal-test-init');
    });

    $('.paypal-reference-test').not('paypal-test-init').each(function(i, el){
        var $this = $(el);
        var $msg = $('<small style="padding-left:10px;"></small>').insertAfter($this);

        var __verify = function(){
            $msg.removeClass('text-danger text-success').addClass('text-muted').html('<i class="fa fa-spin fa-spinner"></i> Testing...');

            $.get('/jpanel/paypal/verify_reference_transactions.json', function(is_connected){
                if (is_connected)
                    $msg.removeClass('text-muted').addClass('text-success').html('<i class="fa fa-thumbs-up"></i> Works!');
                else
                    $msg.removeClass('text-muted').addClass('text-danger').html('<i class="fa fa-thumbs-down"></i> Failed!');
            },'json');
        }

        $this.click(function(ev){ ev.preventDefault(); __verify(); });

        $this.addClass('paypal-test-init');
    });

    $('.gocardless-test').not('gocardless-test-init').each(function(i, el){
        var $this = $(el);
        var $msg = $('<small style="padding-left:10px;"></small>').insertAfter($this);

        var __verify = function(){
            $msg.removeClass('text-danger text-success').addClass('text-muted').html('<i class="fa fa-spin fa-spinner"></i> Testing...');

            $.get('/jpanel/settings/payment/gocardless/verify', function(is_connected){
                if (is_connected)
                    $msg.removeClass('text-muted').addClass('text-success').html('<i class="fa fa-thumbs-up"></i> Works!');
                else
                    $msg.removeClass('text-muted').addClass('text-danger').html('<i class="fa fa-thumbs-down"></i> Failed!');
            },'json');
        }

        $this.click(function(ev){ ev.preventDefault(); __verify(); });

        $this.addClass('gocardless-test-init');
    });

    $.dsTxn = function () {
        if ($('.ds-txn').length == 0) return;

        $('.ds-txn').not('.-ds-txn-complete').each(function(i,el){
            $(el).click(function(ev){
                ev.preventDefault();

                // load modal
                var modal = j.templates.render('txnModalTmpl');
                $('body').append(modal);
                $(modal).on('show.bs.modal', function () {
                    $(this).find('.modal-dialog').velocity('transition.flipYIn', {duration:500});
                });
                $(modal).modal();
                var $modal = $(modal);

                $modal.on('hidden.bs.modal', function () {
                    $modal.remove();
                });

                var txnId = $(el).data('txn-id');

                var populate = function(){
                    $.post('/jpanel/transactions/'+txnId+'/modal', null, function(output) {
                        $modal.find('.modal-body-wrapper').html(output);

                        $modal.find('.txn-issue-tax-receipt').click(function(ev){
                            ev.preventDefault();

                            $modal.find('.modal-body-wrapper').html('<div class="modal-body"><span class="text-muted"><i class="fa fa-spin fa-circle-o-notch"></i> Issuing tax receipt...</span></div>');

                            $.post('/jpanel/transactions/'+txnId+'/issue_tax_receipt',function(json){
                                if (json.status === 'success')
                                    populate();
                                else {
                                    populate();
                                    toastr['error'](json.message);
                                }
                            })
                        });

                        $modal.find('.txn-resync').click(function(ev){
                            ev.preventDefault();

                            if (!confirm('Are you sure you want to push this transaction to DonorPerfect?\n\n- A new gift id will be created in DonorPerfect.\n- If there was a previous gift id associated with this transaction, you should delete it directly in DP.'))
                                return;

                            $modal.find('.modal-body-wrapper').html('<div class="modal-body"><span class="text-muted"><i class="fa fa-spin fa-circle-o-notch"></i> Syncing with DonorPerfect...</span></body>');

                            $.post('/jpanel/transactions/'+txnId+'/sync_dpo',function(json){
                                if (json.status === 'success') {
                                    populate();
                                    $('#transactionHistory').DataTable().draw();
                                } else {
                                    populate();
                                    toastr['error'](json.message);
                                }
                            })
                        });

                        $modal.find('.txn-refund').click(function(ev){
                            ev.preventDefault();

                            if (!confirm('Are you sure you want to refund the entire amount of this transaction?'))
                                return;

                            $modal.find('.modal-body-wrapper').html('<div class="modal-body"><span class="text-muted"><i class="fa fa-spin fa-circle-o-notch"></i> Refunding...</span></body>');

                            $.post('/jpanel/transactions/'+txnId+'/refund',function(json){
                                if (json.status === 'success') {
                                    populate();
                                    $('#transactionHistory').DataTable().draw();
                                } else {
                                    populate();
                                    toastr['error'](json.message);
                                }
                            })
                        });

                        $modal.find('.txn-refresh').click(function(ev){
                            ev.preventDefault();

                            $modal.find('.modal-body-wrapper').html('<div class="modal-body"><span class="text-muted"><i class="fa fa-spin fa-circle-o-notch"></i> Refreshing Payment Status...</span></body>');

                            $.post('/jpanel/transactions/'+txnId+'/refresh-payment-status',function(json){
                                if (json.status === 'success') {
                                    populate();
                                    $('#transactionHistory').DataTable().draw();
                                } else {
                                    populate();
                                    toastr['error'](json.message);
                                }
                            })
                        });
                    });
                };

                populate();

                return $modal;
            });

            $(el).addClass('-ds-txn-complete');
        });
    };
    $.dsTxn();

    j.taxReceipt.init();
    j.tribute.init();

    $.dpDonor = function () {
        if ($('.dp-donor').not('.dp-donor-init').length == 0) return;

        $('.dp-donor').each(function(i,el){
            j.dpdonor.init($(el));
        });
    }
    $.dpDonor();

    $.dpGift = function () {
        if ($('.dp-gift').not('dp-gift-init').length == 0) return;

        $('.dp-gift').each(function(i,el){
            $(el).click(function(ev){
                ev.preventDefault();
                j.dpgift.show({id:$(this).data('gift')});
            });

            $(el).addClass('dp-gift-init');
        });
    }
    $.dpGift();

    $.dsSponsor = function () {
        if ($('.ds-sponsor').not('.ds-sponsor-init').length == 0) return;

        $('.ds-sponsor').not('.ds-sponsor-init').each(function(i,el){
            $(el).click(function(ev){
                ev.preventDefault();

                if ($(this).data('sponsorId'))
                    j.dsSponsor.show({id:$(this).data('sponsorId')});

                if ($(this).data('sponsorshipId'))
                    j.dsSponsor.show({'sponsorshipId':$(this).data('sponsorshipId')});

            });

            $(el).addClass('ds-sponsor-init');
        });
    }
    $.dsSponsor();

    $.dsProducts = function () {
        if ($('.ds-products').not('.ds-products-init, .selectize-control, .selectize-dropdown').length == 0) return;

        var url = '/jpanel/products.json';

        if ($.dsProducts.items) {
            processItems($.dsProducts.items);
        } else {
            $.getJSON(url, processItems);
        }

        function filterItems(items, is_donation) {
            return $.grep(items, function (item) {
                if (
                    typeof is_donation === 'undefined'
                    || (is_donation && item['has_donation_variant'])
                    || (!is_donation && !item['has_donation_variant'])
                ) {
                    return true;
                }

                return false;
            });
        }

        function processItems(items){
            items = (items === null) ? [] : items;
            $.dsProducts.items = items;

            $('.ds-products').not('.ds-products-init, .selectize-control, .selectize-dropdown').each(function(i, input){
                // cache input reference
                var $input = $(input);
                var is_donation = $input.data('is-donation');
                var select_items = filterItems(items, is_donation);

                // if its empty, show a tool tip
                if (select_items.length === 0) {
                    $input.after($('<small class="text-info ds-products-warning"><i class="fa fa-exclamation-circle"></i> This product does not exist.</small>'));
                }

                var selectedProductIds = $input.val();
                if (typeof selectedProductIds === 'string') {
                    selectedProductIds = selectedProductIds.split(',');
                }

                // if the current value of the field isn't in the list from DPO,
                // we need to force the current value into the list of options so it doesn't get lost
                var productOptionIds = $.map(select_items, function (v) { return v.id; });
                $.each(selectedProductIds, function (i, product_id) {
                    if ($.inArray(product_id, productOptionIds) === -1) {
                        select_items.push({
                            'id': product_id,
                            'name': 'Unknown Product',
                            'thumbnail': false,
                            'code': 'ID: ' + product_id
                        });
                    }
                });

                $input.selectize({
                    persist      : false,
                    maxItems     : ($input.prop('multiple')) ? $input.data('maxItems') || 20 : 1,
                    valueField   : 'id',
                    labelField   : 'name',
                    sortField    : 'name',
                    searchField  : ['name', 'code'],
                    options      : select_items,
                    render: {
                        option: function(item) {
                            return '<div>' +
                                ((item.thumbnail) ? '<img src="' + item.thumbnail + '" class="selectize-option-avatar"> ' : '<div class="selectize-option-avatar"></div>') +
                                item.name + '<br>' +
                                '<small class="text-muted">' + item.code + '</small>' +
                            '</div>';
                        },
                        item:function(item){
                            return '<div>' +
                                ((item.thumbnail) ? '<img src="' + item.thumbnail + '" class="selectize-item-avatar"> ' : '') +
                                item.name +
                                ' <small class="text-muted">' + item.code + '</small>' +
                            '</div>';
                        }
                    }
                });

                $input.addClass('dp-products-init');
            });
        }
    }
    $.dsProducts();

    $.dsUrls = function () {
        if ($('.ds-urls').not('.ds-urls-init, .selectize-control, .selectize-dropdown').length == 0) return;

        $.getJSON('/jpanel/urls.json', function(items){
            $('.ds-urls').not('.ds-urls-init, .selectize-control, .selectize-dropdown').each(function(i, input){

                // cache input reference
                var $input = $(input);

                // make sure we have an array
                if (items === null) items = [];

                // if its empty, show a tool tip
                if (items.length === 0) {
                    $input.after($('<small class="text-info ds-urls-warning"><i class="fa fa-exclamation-circle"></i> This URL is an external link.</small>'));
                }

                // if the current value of the field isn't in the list from DPO,
                // we need to force the current value into the list of options so it doesn't get lost
                var current_val_exists = false;
                $.each(items,function(i,item){
                    if (item.code === $input.val()) {
                        current_val_exists = true;
                        return;
                    }
                });
                if (!current_val_exists) {
                    items.push({'url': $input.val()});
                }

                $input.selectize({
                    persist      : false,
                    maxItems     : 1,
                    valueField   : 'url',
                    labelField   : 'url',
                    sortField    : 'name',
                    create       : true,
                    createOnBlur : true,
                    searchField  : ['name', 'url'],
                    options      : items,
                    render: {
                        option: function(item) {
                            if (typeof item.name === 'undefined')
                                return '<div>' +
                                    item.url + '<br>' +
                                    '<small class="text-info"><i class="fa fa-exclamation-circle"></i> This is not a page on your site.</small>' +
                                '</div>';

                            if (item.type === 'category')
                                return '<div><i class="fa fa-tags fa-fw"></i> ' +
                                    item.name + '<br>' +
                                    '<small class="text-muted">' + item.url + '</small>' +
                                '</div>';

                            if (item.type === 'product')
                                return '<div><i class="fa fa-tag fa-fw"></i> ' +
                                    item.name + '<br>' +
                                    '<small class="text-muted">' + item.url + '</small>' +
                                '</div>';

                            if (item.type === 'page')
                                return '<div><i class="fa fa-file fa-fw"></i> ' +
                                    item.name + '<br>' +
                                    '<small class="text-muted">' + item.url + '</small>' +
                                '</div>';

                            if (item.type === 'post')
                                return '<div><i class="fa fa-rss fa-fw"></i> ' +
                                    item.name + '<br>' +
                                    '<small class="text-muted">' + item.url + '</small>' +
                                '</div>';
                        },
                        item:function(item){
                            if (typeof item.name === 'undefined')
                                return '<div>' +
                                    item.url + ' <small class="text-info"><i class="fa fa-exclamation-circle"></i> This is not a page on your site.</small>' +
                                '</div>';

                            if (item.type === 'category')
                                return '<div><i class="fa fa-tags fa-fw"></i> ' +
                                    item.name +
                                    ' <small class="text-muted">' + item.url + '</small>' +
                                '</div>';

                            if (item.type === 'product')
                                return '<div><i class="fa fa-tag fa-fw"></i> ' +
                                    item.name +
                                    ' <small class="text-muted">' + item.url + '</small>' +
                                '</div>';

                            if (item.type === 'page')
                                return '<div><i class="fa fa-file fa-fw"></i> ' +
                                    item.name +
                                    ' <small class="text-muted">' + item.url + '</small>' +
                                '</div>';

                            if (item.type === 'post')
                                return '<div><i class="fa fa-rss fa-fw"></i> ' +
                                    item.name +
                                    ' <small class="text-muted">' + item.url + '</small>' +
                                '</div>';
                        }
                    }
                });

                $input.addClass('dp-urls-init');
            });
        });
    }
    $.dsUrls();

    $.dsMembers = function () {
        if ($('.ds-members').not('.ds-members-init, .selectize-control, .selectize-dropdown').length == 0) return;

        $('.ds-members').not('.ds-members-init, .selectize-control, .selectize-dropdown').each(function(i, input){

            // cache input reference
            var $input = $(input);

            $input.selectize({
                maxItems     : 1,
                valueField   : 'id',
                labelField   : 'display_name',
                sortField    : 'display_name',
                create       : false,
                preload      : 'focus',
                placeholder  : 'Find a supporter...',
                searchField  : ['display_name', 'email', 'display_bill_address'],
                load: function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        url: '/jpanel/supporters.json?query=' + encodeURIComponent(query),
                        type: 'GET',
                        dataType: 'json',
                        error: function() {
                            callback();
                        },
                        success: function(members) {
                            callback(members);
                        }
                    });
                },
                render: {
                    option: function(item) {
                        return '<div>' +
                            '<i class="fa ' + item.icon + ' fa-fw"></i>' + item.display_name + ((item.email || item.display_bill_address) ? '<br>':'') +
                            ((item.email) ? '<small class="text-muted">'+item.email+'</small>':'') +
                            ((item.display_bill_address) ? '<small class="text-muted">'+item.display_bill_address+'</small>':'') +
                        '</div>';
                    },
                    item:function(item){
                        return '<div>' +
                            '<i class="fa ' + item.icon + '  fa-fw"></i>' + item.display_name + ( (item.email) ? ' <small class="text-muted">' + item.email + '</small>' : '' ) +
                        '</div>';
                    }
                }
            });

            $input.addClass('ds-members-init');
        });
    }
    $.dsMembers();

    $.dsVariants = function(){
        if ($('.ds-variants').not('.ds-variants-init, .selectize-control, .selectize-dropdown').length == 0) return;

        var url = '/jpanel/variants.json?';

        if ($.dsVariants.items) {
            processItems($.dsVariants.items);
        } else {
            $.getJSON(url, processItems);
        }

        function filterItems(items, is_donation, exclude) {
            if (typeof exclude === 'string') {
                exclude = exclude.split(',');
            } else if (!Array.isArray(exclude)) {
                exclude = [];
            }
            var excluded_ids = $.map(exclude, function (excluded_id) {
                return $.toNumber(excluded_id);
            });
            return $.grep(items, function( item ) {
                if (
                    $.inArray(item.id, excluded_ids) === -1
                    && (
                        typeof is_donation === 'undefined'
                        || (is_donation && item['is_donation'])
                        || (!is_donation && !item['is_donation'])
                    )
                ) {
                    return true;
                }
                return false;
            });
        }

        function processItems(items) {
            items = (items === null) ? [] : items;
            $.dsVariants.items = items;

            $('.ds-variants').not('.ds-variants-init, .selectize-control, .selectize-dropdown').each(function(i, input){
                // cache input reference
                var $input = $(input);
                var is_donation = $input.data('is-donation');
                var exclude = $input.data('exclude');
                var select_items = filterItems(items, is_donation, exclude);

                // if its empty, show a tool tip
                if (select_items.length === 0) {
                    $input.after($('<small class="text-info ds-products-warning"><i class="fa fa-exclamation-circle"></i> This variant does not exist.</small>'));
                }

                var selectedVariantIds = $input.val();
                if (typeof selectedVariantIds === 'string') {
                    selectedVariantIds = selectedVariantIds.split(',');
                }

                // if the current value of the field isn't in the list from DPO,
                // we need to force the current value into the list of options so it doesn't get lost
                var variantOptionIds = $.map(select_items, function (v) { return v.id; });
                $.each(selectedVariantIds, function (i, variant_id) {
                    if ($.inArray(variant_id, variantOptionIds) === -1) {
                        select_items.push({
                            'id': variant_id,
                            'name': 'Unknown Product',
                            'variant_name': 'Unknown Variant',
                            'price': 0,
                            'is_donation': false,
                            'thumbnail': false,
                            'code': 'ID: ' + variant_id
                        });
                    }
                });

                $input.selectize({
                    persist      : false,
                    maxItems     : ($input.prop('multiple')) ? $input.data('maxItems') || 15 : 1,
                    valueField   : 'id',
                    labelField   : 'variant_name',
                    sortField    : 'variant_name',
                    create       : false,
                    placeholder  : 'Find a product...',
                    searchField  : ['variant_name', 'code', 'name'],
                    options      : select_items,
                    render: {
                        option: function(item) {
                            return '<div>' +
                                ((item.thumbnail) ? '<img src="' + item.thumbnail + '" class="selectize-option-avatar"> ' : '<div class="selectize-option-avatar"></div>') +
                                item.name + ((item.variant_name) ? ' - ' + item.variant_name:'') +
                                ' <small class="text-muted">'+item.code+'</small>' +
                            '</div>';
                        },
                        item:function(item){
                            item.name = item.name || 'Unknown';
                            item.price = item.price || 0;
                            return '<div data-price="'+item.price.formatMoney()+'" data-name="'+item.name + ((item.variant_name) ? ' - ' + item.variant_name:'')+'">' +
                                ((item.thumbnail) ? '<img src="' + item.thumbnail + '" class="selectize-item-avatar"> ' : '') +
                                item.name + ((item.variant_name) ? ' - ' + item.variant_name:'') + ( (item.code) ? ' <small class="text-muted">' + item.code + '</small>' : '' ) +
                            '</div>';
                        }
                    }
                });

                $input.addClass('ds-variant-selector-init');
            });
        }
    }
    $.dsVariants();

    $.dsDownloads = function(){
        if ($('.ds-downloads').not('.ds-downloads-init, .selectize-control, .selectize-dropdown').length == 0) return;

        $('.ds-downloads').not('.ds-downloads-init, .selectize-control, .selectize-dropdown').each(function(i, input){

            // cache input reference
            var $input = $(input);

            $input.selectize({
                maxItems     : 1,
                valueField   : 'id',
                labelField   : 'name',
                sortField    : 'name',
                create       : false,
                placeholder  : 'Find a file...',
                searchField  : ['name'],
                load: function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        url: '/jpanel/downloads.json?query=' + encodeURIComponent(query),
                        type: 'GET',
                        dataType: 'json',
                        error: function() {
                            callback();
                        },
                        success: function(downloads) {
                            callback(downloads);
                        }
                    });
                },
                render: {
                    option: function(item) {
                        return '<div>' +
                            '<i class="fa fa-fw ' + item.fa_icon + '"></i> ' +
                            item.name +
                            ' <small class="text-muted">'+item.size_formatted+'</small>' +
                        '</div>';
                    },
                    item:function(item){
                        return '<div>' +
                            item.name +
                        '</div>';
                    }
                }
            });

            $input.addClass('ds-downloads-selector-init');
        });
    }
    $.dsDownloads();

    $.dpoCodes = function(context){

        if (typeof context === 'undefined') { context = 'body'; }

        // codes autocomplete
        $(context).find('input.dpo-codes').not('.dpo-codes-init, .selectize-control').each(function(i, input){

            $.getJSON('/jpanel/donor/codes/' + $(input).data('code') + '.json', function(items){
                // cache input reference
                var $input = $(input);

                // make sure we have an array
                if (items === null) items = [];

                // if its empty, show a tool tip
                if (items.length === 0) {
                    $input.after($('<small class="text-danger dpo-codes-warning"><i class="fa fa-exclamation-triangle"></i> No '+$(input).data('code')+' codes in DPO.</small>'));
                }

                // if the current value of the field isn't in the list from DPO,
                // we need to force the current value into the list of options so it doesn't get lost
                var current_val_exists = false;
                $.each(items,function(i,item){
                    if (item.code === $input.val()) {
                        current_val_exists = true;
                        return;
                    }
                });
                if (!current_val_exists) {
                    items.push({'code': $input.val()});
                }

                $input.selectize({
                    persist: false,
                    maxItems: 1,
                    valueField: 'code',
                    labelField: 'code',
                    sortField: 'code',
                    create: true,
                    createOnBlur: true,
                    searchField: ['code', 'description'],
                    options: items,
                    render: {
                        option: function(item) {
                            if (item.code === '') return '<div class="hide"></div>';

                            if (typeof item.description === 'undefined')
                                return '<div>' +
                                    item.code + '<br>' +
                                    '<small class="text-danger"><i class="fa fa-exclamation-triangle"></i> \'' + item.code + '\' does not exist in DonorPerfect.</small>' +
                                '</div>';

                            return '<div>' +
                                item.code + '<br>' +
                                '<small class="text-muted">' + item.description + '</small>' +
                            '</div>';
                        },
                        item:function(item){
                            if (typeof item.description === 'undefined')
                                return '<div>' +
                                    item.code + ' <small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Missing in DPO</small>' +
                                '</div>';

                            return '<div>' +
                                item.code + ' <small class="text-muted">' + item.description + '</small>' +
                            '</div>';
                        }
                    }
                });

                $input.addClass('dpo-codes-init');
            });
        });
    }
    $.dpoCodes();

    $.ajaxModals = function(){
        $('[data-toggle="ajax-modal"]').not('.-ajax-modal').click(function(ev){
            ev.preventDefault();

            var target = $(this).attr('href');
            if (!target) {
                target = $(this).data('target');
            }

            var $modal = showModal().data('original-target', target);

            fillModal($modal, target);
        }).addClass('-ajax-modal');

        var showModal = function(){
            return $('<div class="modal fade ajax-modal">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                    '</div>' +
                '</div>' +
            '</div>')
            .append('body')
            .on('show.bs.modal', function () {
                if (!$(this).is(':visible')) {
                    $(this).find('.modal-dialog').velocity('transition.flipYIn', {duration:500});
                }
            })
            .on('hidden.bs.modal', function(){
                $(this).remove();
            })
            .on('shown.bs.modal', function(){
                onLoadContent($(this));
            })
            .modal('show');
        }

        var onLoadContent = function ($modal) {
            j.ui.formatSpecialFields();

            $('form').not('.-ajax-modal-form').submit(function(ev){
                ev.preventDefault();

                $modal.find('.modal-content')
                    .html('<div class="text-center text-muted" style="margin:60px;"><i class="fa fa-4x fa-spin fa-spinner"></i></div>');

                $.post($(this).attr('action'), $(this).serializeArray(), function (data) { onAjaxComplete(data, $modal); })
                    .fail(function(err) {
                        var laravelErrors = err.responseJSON && err.responseJSON.errors ? err.responseJSON.errors : false;
                        if (laravelErrors) {
                            onAjaxComplete({ error: Object.keys(laravelErrors)
                                .map(function (attribute) { return laravelErrors[attribute]; })
                                .join(' ')
                            }, $modal)
                        }
                    });
            }).addClass('-ajax-modal-form');

            $('[data-ajax-modal-link]').not('.-ajax-modal-link').click(function(ev){
                ev.preventDefault();

                if ($(this).data('ajax-confirm')) {
                    if (!confirm($(this).data('ajax-confirm'))) {
                        return;
                    }
                }

                $modal.find('.modal-content')
                    .html('<div class="text-center text-muted" style="margin:60px;"><i class="fa fa-4x fa-spin fa-spinner"></i></div>');

                $.get($(this).data('ajax-modal-link'), function (data) { onAjaxComplete(data, $modal); });
            }).addClass('-ajax-modal-link');
        }

        var fillModal = function($modal, target){
            $modal.find('.modal-content')
                .html('<div class="text-center text-muted" style="margin:60px;"><i class="fa fa-4x fa-spin fa-spinner"></i></div>');

            $modal.find('.modal-content').load(target, function(){
                onLoadContent($modal);
            });
        }

        var onAjaxComplete = function(data, $modal){
            if (data.success) {
                toastr['success'](data.success);
            } else if (data.error) {
                toastr['error'](data.error);
            }

            if ($('table.dataTable').length > 0)
                $('table.dataTable').DataTable().draw();
            else
                window.location.reload();

            if (data['ajax-modal-action'] && data['ajax-modal-action'] == 'close') {
                $modal.modal('hide');
            }

            if (data['ajax-modal-action'] && data['ajax-modal-action'] == 'refresh') {
                $modal.modal('hide');
                window.location.reload();
            }

            if (data['ajax-modal-redirect']) {
                $modal.data('original-target',data['ajax-modal-redirect']);
            }

            fillModal($modal, $modal.data('original-target'));
        }
    };

    $.ajaxModals();
}
