/* globals j */

import $ from 'jquery';

export default {
    formatTable:function($table){
        var $master = $table.find('input.master');
        $table.data('master-checkbox', $master);

        $master.click(function(){
            var $slaves = $table.find('input:enabled.slave');
            $slaves.prop('checked', $(this).prop('checked'));
            j.ui.datatable.updateCount($table);
        });

        j.ui.datatable.formatRows($table);

    },
    formatRows:function($table){
        var $master = $table.data('master-checkbox');

        if (!$master) return;

        var $slaves = $table.find('input.slave');
        $slaves.click(function() {
            if (!$(this).attr('checked')) {
                $master.removeAttr('checked');
            }
            j.ui.datatable.updateCount($table);
        });
    },
    updateCount:function($table){
        var count = $table.find('input.slave:checked').length;
        if (count == 0) count = '';
        $('.checkbox-counter').html(count).velocity("callout.bounce");
        $('.checkbox-counter-export').html(count ? '(' + count + ')' : 'All');
    },
    values:function(table_id) {
        var $table = $(table_id);
        var checkboxes = $table.find('input.slave:checked');
        var vals = [];
        checkboxes.each(function(i,e){ vals.push($(e).val()); });
        return vals;
    },
    enableFilters:function(datatable, callback) {
        if (datatable) {
            datatable.on('draw.dt', function(){
                $('th input[type=checkbox].master').prop('checked', false);
                j.ui.datatable.updateCount($('.datatable'));
            });
        }

        $('.datatable-filters input[type!=hidden], .datatable-filters select').each(function(i, input){

            if ($(input).data('datepicker')) {
                $(input).on('changeDate', function () {
                    datatable.draw();
                    if (callback) callback(datatable);
                });

            } else if ($(input).hasClass('selectized')) {

                $(input).selectize().on('change', function () {
                    datatable.draw();
                    if (callback) callback(datatable);
                });

            } else if(input.getAttribute('data-date-range-picker') !== null) {
                $(input).on('apply.daterangepicker cancel.daterangepicker', function(){
                    datatable.draw();
                });
            } else {
                $(input).change(function(){
                    datatable.draw();
                    if (callback) callback(datatable);
                });
            }
        });

        function elementHasValue ($el) {
            let inputNotEmpty = false;
            const $input = $el.find('input.form-control,select.form-control');
            const $selectize = $el.find('.selectize.form-control,.locally-selectize.form-control');

            if ($selectize.length > 0) {
                if ($selectize.data('selectize') && $selectize.data('selectize').getValue().length > 0) {
                    return true;
                } else {
                    return false;
                }
            }

            $input.each(function () {
                if ($(this).val() !== '') {
                    inputNotEmpty = true;
                }
            });

            return inputNotEmpty;
        }

        function hideRelevantFilters () {
            $('.more-field').each(function(i, el){
                const $el = $(el);
                if (!elementHasValue($el)) {
                    $el.addClass('hide');
                } else {
                    $el.removeClass('hide');
                }
            });
        }

        $('.toggle-more-fields').on('click', function (ev) {
            ev.preventDefault();

            var $this = $(this);

            if ($this.data('switch') == undefined) {
                $this.data('switch', false);
            }

            // hide
            if ($this.data('switch')) {
                // only hide those with no value set
                hideRelevantFilters();
                $this.html('More Filters').data('switch', false);

            // show
            } else {
                $('.more-field').removeClass('hide');
                $this.html('Less Filters').data('switch', true);
            }
        })

        $('form.datatable-filters').on('submit', function(ev){
            ev.preventDefault();
        });

        hideRelevantFilters();
    },
    filterValues: function(selector){
        var $el = $(selector);
        var data = {};
        if ($el.hasClass('dataTable')) {
            var datatable = $el.dataTable();
            var osettings = datatable.fnSettings();
            if (osettings.ajax && osettings.ajax.data) {
                osettings.ajax.data(data);
            } else {
                var fields = $('.datatable-filters').serializeArray();
                $.each(fields,function(i, field){
                    data[field.name] = field.value;
                });
            }
        }
        return data;
    }
};
