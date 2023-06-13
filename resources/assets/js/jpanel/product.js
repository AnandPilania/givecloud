/* globals j */

import $ from 'jquery';
import Vue from 'vue';
import AccountDesignations from '@app/components/Product/AccountDesignations';

export default {
    init:function(){
        if ($('#productForm').length === 0) return;

        $('#productForm').bootstrapValidator({
            fields:{
                code: {
                    validators: {
                        notEmpty: {
                            message: 'The product code/sku is required'
                        },
                        regexp: {
                            regexp: /^[0-9a-z-]+$/i,
                            message: 'The product code/sku must not contain any spaces or special characters.'
                        },
                        remote: {
                            url: '/jpanel/products/validate_sku',
                            // Send { username: 'its value', email: 'its value' } to the back-end
                            data: function(validator) {
                                return {
                                    product_id: validator.getFieldElements('id').val(),
                                    code: validator.getFieldElements('code').val()
                                };
                            },
                            message: 'The SKU is already in use. The SKU must be unique.',
                            type: 'POST'
                        }
                    }
                }
            },
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            }
        });

        j.product.onOutOfStockChange();
        j.product.accountDesignations();
        j.product.fields.init();
    },
    onOutOfStockChange:function(){
        var flag = $('#outofstock_allow').val() == 0;
        if (flag) $('#outofstock_message_wrap').css({display:'block'});
        else $('#outofstock_message_wrap').css({display:'none'});
    },
    accountDesignations() {
        var $el = $('#account-designation-app');

        const app = new Vue({
            el: $el[0],
            components: { 'account-designations': AccountDesignations },
            template: `
                <account-designations :class="classes"
                    :dp-enabled="dpEnabled"
                    :has-variant-level-coding="hasVariantLevelCoding"
                    :disable-supporters-choice="disableSupportersChoice"
                    :options="options"
                ></account-designations>
            `,
            data: {
                classes: $el.attr('class'),
                dpEnabled: $el.data('dp-enabled'),
                hasVariantLevelCoding: $el.data('has-variant-level-coding'),
                disableSupportersChoice: $el.data('disable-supporters-choice'),
                options: $el.data('options'),
            },
        });

        $('#template-suffix').on('change', function() {
            app.disableSupportersChoice = 'page-with-payment' !== $(this).val();
        });
    },
    fields:{
        init:function(){
            j.product.fields.populate($.parseJSON($('#fieldsJson').html()));
        },
        populate:function(data){
            var table = $('#fields_blocks');
            var template = j.templates.compile('fieldsTmpl');

            function getDefaultField($choice) {
                return $choice.parents('.productfields').find('> input[name$="[default_value]"]');
            }

            function getInputsNextIndex($choices) {
                var choiceInputs = Array.from($choices.find('> .choice:not(.hide) input[data-index][name$="[value]"]'));

                if (choiceInputs.length === 0) {
                    return 0;
                }

                return ++choiceInputs
                    .map(function (i) { return i.dataset['index'] })
                    .sort(function (a, b) { return b - a; })[0];
            }

            function setChoiceInputIndex($choice) {
                var choiceInputsMaxIndex = getInputsNextIndex($choice.parents('.choices'));

                $choice.find('input[name*="[_choice_id_]"]')
                    .each(function (i, choice) {
                        $(choice).attr('data-index', choiceInputsMaxIndex);

                        var inputNewIndex = $(choice).data('name').replace('_choice_id_', choiceInputsMaxIndex);
                        $(choice).attr('data-name', inputNewIndex);
                        $(choice).attr('name', inputNewIndex);
                    });
            }

            function setChoiceIcon($choice, toDefault) {
                toDefault = toDefault || false;
                var $defaultChoiceIcon = $choice.find('.btn.default-choice > i');

                if (toDefault) {
                    $defaultChoiceIcon.removeClass('text-gray-400');
                } else {
                    $defaultChoiceIcon.addClass('text-gray-400');
                }
            }

            table.on('change', '.productfields_options_wrap .checkbox input[type=checkbox]', function() {
                var $wrap = $(this).parents('.productfields_options_wrap');
                var $simple = $wrap.find('.simple');
                var $choices = $wrap.find('.choices');
                var choices = [];
                if ($(this).is(':checked')) {
                    var html = $choices.find('> .clone').html();
                    $choices.find('> .choice').remove();
                    choices = $simple.find('textarea').val().split("\n");
                    choices.forEach(function(choice) {
                        var $choice = $(html);

                        $choice.find('.col-sm-3 input').val(choice);
                        $choice.find('.col-sm-7 input').val(choice);
                        $choices.append($choice);

                        var defaultOption = getDefaultField($choice).val();
                        $choice.find('[data-name]').each(function() {
                            $(this).attr('name', $(this).data('name'));
                            if ($(this).attr('name').indexOf('[value]') > -1) {
                                setChoiceIcon($choice, $(this).val() === defaultOption);
                            }
                        });
                        setChoiceInputIndex($choice);
                    });
                    $simple.addClass('hide').find('textarea').val('');
                    $choices.removeClass('hide');
                } else {
                    $simple.find('textarea').val('');
                    $choices.find('> .choice').each(function() {
                        choices.push($(this).find('.col-sm-7 input').val());
                    });
                    $simple.find('textarea').val(choices.join("\n"));
                    $simple.find('input[id$="_options_default"]').val(getDefaultField($choices).val());
                    $choices.addClass('hide').find('> .choice').remove();
                    $simple.removeClass('hide');
                }
            });

            table.on('click', '.choice button.choice-up', function() {
                var choice = $(this).parents('.choice');
                var prevChoice = choice.prev('.choice');
                if (prevChoice.length) {
                    choice.detach().insertBefore(prevChoice);
                }
            });

            table.on('click', '.choice button.choice-down', function() {
                var choice = $(this).parents('.choice');
                var nextChoice = choice.next('.choice');
                if (nextChoice.length) {
                    choice.detach().insertAfter(nextChoice);
                }
            });

            table.on('click', '.choice button.add-choice', function() {
                var $choices = $(this).parents('.choices');
                var $choice = $($choices.find('> .clone').html());

                $choice.find('[data-name]').each(function() {
                    $(this).attr('name', $(this).data('name'));
                });
                $(this).parents('.choice').after($choice);
                $choice.find('.col-sm-3 input').focus();

                setChoiceInputIndex($choice);
            });

            table.on('click', '.choice button.remove-choice', function() {
                var $choices = $(this).parents('.productfields_options_wrap').find('.choices > .choice');
                if ($choices.length > 1) {
                    $(this).parents('.choice').remove();
                }
            });

            table.on('click', '.choice button.default-choice', function() {
                var choiceToSelect = $(this).parents('.choice');
                var allChoices = choiceToSelect.parents('.productfields').find('.choice');
                var $choiceDefaultToSelect = getDefaultField(choiceToSelect);

                if (choiceToSelect.length === 0 || allChoices.length === 0 || $choiceDefaultToSelect.length === 0) {
                    console.error('Choices elements are missing.');
                    return;
                }

                var newOptionDefaultValue = choiceToSelect.find('input[name$="[value]"]').val();
                var isChoiceSelected = $choiceDefaultToSelect.val() === newOptionDefaultValue;
                allChoices.each(function (i, choice) {
                    setChoiceIcon($(choice));
                });
                $choiceDefaultToSelect.val(isChoiceSelected ? "" : newOptionDefaultValue);
                setChoiceIcon(choiceToSelect, ! isChoiceSelected);
            });

            table.on('input', 'input[id$="_options_default"]', function() {
                getDefaultField($(this)).val($(this).val());
            });

            table.on('input', 'input[id$="_hidden_default"]', function() {
                getDefaultField($(this)).val($(this).val());
            });

            // loop through each
            $.each(data,function(i,e){
                var newRow = null,
                    data = $.extend({}, e); // create a object that will populate the template

                // correct the data for the template
                data['_dom_id'] = e.id;
                data['_isnew'] = 0;
                data['default_value'] = e.default_value || null;
                data['product_meta_options'] = $('#product_meta_options').html();

                // populate row
                newRow = $(template(data)); // create the new row using the template and data
                newRow.appendTo(table); // append new row to form

                // autoselect the proper map-to-dpo field if it exists
                if ($.trim(data.map_to_product_meta) !== '')
                    newRow.find('.-map_to_product_meta option[value='+data.map_to_product_meta+']').attr('selected','selected');

                j.product.fields.onTypeChange(e.id);
                j.product.fields.onUDFChange(e.id);
            });

            j.product.fields.onRowCountChange();
        },
        add:function(){
            var table = $('#fields_blocks'),
                newRow = null,
                data = {}; // create a object that will populate the template

            var template = j.templates.compile('fieldsTmpl');

            var sequence = $('#fields_blocks>div').length + 1;

            // correct the data for the template
            data['_dom_id'] = j.util.rand();
            data['id'] = '';
            data['_isnew'] = '1';
            data['default_value'] = null;
            data['sequence'] = sequence;
            data['type'] = 'text';
            data['name'] = '';
            data['hint'] = '';
            data['body'] = '';
            data['options'] = '';
            data['isrequired'] = '1';
            data['product_meta_options'] = $('#product_meta_options').html();

            // populate row
            newRow = $(template(data)); // create the new row using the template and data
            newRow.appendTo(table); // append new row to form
            j.product.fields.onTypeChange(data['_dom_id']);

            j.product.fields.onRowCountChange();
            j.product.fields.onUDFChange(data['_dom_id']);
            newRow.find('.focus-first').focus();
        },
        remove:function(id){
            var isnew = $('#productfields-table-row-'+id+' input[name=\'productfields['+id+'][_isnew]\']').val();

            if (!isnew) {
                if (!confirm('Are you sure you want to delete this row?')) { return; }
            }

            $('#productfields-table-row-'+id).remove();

            if (isnew !== '1') {
                $('#productForm').append($('<input type="hidden" name="productfields['+id+'][id]" value="'+id+'" />'));
                $('#productForm').append($('<input type="hidden" name="productfields['+id+'][_isdelete]" value="1" />'));
            }

            j.product.fields.onRowCountChange();
        },
        onRowCountChange:function(){
            var table = $('#productfields-table'),
                rows = table.find('tbody tr');

            // deail with single rows
            if (rows.length === 0) {
                //j.product.fields.add();
                //rows = table.find('tbody tr');
            }

            if (rows.length === 1) {
                table.find('.-hide-if-single-row').css({display:'none'});
            } else {
                table.find('th.-hide-if-single-row').css({display:'table-cell'});
                table.find('td.-hide-if-single-row').css({display:'table-cell'});
                table.find('a.-hide-if-single-row').css({display:'inline'});
            }
        },
        onTypeChange:function(id){
            var type = $('#productfields_'+id+'_type').val(),
                options = $('.productfields_'+id+'_options_wrap'),
                hidden = $('.productfields_'+id+'_hidden_wrap'),
                settings = $('.productfields_'+id+'_settings_wrap'),
                body = $('.productfields_'+id+'_body_wrap'),
                hint = $('.productfields_'+id+'_hint_wrap');

            options.addClass('hide');
            hidden.addClass('hide');
            hint.addClass('hide');
            settings.addClass('hide');
            body.addClass('hide');

            if (type == 'select' || type == 'multi-select') {
                options.removeClass('hide');
            }

            if (type == 'hidden') {
                hidden.removeClass('hide');
            }

            if (type == 'html') {
                body.removeClass('hide');
                body.find('textarea.html:not(:tinymce)').givecloudeditor({
                    content_css: window.Givecloud.settings.tinymce_css
                });
            }

            if (type != 'html') {
                hint.removeClass('hide');
                settings.removeClass('hide');
            }
        },
        onUDFChange:function(id){
            // make sure no other udf select boxes have the same udf selected
            var cur_val = $('#map_to_product_meta-'+id).val(),
                all_other_udfs = $('.-map_to_product_meta').not('#map_to_product_meta-'+id);

            all_other_udfs.each(function(i,udf){
                if ($(udf).val() == cur_val) $(udf)[0].selectedIndex = 0;
            });
        }
    }
};
