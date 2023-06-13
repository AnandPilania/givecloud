
import $ from 'jquery';
import _ from 'lodash';
import axios from 'axios';
import moment from 'moment';
import Sugar from 'sugar';
import toastr from 'toastr';
import Vue from 'vue';
import j from '@app/jpanel';

const pos = {
    searchTimeout: 1000,
    order: null,
    orderDefaults:null,
    paymentApp:null,
    lastPaymentType:null,
    showRemainingHintsBelowQty: 20,

    init:function(){

        setTimeout(function(){ $('.preloader').fadeOut(function(){ $(this).remove(); }); }, 800);

        $('input.date').datepicker({ format: 'M d, yyyy', autoclose:true });

        // bind all ui events
        $('.product-search').bind('keyup', pos.events.onSearchKeyUp);
        $('.product-search-reset').bind('click', pos.search.reset);
        $('#order-details-form').bind('submit',pos.events.onDetailsSave);
        $('#order-promo-form').bind('submit',pos.events.onPromoSave);
        $('#order-select-child').bind('submit',pos.events.onAddChild);
        $('#order-select-fundraiser').bind('submit',pos.events.onAddFundraiser);
        $('.save-defaults').bind('click',pos.events.onSaveDefaults);
        $('.clear-defaults').bind('click',pos.events.onClearDefaults);

        $('.pos-save-account').bind('click',pos.events.onDetailsSave);
        $('.pos-save-guest').bind('click',function(ev){
            $('#member_id').data('selectize').clear();
            pos.events.onDetailsSave(ev);
        });
        $('input[name=dcc_type]').bind('change', pos.events.onDccTypeChange);
        $('input[name=referral_source]').bind('change', pos.events.onReferralSourceChange);

        $('.copy-billing').bind('click',pos.events.onCopyBilling);
        $('.copy-shipping').bind('click',pos.events.onCopyShipping);

        $('.pos-show-bill-address').bind('click',function(){
            $('#modal-addresses').bind('shown.bs.modal', function(){
                $(this).find('a[href="#pos-bill-address-tab"]').click();
                $(this).find('input[name=bill_first_name]').focus();
                $(this).unbind('show.bs.modal');
            }).modal('show');
        })

        $('.pos-show-ship-address').bind('click',function(){
            $('#modal-addresses').bind('shown.bs.modal', function(){
                $(this).find('a[href="#pos-ship-address-tab"]').click();
                $(this).find('input[name=ship_first_name]').focus();
                $(this).unbind('show.bs.modal');
            }).modal('show');
        })

        $('.modal-account-select-btn').bind('click',function(ev){
            ev.preventDefault();
            $('#modal-account').modal('hide');
        });

        $('.modal-account-guest-btn').bind('click',function(ev){
            ev.preventDefault();
            $('#modal-account').modal('hide');
        });

        $('.pos-show-payment').bind('click',pos.payment.showModal);

        pos.payment.init();

        $(window).bind('resize', pos.events.resizePanels);

        // member look-up
        $.dsMembers = function () {
            if ($('.ds-members').not('.ds-members-init, .selectize-control').length == 0) return;

            $('.ds-members').not('.ds-members-init, .selectize-control').each(function(i, input){

                // cache input reference
                var $input = $(input);

                $input.selectize({
                    maxItems     : 1,
                    valueField   : 'id',
                    labelField   : 'display_name',
                    sortField    : 'display_name',
                    create       : false,
                    preload      : 'focus',
                    placeholder  : 'Start typing to search for a supporter...',
                    searchField  : ['display_name', 'email', 'display_bill_address', 'display_bill_phone'],
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
                        option: function (item) {
                            var displayName = item.display_name;
                            if (item.email) {
                                displayName += ' <small class="text-muted">' + item.email + '</span>';
                            }
                            var meta = '';
                            var spacer = '&nbsp;&nbsp;&nbsp;&nbsp;';
                            if (item.display_bill_address) {
                                meta += '<i class="fa fa-envelope-o"></i> ' + item.display_bill_address;
                                if (item.display_bill_phone !== null) {
                                    meta += ' <i class="fa fa-phone"></i> ' + item.display_bill_phone;
                                }
                                meta += spacer;
                            }
                            if (item.membership_name !== null) {
                                meta += '<i class="fa fa-heart"></i> ' + item.membership_name;
                                if (item.membership_expires_on !== null) {
                                    meta += ' - ' + item.membership_expires_on;
                                }
                                if (item.membership_is_expired) {
                                    meta += '(Expired)';
                                }
                            }
                            return '<div>' +
                                '<i class="fa ' + item.icon + '"></i> ' + displayName + (meta ? '<br><small class="text-muted">' + meta + '</small>': '') +
                            '</div>';
                        },
                        item:function(item){
                            return '<div>' +
                                '<i class="fa ' + item.icon + '"></i> ' + item.display_name + ( (item.email) ? ' <small class="text-muted">' + item.email + '</small>' : '' ) +
                            '</div>';
                        }
                    }
                });

                $input.addClass('ds-members-init');
            });
        }
        $.dsMembers();

        // promocode look-up
        $.dsPromocodes = function () {
            if ($('.ds-promocodes').not('.ds-promocodes-init, .selectize-control').length == 0) return;

            $('.ds-promocodes').not('.ds-promocodes-init, .selectize-control').each(function(i, input){

                // cache input reference
                var $input = $(input);

                $input.selectize({
                    valueField   : 'code',
                    labelField   : 'code',
                    sortField    : 'code',
                    create       : true, // this allows for QUICK entry of a promocode before the server replies with matches
                    createOnBlur : true,
                    maxItems     : 1,
                    preload      : 'focus',
                    placeholder  : 'Select promocode(s)...',
                    searchField  : ['code'],
                    load: function(query, callback) {
                        if (!query.length) return callback();
                        $.ajax({
                            url: '/jpanel/promocodes.json?query=' + encodeURIComponent(query),
                            type: 'GET',
                            dataType: 'json',
                            error: function() {
                                callback();
                            },
                            success: function(codes) {
                                callback(codes);
                            }
                        });
                    }
                });

                $input.addClass('ds-promocodes-init');
            });
        }
        $.dsPromocodes();

        // fundraisers look-up
        $.dsFundraisers = function () {
            if ($('.ds-fundraisers').not('.ds-fundraisers-init, .selectize-control').length == 0) return;

            $('.ds-fundraisers').not('.ds-fundraisers-init, .selectize-control').each(function(i, input){

                // cache input reference
                var $input = $(input);

                $input.selectize({
                    maxItems     : 1,
                    valueField   : 'id',
                    labelField   : 'title',
                    sortField    : 'title',
                    create       : false,
                    preload      : 'focus',
                    placeholder  : 'Search for a fundraising page...',
                    searchField  : ['title', 'url', 'author'],
                    load: function(query, callback) {
                        $.ajax({
                            url: '/jpanel/fundraisers.json?query=' + encodeURIComponent(query),
                            type: 'GET',
                            dataType: 'json',
                            error: function() {
                                callback();
                            },
                            success: function(pages) {
                                callback(pages);
                            }
                        });
                    },
                    render: {
                        option: function(item) {
                            return '<div>' +
                                ((item.thumbnail) ? '<img src="' + item.thumbnail + '" class="selectize-option-avatar"> ' : '<div class="selectize-option-avatar"></div>') +
                                item.title +
                                '<br><small class="text-muted"><i class="fa fa-fw fa-user-circle"></i> '+item.author+'</small>' +
                            '</div>';
                        },
                        item:function(item){
                            return '<div>' +
                                ((item.thumbnail) ? '<img src="' + item.thumbnail + '" class="selectize-option-avatar"> ' : '<div class="selectize-option-avatar"></div>') +
                                item.title + '<br><small class="text-muted"><i class="fa fa-fw fa-user-circle"></i> ' + item.author + '</small>' +
                            '</div>';
                        }
                    }
                });

                $input.addClass('ds-fundraisers-init');
            });
        }
        $.dsFundraisers();

        pos.search.init();

        pos.events.resizePanels();

        var subdivisions = {};
        $('select[name=bill_country],select[name=ship_country],select[name=tax_country]').change(function(){
            function updateSubdivisions(data) {
                $state.html('<option></option>');
                _.forEach(data.subdivisions, function(label, value) {
                    $('<option>')
                        .html($country.is('[name^=tax_]') ? value : label)
                        .attr('value', value)
                        .appendTo($state);
                });
                $state.val(state);
            }
            var $country = $(this);
            var $state = $country.parents('.tab-pane').find('select[name$=_state]');
            var country = $country.val();
            var state = $state.val();
            if (subdivisions[country]) {
                updateSubdivisions(subdivisions[country]);
            } else {
                $state.html('<option></option>');
                $.getJSON('https://app.givecloud.co/services/locale/' + country + '/subdivisions.json', function(data) {
                    subdivisions[country] = data;
                    updateSubdivisions(data);
                });
            }
        });

        $.getJSON('https://app.givecloud.co/services/locale/countries.json', function(data){
            delete data.countries['CA'];
            delete data.countries['US'];
            $.each(data.countries, function(country_code, country_name) {
                var option = $('<option/>').attr('value', country_code).text(country_name);
                $('#pos-bill-address-tab select[name=bill_country]').append(option.clone());
                $('#pos-ship-address-tab select[name=ship_country]').append(option.clone());
            });
            $('#pos-bill-address-tab select[name=bill_country]').val(window._settings.default_country).trigger('change');
            $('#pos-ship-address-tab select[name=ship_country]').val(window._settings.default_country).trigger('change');
        });

        pos.cart.draw();
        pos.currency.render();
    },

    currency: {
        active: window.Givecloud?.config?.currency,
        render: function() {
            if (window.Givecloud.config.currencies.length > 1) {
                var html = j.templates.render('currencyDropdownTmpl', {
                    currency: pos.currency.active,
                    currencies: window.Givecloud.config.currencies,
                });
                $('#currencyDropdown').html(html);
            } else {
                $('#currencyDropdown').empty();
            }
        },
        get: function(currencyCode) {
            return _.find(window.Givecloud.config.currencies, { code: currencyCode  });
        },
        set: function(currencyCode) {
            var currency = pos.currency.get(currencyCode);
            if (currency) {
                pos.currency.active = currency;
            }
        },
        use: function(currencyCode) {
            pos.currency.set(currencyCode);
            pos.currency.render();

            if (pos.order) {
                axios.post('/jpanel/pos/'+pos.order.id+'/update', { currency_code: currencyCode }).then(function(res) {
                    pos.order = res.data.order;
                    pos.cart.draw();
                    pos.search.showCategory(true);
                });
            } else {
                pos.cart.draw();
                pos.search.showCategory(true);
            }
        },
    },

    search:{
        categories : null,

        init:function(){
            pos.search.drawBookmarks();
            pos.search.loadCategories();
        },

        loadCategories:function(){
            pos.search.paneState('loading');

            var success = function (json) {
                if (json.length === 0 && ! $('.product-list').hasClass('p2p-enabled') && ! $('.product-list').hasClass('sponsorships-enabled')) {
                    return pos.search.paneState('empty');
                }

                pos.search.categories = json;
                pos.search.showCategory();
            };

            var error = function () {
                pos.search.paneState('empty');
            };

            $.ajax({
                type     : 'post',
                url      : '/jpanel/pos/categories.json',
                success  : success,
                error    : error,
                dataType : 'json'
            });
        },

        showCategory:function(category){
            var $category;

            if (category === true) {
                category = pos.search.category || null;
            } else {
                pos.search.category = category;
            }

            var is_categories_root = (typeof category === 'undefined' || category === null);
            var categories = is_categories_root ? pos.search.categories : category.child_categories;

            // clear canvas
            $('.product-list').empty();

            // back
            if (typeof category !== 'undefined' && category !== null) {
                var $back = $('<div class="category category-back col-xs-12 col-md-4 col-lg-3 text-center">' +
                        '<span class="fa-stack fa-4x fa-lg">' +
                            '<i class="fa fa-circle fa-stack-2x"></i>' +
                            '<i class="fa fa-arrow-left fa-stack-1x fa-inverse"></i>' +
                        '</span>' +
                    '</div>').appendTo('.product-list');
                $back.on('click', function(){ pos.search.backToCategoryId(category.parent_id); });
            }

            if (categories.length > 0) {
                $.each(categories, function(e, cat){
                    var $category = $(pos.search.categoryHtml(cat)).appendTo('.product-list');

                    // add thumbs
                    if (cat.product_thumbs && cat.product_thumbs.length > 0) {
                        var $category_thumbs = $category.find('.category-thumbs');
                        $category_thumbs.addClass('category-thumbs-'+cat.product_thumbs.length);
                        $.each(cat.product_thumbs, function(j, thumb){
                            $category_thumbs.append($('<div class="category-thumb" style="background-image:url(\''+thumb+'\');">'));
                        });
                    }

                    $category.on('click', function(ev){ ev.preventDefault(); pos.search.showCategory(cat); });
                });
            }

            // show child option if in categories root
            if ($('.product-list').hasClass('sponsorships-enabled')){
                if (is_categories_root) {
                    $category = $(pos.search.categoryHtml({'name':'Child Sponsorship'})).appendTo('.product-list');
                    $category.find('.category-thumbs').append($('<div class="category-thumb"></div>'));
                    $category.on('click', function(ev){ ev.preventDefault(); $('#modal-select-child').modal(); });
                }
            }

            // show child option if in categories root
            if ($('.product-list').hasClass('p2p-enabled')){
                if (is_categories_root) {
                    $category = $(pos.search.categoryHtml({'name':'Fundraising Pages'})).appendTo('.product-list');
                    $category.find('.category-thumbs').append($('<div class="category-thumb"></div>'));
                    $category.on('click', function(ev){ ev.preventDefault(); $('#modal-select-fundraiser').modal(); });
                }
            }

            if (!is_categories_root) {
                pos.search.findByCategoryId(category.id);
            }
        },

        backToCategoryId:function(category_id){

            if (category_id == null)
                pos.search.showCategory();

            var _find_id = function(cats){
                $.each(cats,function(ix, cat){
                    if (cat.id === category_id)
                        pos.search.showCategory(cat);

                    if (cat.child_categories.length > 0)
                        _find_id(cat.child_categories);
                });
            }
            _find_id(pos.search.categories);
        },

        categoryHtml:function(category){
            return '<div class="category col-xs-12 col-md-4 col-lg-3">' +
                '<div class="thumbnail">' +
                    '<div class="category-thumbs">' +
                    '</div>' +
                    '<div class="caption clearfix">' +
                        '<div class="category-name">' + category.name + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        },

        find:function (keywords) {
            if ($.trim(keywords) === '')
                return pos.search.paneState('find');

            pos.search.paneState('loading');

            var success = function (json) {
                if (json.length === 0)
                    return pos.search.paneState('empty');

                pos.search.drawItems(json);
            };

            var error = function () {
                pos.search.paneState('empty');
            };

            $.ajax({
                type     : 'post',
                url      : '/jpanel/pos/products.json',
                data     : { keywords: keywords, currency_code: pos.currency.active.code },
                success  : success,
                error    : error,
                dataType : 'json'
            });
        },

        findByCategoryId:function (categoryId) {

            var $loading = $('<div class="category category-loading col-xs-12 col-md-4 col-lg-3 text-center text-muted">' +
                '<i class="fa fa-4x fa-spin fa-circle-o-notch"></i>' +
            '</div>').appendTo('.product-list');

            var success = function (json) {
                $loading.remove();
                pos.search.drawItems(json, false);
            };

            var error = function () {
                $loading.remove();
            };

            $.ajax({
                type     : 'post',
                url      : '/jpanel/pos/products.json',
                data     : { category_id: categoryId, currency_code: pos.currency.active.code },
                success  : success,
                error    : error,
                dataType : 'json'
            });
        },

        paneState:function(state){
            $('.product-list').empty();

            if (state === 'empty') {
                $('<div class="text-placeholder text-center col-sm-6 col-sm-offset-3"><i class="fa fa-frown-o fa-4x"></i><h1>Nothing Found</h1></div>').appendTo('.product-list');
            } else if (state === 'find') {
                $('<div class="text-placeholder text-center col-sm-6 col-sm-offset-3"><i class="fa fa-arrow-up fa-4x"></i><h1>Find a Product</h1></div>').appendTo('.product-list');
            } else if (state === 'loading') {
                $('<div class="text-placeholder text-center col-sm-6 col-sm-offset-3"><i class="fa fa-circle-o-notch fa-spin fa-4x"></i></div>').appendTo('.product-list');
            }
        },

        reset:function(){
            $('.product-search').val('');
            pos.search.paneState('find');
            $('.product-search').focus();
            pos.search.showCategory();
        },

        drawBookmarks: function(){
            $('.bookmark-list').empty();

            if (window._settings.product_bookmarks.length > 0) {
                $.each(window._settings.product_bookmarks, function(i, product){
                    var $bookmark = $(pos.search.bookmarkHtml(product)).appendTo('.bookmark-list'),
                        $thumb = $bookmark.find('.thumbnail');

                    $thumb.data('product', product);

                    $thumb.bind('click', pos.search.itemClick);
                });
            } else {
                $('.bookmark-list').append('<div class="col-xs-12 text-muted" style="margin:8px 0px;"><i class="fa fa-star"></i> Pin Your Favorites Here!</div>');
            }

            pos.events.resizePanels();
        },

        bookmarkHtml: function(product){
            return '<div class="col-md-2 bookmark-item col-sm-3 col-xs-4">' +
                    '<div class="thumbnail">' +
                        '<div class="thumb" style="background-image:url(\'' + product.thumbnail_url + '\');"></div>' +
                        product.name +
                    '</div>' +
                '</div>';
        },

        drawItems:function(json, clear_all){

            if (typeof clear_all === 'undefined')
                clear_all = true;

            if (clear_all)
                $('.product-list').empty();

            $.each(json, function(i, product){

                var $item = $(pos.search.itemHtml(product)).appendTo('.product-list'),
                    $thumb = $item.find('.thumbnail');

                $thumb.attr('tabindex', i+1)
                    .data('product', product);

                // mouse click
                $thumb.bind('click', pos.search.itemClick);

                // allow some elements to have their own click events
                $thumb.find('.no-propagation').bind('click',function(ev){ ev.stopPropagation(); });

                // enter key
                $thumb.bind('keyup', function(ev){
                    if(ev.keyCode == 13){
                        $thumb.click();
                    }
                });

                $thumb.find('.bookmark').click(function(ev){ ev.stopPropagation(); pos.search.itemBookmark(ev); });

                setTimeout(function(){$item.css('opacity',1);}, i*50);
            });
        },

        itemBookmark:function(ev){
            var $bookmark = $(ev.currentTarget),
                $thumb = $bookmark.parent(),
                product = $thumb.data('product');

            $bookmark.toggleClass('bookmarked');

            $bookmark.find('.fa-star').removeClass('fa-star').addClass('fa-spin fa-spinner');

            $.ajax({
                type     : 'post',
                url      : '/jpanel/pos/bookmark/' + ($bookmark.hasClass('bookmarked') ? 'add' : 'remove'),
                data     : {'product_id':product.id},
                success  : function(data){
                    $bookmark.find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-star');
                    toastr['success']('Saved');
                    window._settings.product_bookmarks = data;
                    pos.search.drawBookmarks();
                },
                error    : function(){
                    $bookmark.find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-star');
                    toastr['error']('Failed to save bookmark.');
                    $bookmark.toggleClass('bookmarked');
                },
                dataType : 'json'
            });
        },

        itemClick:function(ev){
            ev.preventDefault();

            var product = $(this).data('product');

            var $qty = $(this).find('.qty-selector');
            var qty = $qty.length ? $qty.val() : 1;

            pos.search.itemModal(product, qty);
        },

        itemHtml:function(product){
            var currency = pos.currency.active;

            if (product.default_variant) {
                product.default_variant.price = (product.default_variant.price) ? product.default_variant.price : 0;
            }

            var bookmarked = false;
            $.each(window._settings.product_bookmarks, function(i,p){
                if (p.id == product.id) {
                    bookmarked = true;
                    return false;
                }
            });

            return '<div class="product col-xs-12 col-md-4 col-lg-3 ' + ((product.available_for_purchase === 0 || !product.is_available_for_sale)?'not-clickable':'') + '">' +
                '<div class="thumbnail">' +
                    '<div class="bookmark ' + (bookmarked ? 'bookmarked' : '') + '">' +
                        '<span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x fa-inverse bookmark-shadow"></i><i class="star fa fa-star fa-stack-1x"></i></span>' +
                    '</div>' +
                    '<div class="product-thumb" style="background-image:url(\'' + product.thumbnail_url + '\');">' +
                        ( (product.variants.length === 1 && !product.outofstock_allow) ? '<div class="product-flag-left product-flag-warning">'+ product.variants[0].quantity +' in stock</div>' : '' ) +
                        ( (product.available_for_purchase === 0 || !product.is_available_for_sale) ? '<div class="product-flag product-flag-danger">Sold Out</div>' : '' ) +
                        ( (product.available_for_purchase > 0 && product.available_for_purchase < pos.showRemainingHintsBelowQty) ? '<div class="product-flag product-flag-warning"><strong>'+product.available_for_purchase+'</strong> Left</div>' : '' ) +
                        ( (product.default_variant && product.default_variant.is_sale) ? '<div class="product-flag product-flag-info">On Sale</div>' : '' ) +
                    '</div>' +
                    '<div class="caption clearfix">' +
                        '<div class="product-name">' + product.name + '</div>' +
                        '<div class="product-code">' + product.code + '</div>' +
                        //'<div class="product-actions pull-right"><a href="#" class="btn btn-success btn-xs" data-toggle="modal" data-target="#modal-one-time-donation" role="button"><i class="fa fa-plus"></i> Add</a></div>' +
                        ((product.default_variant) ? ('<div class="product-price pull-left">' + ( (product.default_variant.is_donation) ? '&nbsp;' : ( (product.default_variant.is_sale)?('<span class="price-sale">'+currency.symbol+product.default_variant.saleprice.formatMoney()+'</span> <span class="price-strike">'+currency.symbol+product.default_variant.price.formatMoney()+'</span>'):(currency.symbol+product.default_variant.price.formatMoney()) )) + '</div>') : '&nbsp;') +
                    '</div>' +
                    '<div class="actions text-right" style="margin-right:4px; margin-top:-23px;">' +
                        '<div style="width:85px; display:inline-block;">' +
                            ((!product.hide_qty) ? '<div class="input-group input-group-sm"><select class="form-control qty-selector no-propagation"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option><option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option><option>22</option><option>23</option><option>24</option><option>25</option></select><div class="input-group-btn"><a href="#" class="btn btn-success"><i class="fa fa-chevron-right"></i></a></div></div>' : '<a href="#" style="margin-bottom:5px;" class="btn btn-sm btn-success"><i class="fa fa-chevron-right"></i></a>') +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        },

        itemModal:function(product, qty){

            if (typeof qty === 'undefined') {
                qty = 1;
            }

            if (!product.default_variant) {
                return alert('No default variant set.');
            }

            // if this item has no options, add it to cart immediately
            if (product.variants.length === 1 && !product.default_variant.is_donation && product.default_variant.billing_period === 'onetime' && product.custom_fields.length === 0) {
                return pos.cart.addItem({
                    'variant_id' : product.default_variant.id,
                    'qty'        : qty
                });
            }

            // continue the modal process
            var $modal = $(pos.search.itemModalHtml(product)).appendTo('body'),
                $form = $modal.find('form'),
                $body = $modal.find('.modal-body');

            // product title
            $body.append('<h1>'+product.name+' <small>'+product.code+'</small></h1>');

            // if there are available for purchase
            if (product.available_for_purchase > 0) {

                // choose variants
                if (product.variants.length > 1) {
                    $modal.find('.modal-dialog').removeClass('modal-sm'); // larger dialog for custom fields
                    // populate variants
                    $body.append($(pos.search.itemModalVariantHtml(product.variants, product.default_variant.id, product.outofstock_allow)));
                // one variant (defaulted and hidden)
                } else {
                    $form.append($('<input type="hidden" name="variant_id" value="'+product.default_variant.id+'" data-is-donation="' + product.default_variant.is_donation + '" data-billing-period="' + product.default_variant.billing_period + '" data-recurring-first-payment="' + (product.default_variant.billing_starts_on ? 0 : 1) + '">'));
                }

                $form.find('input[name=variant_id]').change(function(){

                    // choose price
                    if ($(this).data('is-donation') == 1) {
                        if (!$body.find('input[name=amount]').length) {
                            $body.append('<div class="form-group">' +
                                '<label>Donation Amount</label>' +
                                '<div class="input-group input-group-lg">' +
                                    '<div class="input-group-addon">' + pos.currency.active.symbol + '</div>' +
                                    '<input type="text" class="form-control text-right" name="amount" value="" autocomplete="off" placeholder="0.00">' +
                                '</div>' +
                            '</div>');
                        }
                    } else {
                        $body.find('input[name=amount]').closest('.form-group').remove();
                    }

                    // recurring
                    var billing_period = $(this).data('billing-period');
                    var recurring_first_payment = $(this).data('recurring-first-payment');

                    $body.find('.recurring-options-wrap').remove();

                    if (billing_period != 'onetime') {
                        $body.append(pos.search.itemRecurringHtml(product));
                        $body.find('.days-wrap, .week-days-wrap, .initial-charge-wrap').addClass('hide');

                        $form.find('input[name=recurring_frequency]').val(billing_period);

                        if (window._settings.rpp_default_type == 'fixed') {
                            if (recurring_first_payment) {
                                $body.find('.initial-charge-wrap').removeClass('hide');
                            } else {
                                $body.find('.initial-charge-wrap').addClass('hide');
                            }

                            if (billing_period == 'weekly' || billing_period == 'biweekly') {
                                $body.find('.week-days-wrap').removeClass('hide');
                            } else {
                                $body.find('.days-wrap').removeClass('hide');
                            }
                        }
                    }
                }).first().trigger('change');


                // custom fields
                if (product.custom_fields.length > 0) {
                    $modal.find('.modal-dialog').removeClass('modal-sm'); // larger dialog for custom fields
                    // populate all fields
                    $.each(product.custom_fields, function(i, field) {
                        $body.append($(pos.search.itemModalFieldHtml(field)));
                    })

                    $form.find('input.date').datepicker({ format: 'M d, yyyy', autoclose: true });

                    $form.find('.multi-select-required input[type=checkbox]').on('change', function(){
                        var $parent = $(this).parents('.multi-select-required');
                        var $inputs = $parent.find('input[type=checkbox]');
                        if ($inputs.filter(':checked').length) {
                            $inputs.removeAttr('required');
                        } else {
                            $inputs.attr('required', true);
                        }
                    });
                }

                // qty
                if (product.hide_qty === false) {
                    $modal.find('.modal-footer').append('<div class="form-group pull-left qty-wrap">' +
                        '<div class="input-group input-group-lg">' +
                            '<input type="number" class="form-control" name="qty" value="' + qty + '" placeholder="Qty" max="' + product.available_for_purchase + '">' +
                            '<div class="input-group-btn">' +
                                '<button type="button" class="btn btn-info qty-up"><i class="fa fa-plus fa-fw"></i></button>' +
                                '<button type="button" class="btn btn-info qty-down"><i class="fa fa-minus fa-fw"></i></button>' +
                            '</div>' +
                        '</div>' +
                        ( (product.available_for_purchase < pos.showRemainingHintsBelowQty) ? '<small class="text-muted">Only <span class="label label-default">' + product.available_for_purchase + '</span> remaining</small>' : '' ) +
                    '</div>');

                    $modal.find('.qty-up').bind('click',function(ev){ ev.preventDefault(); pos.search.itemQty(1); });
                    $modal.find('.qty-down').bind('click',function(ev){ ev.preventDefault(); pos.search.itemQty(-1); });
                } else if (product.available_for_purchase < pos.showRemainingHintsBelowQty) {
                    $modal.find('.modal-footer').append('<div class="form-group pull-left qty-wrap">' +
                        '<small class="text-muted">Only <span class="label label-warning">' + product.available_for_purchase + '</span> remaining</small>' +
                    '</div>');
                }

                // product title
                $body.append(pos.search.itemCodeOverridesHtml());

                // gift aid
                if (window.Givecloud.config.gift_aid && product.is_tax_receiptable) {
                    $body.append(
                        '<div class="form-group">' +
                            '<label>Gift Aid Eligible:</label>' +
                            '<div data-toggle="buttons">' +
                                '<label class="btn btn-lg btn-default">' +
                                    '<input type="radio" name="gift_aid" value="1" autocomplete="off"> <i class="fa fa-check"></i> Yes' +
                                '</label>' +
                                '<label class="btn btn-lg btn-default active">' +
                                    '<input type="radio" name="gift_aid" value="0" autocomplete="off" checked> <i class="fa fa-check"></i> No' +
                                '</label>' +
                            '</div>' +
                        '</div>'
                    );
                }

                // focus first field
                $modal.bind('shown.bs.modal', function(){
                    $form.find('input, select, textarea').not('input[name=variant_id]').first().focus();
                });

                // focus first field
                $modal.bind('hidden.bs.modal', function(){
                    $modal.remove();
                });

                // handle form submission
                $form.bind('submit', function(ev, data){
                    ev.preventDefault();

                    pos.cart.addItem($(this).serializeArray());

                    var auto_hide = (typeof data != 'undefined' && data.do_not_close) ? false : true;

                    if (auto_hide) {
                        $modal.modal('hide');
                    }
                });

                $form.find('.add-and-add-again').bind('click', function(ev){
                    ev.preventDefault();
                    $form.trigger('submit', [{'do_not_close': true}]);
                });
            }

            $modal.modal();

            $modal.bind('shown.bs.modal',function(){
                // make sure we select either the default variant
                // or the first alternate option if there is no
                // clickable default variant
                $('label.variant-btn.clickable.-default-variant, label.variant-btn.clickable:first')
                    .first()
                    .click();
            });
        },

        itemQty:function(increment){
            var $qty = $('.item-modal input[name=qty]').first(),
                max = parseInt($qty.attr('max')),
                val = parseInt($qty.val());

            if (isNaN(val))
                val = 0;

            val += increment;

            if (val < 1)
                val = 1;

            if (val > max)
                val = max;

            $qty.val(val);
        },

        itemModalHtml:function(product){
            var add_btn = null;

            if (product.available_for_purchase == 0) {
                add_btn = '<span class="btn btn-lg disabled btn-outline btn-danger">Sold Out</span>';
            } else {
                add_btn = '<div class="btn-group btn-group-lg dropup">' +
                    '<button type="submit" class="btn btn-success btn-bold"><i class="fa fa-check"></i> Add</button>' +
                    '<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                        '<i class="fa fa-caret-up"></i>' +
                        '<span class="sr-only">Toggle Dropdown</span>' +
                    '</button>' +
                    '<ul class="dropdown-menu pull-right">' +
                        '<li><a href="#" class="add-and-add-again"><i class="fa fa-refresh"></i> Add &amp; Repeat</a></li>' +
                    '</ul>' +
                '</div>'
            }

            return '<div class="modal fade modal-success item-modal" id="item-modal-'+product.id+'" tabindex="-1">' +
                '<div class="modal-dialog modal-sm" role="document">' +
                    '<form>' +
                        '<div class="modal-content">' +
                            '<div class="modal-body"><a href="#" data-dismiss="modal" class="pull-right"><i class="fa fa-times"></i></a></div>' +
                            '<div class="modal-footer">' +
                                add_btn + //( (product.available_for_purchase == 0) ? '<span class="btn btn-lg disabled btn-outline btn-danger">Sold Out</span>' : '<button type="submit" class="btn btn-lg btn-success btn-bold"><i class="fa fa-check"></i> Add</button>' ) +
                            '</div>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>';
        },

        itemModalVariantHtml:function(variants, default_variant_id, outofstock_allow){
            var html = '';
            var currency = pos.currency.active;

            html += '<div class="form-group">' +
                '<label>Option:</label>' +
                '<div data-toggle="buttons">';

            if (outofstock_allow){
                $.each(variants, function(i, variant){

                    //if ($.trim(variant.variantname) == '') return;

                    html += '<label class="btn btn-lg btn-default variant-btn clickable '+((variant.id == default_variant_id)?'-default-variant':'')+'">' +
                            '<input type="radio" name="variant_id" value="'+variant.id+'" required autocomplete="off" data-is-donation="' + variant.is_donation + '" data-billing-period="' + variant.billing_period + '" data-recurring-first-payment="' + (variant.billing_starts_on ? 0 : 1) + '">' +
                            '<i class="fa fa-check"></i> ' + $.trim(variant.variantname) +
                            ((variant.is_donation) ? '' : ' (' + currency.symbol + variant.actual_price.formatMoney() + ' ' + ((variant.is_sale) ? '<small class="text-strike text-muted">' + currency.symbol + variant.price.formatMoney() + '</small>' : '') + ')') +
                        '</label>';
                });
            }else{
                $.each(variants, function(i, variant){

                    //if ($.trim(variant.variantname) == '') return;

                    html += '<label class="btn btn-lg btn-default ' + ((variant.quantity < 1)?'not-clickable disabled':'clickable') + '" id="'+variant.id+'">' +
                            '<div class="pos-quantity-remaining"><span class="pull-right label label-xs label-warning" style="font-size:50%;">' + variant.quantity + ' in stock</span></div>'+
                            '<input type="radio" name="variant_id" value="'+variant.id+'" '+' required autocomplete="off" data-is-donation="' + variant.is_donation + '" data-billing-period="' + variant.billing_period + '" data-recurring-first-payment="' + (variant.billing_starts_on ? 0 : 1) + '"> <i class="fa fa-check"></i> ' + $.trim(variant.variantname) +
                            ((variant.is_donation) ? '' : ' (' + currency.symbol + variant.actual_price.formatMoney() + ' ' + ((variant.is_sale) ? '<small class="text-strike text-muted">' + currency.symbol + variant.price.formatMoney() + '</small>' : '') + ')') +
                        '</label>';
                });
            }

            html += '</div>' +
                '</div>';

            return html;
        },

        itemModalFieldHtml:function(field){
            var html = '';
            var input_classes = '';
            var input_type = '';

            // select options
            if (field.type == 'select') {

                html += '<div class="form-group">' +
                    '<label>'+field.name+((field.isrequired)?'*':'')+':</label>' +
                    '<div data-toggle="buttons">';

                $.each(field.choices, function(i, opt){
                    html += '<label class="btn btn-lg btn-default">' +
                            '<input type="radio" name="fields['+field.id+']" value="'+opt.value+'" id="" '+((field.isrequired)?'required':'')+' autocomplete="off"> <i class="fa fa-check"></i> ' + opt.label +
                        '</label>';
                });

                html += '</div>' +
                    '</div>';

            // multi-select options
            } else if (field.type == 'multi-select') {

                html += '<div class="form-group'+(field.isrequired?' multi-select-required':'')+'">' +
                    '<label>'+field.name+((field.isrequired)?'*':'')+':</label>' +
                    '<div data-toggle="buttons">';

                $.each(field.choices, function(i, opt){
                    html += '<label class="btn btn-lg btn-default">' +
                            '<input type="checkbox" name="fields['+field.id+'][]" value="'+_.escape(opt.value)+'" id="" '+((field.isrequired)?'data-bv-choice="true" data-bv-choice-min="1" data-bv-choice-message="Please make at least one choice."':'')+' autocomplete="off"'+(field.isrequired?' required':'')+'> <i class="fa fa-check"></i> ' + opt.label +
                        '</label>';
                });

                html += '</div>' +
                    '</div>';

            // big-text
            } else if (field.type == 'lg_text') {

                html += '<div class="form-group">' +
                    '<label>'+field.name+((field.isrequired)?'*':'')+':</label>' +
                    '<textarea rows="4" class="form-control input-lg" name="fields['+field.id+']" '+((field.isrequired)?'required':'')+'></textarea>' +
                '</div>';

            // hidden
            } else if (field.type == 'hidden') {

                html += '<input type="hidden" name="fields['+field.id+']" value="' + _.escape(field.default_value) + '"/>';

            // check
            } else if (field.type == 'check') {

                html += '<div class="checkbox">' +
                    '<label>' +
                        '<input type="checkbox" name="fields['+field.id+']" '+((field.isrequired)?'required':'')+' value="1"> ' + field.name +
                    '</label>' +
                '</div>';

            // html
            } else if (field.type == 'html') {

                html += '<div class="form-group">'+field.body+'</div>';

            } else {

                if (field.type === 'date') {
                    input_classes = 'date';
                }

                if (field.type === 'email') {
                    input_type = 'email';
                } else if (field.type === 'phone') {
                    input_type = 'tel';
                } else {
                    input_type = 'text';
                }

                html += '<div class="form-group">' +
                    '<label>'+field.name+((field.isrequired)?'*':'')+':</label>' +
                    '<input type="'+input_type+'" class="form-control input-lg '+input_classes+'" name="fields['+field.id+']" value="' + _.escape(field.default_value) + '" '+((field.isrequired)?'required':'')+' placeholder="">' +
                '</div>';

            }

            return html;
        },

        itemRecurringHtml:function(){
            var $html = $('<div class="recurring-options-wrap">' +
                '<input type="hidden" name="recurring_frequency">' +
                '<div class="form-group hide days-wrap">' +
                    '<label>Payment Day:</label>' +
                    '<div data-toggle="buttons" class="days"></div>' +
                '</div>' +
                '<div class="form-group hide week-days-wrap">' +
                    '<label>Payment Day:</label>' +
                    '<div data-toggle="buttons" class="week-days"></div>' +
                '</div>' +
                '<div class="form-group hide initial-charge-wrap">' +
                    '<label>Initial Charge:</label>' +
                    '<div data-toggle="buttons">' +
                        '<label class="btn btn-lg btn-default active">' +
                            '<input type="radio" name="recurring_with_initial_charge" value="1" id="" checked autocomplete="off"> <i class="fa fa-check"></i> Yes' +
                        '</label>' +
                        '<label class="btn btn-lg btn-default">' +
                            '<input type="radio" name="recurring_with_initial_charge" value="0" id="" autocomplete="off"> <i class="fa fa-check"></i> No' +
                        '</label>' +
                    '</div>' +
                '</div>' +
            '</div>');

            var $days_of_week_container = $html.find('.week-days');
            if (window._settings.payment_day_of_week_options) {
                var days = ['','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                $.each(window._settings.payment_day_of_week_options, function(ix, day){
                    $days_of_week_container.append($('<label class="btn btn-lg btn-default ' + ((ix==0)?'active':'') + '">' +
                            '<input type="radio" name="recurring_day_of_week" value="' + day + '" id="" ' + ((ix==0)?'checked':'') + ' autocomplete="off"> <i class="fa fa-check"></i> ' + days[parseInt(day)] +
                        '</label>'));
                });
            }

            var $days_of_month_container = $html.find('.days');
            if (window._settings.payment_day_options) {
                $.each(window._settings.payment_day_options, function(ix, day){
                    $days_of_month_container.append($('<label class="btn btn-lg btn-default ' + ((ix==0)?'active':'') + '">' +
                            '<input type="radio" name="recurring_day" value="' + day + '" id="" ' + ((ix==0)?'checked':'') + ' autocomplete="off"> <i class="fa fa-check"></i> ' + stThRd(day) +
                        '</label>'));
                });
            }

            return $html;
        },

        itemCodeOverridesHtml: function() {
            if (window._settings.dp_fields === false) {
                return;
            }

            var $html = $('<div id="item-code-overrides-wrap">' +
                    '<p><small><a href="#item-code-overrides" data-toggle="collapse">DonorPerfect Overrides</a></small></p>' +
                    '<div id="item-code-overrides" class="collapse">' +
                        '<div class="row"></div>' +
                    '</div>' +
                '</div>');

            if (window._settings.dp_fields) {
                $.each(window._settings.dp_fields, function(field, label){
                    if (typeof window._settings.dp_codes[field] !== 'undefined' && window._settings.dp_codes[field].length > 0) {
                        var $field = $('<div class="form-group-sm col-sm-4">' +
                                '<label>' + label + '</label>' +
                                '<select class="form-control" name="metadata[dp_'+field+']" value=""><option></option></select>' +
                            '</div>').appendTo($html.find('#item-code-overrides .row'));

                        $.each(window._settings.dp_codes[field], function(i, code){
                            $field.find('select').append($('<option value="'+code.code+'">' + code.description + '</option>'));
                        });
                    } else if (field === 'fair_market_value') {
                        $([
                            '<div class="form-group-sm col-sm-4">',
                                '<label>Fair Mkt. Value</label>',
                                '<select class="form-control" name="metadata[dp_fair_market_value]">',
                                    '<option value=""></option>',
                                    '<option value="N">Do Not Use</option>',
                                    '<option value="Y">Populate with Purchase Value</option>',
                                '</select>',
                            '</div>'
                        ].join('')).appendTo($html.find('#item-code-overrides .row'));
                    } else if (field === 'no_calc') {
                        $([
                            '<div class="form-group-sm col-sm-4">',
                                '<label>NoCalc</label>',
                                '<select class="form-control" name="metadata[dp_no_calc]">',
                                    '<option value=""></option>',
                                    '<option value="N">N</option>',
                                    '<option value="Y">Y</option>',
                                '</select>',
                            '</div>'
                        ].join('')).appendTo($html.find('#item-code-overrides .row'));
                    } else {
                        $('<div class="form-group-sm col-sm-4">' +
                                '<label>' + label + '</label>' +
                                '<input type="text" class="form-control" name="metadata[dp_'+field+']" value="">' +
                            '</div>').appendTo($html.find('#item-code-overrides .row'));
                    }
                });
            }

            if (window._settings.dp_udfs) {
                $.each(window._settings.dp_udfs, function(field, label){
                    if (typeof window._settings.dp_codes[field] !== 'undefined' && window._settings.dp_codes[field].length > 0) {
                        var $field = $('<div class="form-group-sm col-sm-4">' +
                                '<label>' + label + '</label>' +
                                '<select class="form-control" name="metadata[dpudf_'+field+']" value=""><option></option></select>' +
                            '</div>').appendTo($html.find('#item-code-overrides .row'));

                        $.each(window._settings.dp_codes[field], function(i, code){
                            $field.find('select').append($('<option value="'+code.code+'">' + code.description + '</option>'));
                        });
                    } else {
                        $('<div class="form-group-sm col-sm-4">' +
                                '<label>' + label + '</label>' +
                                '<input type="text" class="form-control" name="metadata[dpudf_'+field+']" value="">' +
                            '</div>').appendTo($html.find('#item-code-overrides .row'));
                    }
                });
            }

            return $html;
        }
    },


    events: {

        // resize the checkout panels
        resizePanels:function(){

            // portrait mobile
            if ($.isBreakpoint('sm') || $.isBreakpoint('xs')) {
                $('.pos-cart-items').css({
                    'min-height':'60px',
                    'max-height':'auto',
                    'height'    :'auto',
                });

            // all other devices (best display)
            } else {

                var h1 = Math.max(768, $(window).height()),
                    offset = $('.pos-totals').outerHeight()
                        + $('.pos-invoice').outerHeight()
                        + $('.pos-show-payment').outerHeight()
                        + 220,
                    h2 = h1-offset;

                $('.pos-cart-items').css({
                    'min-height':h2+'px',
                    'max-height':h2+'px',
                    'height'    :h2+'px',
                });

                $('.product-list').css({
                    'min-height':(h1-155-$('.bookmark-list').outerHeight())+'px',
                    'max-height':(h1-155-$('.bookmark-list').outerHeight())+'px',
                    'height'    :(h1-155-$('.bookmark-list').outerHeight())+'px',
                });
            }
        },

        // when you type in the search box
        onSearchKeyUp:function(ev) {

            // kill previous timeout
            if (typeof pos.events.searchKeyUpTimer !== 'undefined')
                clearTimeout(pos.events.searchKeyUpTimer);

            // what to do when we search
            var callback = function(){
                pos.search.find($(ev.target).val())
            }

            // enter key
            if (ev.keyCode == 13)
                return callback();

            // search timeout
            pos.events.searchKeyUpTimer = setTimeout(callback, pos.searchTimeout);
        },

        onCopyBilling:function(ev){
            ev.preventDefault();
            $('*[name=ship_title]').val($('*[name=bill_title]').val());
            $('input[name=ship_first_name]').val($('input[name=bill_first_name]').val());
            $('input[name=ship_last_name]').val($('input[name=bill_last_name]').val());
            $('input[name=ship_organization_name]').val($('input[name=bill_organization_name]').val());
            $('input[name=ship_address]').val($('input[name=bill_address]').val());
            $('input[name=ship_address2]').val($('input[name=bill_address2]').val());
            $('input[name=ship_city]').val($('input[name=bill_city]').val());
            $('select[name=ship_country]').val($('select[name=bill_country]').val()).change();
            $('select[name=ship_state]').val($('select[name=bill_state]').val());
            $('input[name=ship_zip]').val($('input[name=bill_zip]').val());
            $('input[name=ship_phone]').val($('input[name=bill_phone]').val());
            $('input[name=ship_email]').val($('input[name=bill_email]').val());
        },

        onCopyShipping:function(ev){
            ev.preventDefault();
            $('*[name=bill_title]').val($('*[name=ship_title]').val());
            $('input[name=bill_first_name]').val($('input[name=ship_first_name]').val());
            $('input[name=bill_last_name]').val($('input[name=ship_last_name]').val());
            $('input[name=bill_organization_name]').val($('input[name=ship_organization_name]').val());
            $('input[name=bill_address]').val($('input[name=ship_address]').val());
            $('input[name=bill_address2]').val($('input[name=ship_address2]').val());
            $('input[name=bill_city]').val($('input[name=ship_city]').val());
            $('select[name=bill_country]').val($('select[name=ship_country]').val()).change();
            $('select[name=bill_state]').val($('select[name=ship_state]').val());
            $('input[name=bill_zip]').val($('input[name=ship_zip]').val());
            $('input[name=bill_phone]').val($('input[name=ship_phone]').val());
            $('input[name=bill_email]').val($('input[name=ship_email]').val());
        },

        onDetailsSave:function(ev){
            ev.preventDefault();

            $('#modal-addresses').modal('hide');
            $('#modal-account').modal('hide');
            $('#modal-details').modal('hide');
            $('#modal-shipping').modal('hide');
            $('#modal-taxes').modal('hide');

            var _callback = function(){

                var data = $('#order-details-form').gc_serializeArray();

                var success = function (json) {
                    if (json.message) {
                        toastr[json.status](json.message);
                    }
                    pos.order = json.order;
                    pos.currency.set(json.order.currency_code);
                    pos.cart.draw();
                };

                var error = function (response) {
                    console.log(response);
                };

                $.ajax({
                    type     : 'post',
                    url      : '/jpanel/pos/'+pos.order.id+'/update',
                    data     : data,
                    success  : success,
                    error    : error,
                    dataType : 'json'
                });
            }

            // if we don't have an order, create an order first
            if (pos.order === null) {
                pos.cart.newCart(_callback);
            } else
                _callback();
        },

        onPromoSave:function(ev){
            ev.preventDefault();

            $('#modal-promo').modal('hide');

            var _callback = function(){

                var data = $('#order-promo-form').serializeArray();

                var success = function (json) {
                    toastr[json.status](json.message);
                    pos.order = json.order;
                    pos.currency.set(json.order.currency_code);
                    pos.cart.draw();
                };

                var error = function (response) {
                    toastr['error'](response.message);
                };

                $.ajax({
                    type     : 'post',
                    url      : '/jpanel/pos/'+pos.order.id+'/promos/apply',
                    data     : data,
                    success  : success,
                    error    : error,
                    dataType : 'json'
                });
            }

            // if we don't have an order, create an order first
            if (pos.order === null) {
                pos.cart.newCart(_callback);
            } else
                _callback();
        },

        onAddChild:function(ev) {
            ev.preventDefault();

            if ($.trim($('#order-select-child input[name=reference_number]').val()) == '') {
                toastr['error']('Provide a child reference number.');
                return;
            }

            $('#modal-select-child').modal('hide');

            pos.cart.paneState('loading');

            var _callback = function(){

                var formData = $('#order-select-child').serializeArray();

                var success = function(json){
                    if (json.status == 'error') {
                        error(json);
                    }

                    pos.order = json.order;
                    pos.currency.set(json.order.currency_code);
                    pos.cart.draw();
                };

                var error = function(json){
                    toastr['error'](json.message);
                };

                $.ajax({
                    type     : 'post',
                    url      : '/jpanel/pos/'+pos.order.id+'/add_by_child_reference',
                    data     : formData,
                    success  : success,
                    error    : error,
                    dataType : 'json'
                });
            }

            // if we don't have an order, create an order first
            if (pos.order === null) {
                pos.cart.newCart(_callback);
            } else {
                _callback();
            }
        },

        onAddFundraiser:function(ev) {
            ev.preventDefault();

            if ($.trim($('#order-select-fundraiser [name=fundraising_page_id]').val()) == '') {
                toastr['error']('Choose a fundraiser.');
                return;
            }

            $('#modal-select-fundraiser').modal('hide');

            pos.cart.paneState('loading');

            var _callback = function(){

                var formData = $('#order-select-fundraiser').serializeArray();

                var success = function(json){
                    if (json.status == 'error') {
                        error(json);
                    }

                    pos.order = json.order;
                    pos.currency.set(json.order.currency_code);
                    pos.cart.draw();
                };

                var error = function(json){
                    toastr['error'](json.message);
                };

                $.ajax({
                    type     : 'post',
                    url      : '/jpanel/pos/'+pos.order.id+'/add_by_fundraising_page',
                    data     : formData,
                    success  : success,
                    error    : error,
                    dataType : 'json'
                });
            }

            // if we don't have an order, create an order first
            if (pos.order === null) {
                pos.cart.newCart(_callback);
            } else {
                _callback();
            }
        },

        onDccTypeChange: function () {
            const value = $('input[name="dcc_type"]:checked').val();
            $('#input_dcc_enabled_by_customer input[type="hidden"][name="dcc_enabled_by_customer"]').val(value ? 1 : 0);
        },

        onReferralSourceChange:function () {
            var val    = $('input[name="referral_source"]:checked').val(),
                $other = $('#other-referral-source');

            if (val == 'Other') {
                $other.removeClass('hide');
                $('input[name="other_referral_source"]:checked').focus();
            } else {
                $other.addClass('hide');
            }
        },

        onSaveDefaults: function(ev) {
            ev.preventDefault();

            if (pos.order == null) {
                toastr['error']('No defaults to save. You need to start a contribution first.');
            } else {
                pos.orderDefaults = $.extend({},pos.order);
                toastr['success']('Saved! Everytime you finish a contribution, a new contribution will be created that looks like this contribution does now.');
            }
        },

        onClearDefaults: function(ev) {
            ev.preventDefault();

            pos.orderDefaults = null;
            toastr['success']('Cleared! Every new contribution will start fresh.');
        }
    },

    cart:{
        paneState:function(state){
            $('.pos-cart-items').empty();
            $('.cart-item-count').html('0');

            $('.pos-total-savings').removeData('value').html('');
            $('.pos-sub-total').removeData('value').html('');
            $('.pos-tax-total').removeData('value').html('');
            $('.pos-tax-label').removeData('value').html('');
            $('.pos-shipping-label').removeData('value').html('');
            $('.pos-shipping-total').removeData('value').html('');
            $('.pos-grand-total').removeData('value').html('');
            $('.pos-show-payment').addClass('disabled').prop('disabled', true);

            if (state === 'empty') {
                $('.pos-cart-items').append($('<div class="text-placeholder text-center col-sm-6 col-sm-offset-3"><h1>Shopping Cart is Empty</h1></div>'));
            } else if (state === 'loading') {
                $('.pos-cart-items').append($('<div class="text-placeholder text-center col-sm-6 col-sm-offset-3"><i class="fa fa-circle-o-notch fa-spin fa-4x"></i></div>'));
            }
        },
        newCart:function(callback, data){
            var success = function(json){
                if (json.status === 'error')
                    return toastr['error']('There was a problem starting your contribution.');

                pos.order = json.order;
                pos.currency.set(json.order.currency_code);
                if (typeof callback === 'function') callback();
            };

            var error = function(){
                return toastr['error']('There was a problem starting your contribution.');
            };

            if (typeof data == 'undefined') {
                data = {
                    currency_code: pos.currency.active.code
                };
            }

            $.ajax({
                type     : 'post',
                url      : '/jpanel/pos/new',
                data     : data,
                success  : success,
                error    : error,
                dataType : 'json'
            });
        },
        newCartFromDefaultsCart: function(){
            var defaults = {
                'source'                     : pos.orderDefaults.source,
                'referral_source'            : pos.orderDefaults.referral_source,
                'comments'                   : pos.orderDefaults.comments,
                'is_anonymous'               : pos.orderDefaults.is_anonymous ? 1 : 0,
                'ordered_at'                 : pos.orderDefaults.ordered_at,
                'dcc_enabled_by_customer'    : pos.orderDefaults.dcc_enabled_by_customer,
                'dcc_type'                   : pos.orderDefaults.dcc_type,
                'currency_code'              : pos.currency.active.code,
                //'member_id'                  : pos.orderDefaults.member_id,
                //'account_type_id'            : pos.orderDefaults.account_type_id,
                //'email_opt_in'               : pos.orderDefaults.email_opt_in,
                //'bill_title'                 : pos.orderDefaults.billing_title,
                //'bill_first_name'            : pos.orderDefaults.billing_first_name,
                //'bill_last_name'             : pos.orderDefaults.billing_last_name,
                //'bill_organization_name'     : pos.orderDefaults.billing_organization_name,
                //'bill_address'               : pos.orderDefaults.billingaddress1,
                //'bill_address2'              : pos.orderDefaults.billingaddress2,
                //'bill_city'                  : pos.orderDefaults.billingcity,
                //'bill_state'                 : pos.orderDefaults.billingstate,
                //'bill_zip'                   : pos.orderDefaults.billingzip,
                //'bill_country'               : pos.orderDefaults.billingcountry,
                //'bill_phone'                 : pos.orderDefaults.billingphone,
                //'bill_email'                 : pos.orderDefaults.billingemail,
                //'ship_title'                 : pos.orderDefaults.shipping_title,
                //'ship_first_name'            : pos.orderDefaults.shipping_first_name,
                //'ship_last_name'             : pos.orderDefaults.shipping_last_name,
                //'ship_organization_name'     : pos.orderDefaults.shipping_organization_name,
                //'ship_email'                 : pos.orderDefaults.shipemail,
                //'ship_address'               : pos.orderDefaults.shipaddress1,
                //'ship_address2'              : pos.orderDefaults.shipaddress2,
                //'ship_city'                  : pos.orderDefaults.shipcity,
                //'ship_state'                 : pos.orderDefaults.shipstate,
                //'ship_zip'                   : pos.orderDefaults.shipzip,
                //'ship_country'               : pos.orderDefaults.shipcountry,
                //'ship_phone'                 : pos.orderDefaults.shipphone,
            };

            pos.cart.newCart(function(){
                pos.cart.draw(true);
            }, defaults);
        },

        setSaleDate: function(value) {
            var saleDate = moment(value);
            $('#modal-details input[name=ordered_at]').datepicker('update', saleDate.isValid() ? saleDate.toDate() : '');
        },

        setSource: function(value) {
            try {
                $('#source_buttons input[value="' + value + '"]').parent().click();
            } catch (err) {
                console.error(err);
            }
        },

        setReferralSource: function(value) {
            try {
                var $el = $('#referral_source_buttons input[value="' + value + '"]:not(#input_referral_source)');
            } catch (err) {
                console.error(err);
                return false;
            }

            if ($el.length) {
                $el.parent().click();
                $('#other-referral-source input[name=other_referral_source]').val('');
                return true;
            }

            $el = $('#referral_source_buttons input[value="Other"]');

            if ($el.length) {
                $el.parent().click();
                $('#other-referral-source input[name=other_referral_source]').val(value);
                return true;
            }

            return false;
        },

        setDccEnabledByCustomer: function(value) {
            try {
                $('#dcc_enabled_by_customer_buttons input[value="' + (value ? 1 : 0) + '"]').parent().click();
                $('#input_dcc_enabled_by_customer input[type="hidden"][name="dcc_enabled_by_customer"]').val(value ? 1 : 0);
            } catch (err) {
                console.error(err);
            }
        },

        setDccType: function(value) {
            try {
                $('#dcc_type_buttons input[value="' + value + '"]').parent().click();
            } catch (err) {
                console.error(err);
            }
        },

        addItem:function(formData){

            pos.cart.paneState('loading');

            var _callback = function(){

                var success = function(json){
                    if (json.status === 'error') {
                        toastr['error'](json.message || "We couldn't add the item to the contribution.");
                    }

                    pos.order = json.order;
                    pos.currency.set(json.order.currency_code);
                    pos.cart.draw();
                };

                var error = function(){
                    alert('Error adding item to cart.');
                };

                $.ajax({
                    type     : 'post',
                    url      : '/jpanel/pos/'+pos.order.id+'/add',
                    data     : formData,
                    success  : success,
                    error    : error,
                    dataType : 'json'
                });
            }

            // if we don't have an order, create an order first
            if (pos.order === null) {
                pos.cart.newCart(_callback);
            } else
                _callback();
        },
        removeItem:function(item){
            pos.cart.paneState('loading');

            var _callback = function(){

                var success = function(json){
                    pos.order = json.order;
                    pos.currency.set(json.order.currency_code);

                    if (pos.order.items.length === 0)
                        pos.cart.paneState('empty');

                    pos.cart.draw();
                };

                var error = function(){
                    alert('Error removing item from cart.');
                };

                $.ajax({
                    type     : 'post',
                    url      : '/jpanel/pos/'+pos.order.id+'/remove',
                    data     : {'order_item_id' : item.id},
                    success  : success,
                    error    : error,
                    dataType : 'json'
                });
            }

            // if we don't have an order, create an order first
            if (pos.order === null) {
                pos.cart.newCart(_callback);
            } else
                _callback();
        },
        draw:function(fromDefaults){
            pos.cart.drawItems();
            pos.cart.drawTotals(fromDefaults);
        },
        drawItems:function(){
            $('.pos-cart-items').empty();

            if (pos.order === null || pos.order.items.length === 0)
                return pos.cart.paneState('empty');

            $.each(pos.order.items, function(i, item){
                var $item = $(pos.cart.itemHtml(item));

                if (!item.is_locked) {
                    $item.find('.item-remove').bind('click', function(ev){
                        $(ev.target).parents('.pos-cart-item').slideUp(function(){ $(this).remove(); });
                        pos.cart.removeItem(item);
                    });
                }

                $item.appendTo('.pos-cart-items');
            });

        },
        drawTotals:function(fromDefaults){
            var currency = pos.currency.active;

            // clear existing shipping options
            $('.pos-shipping-options').empty();

            // no order
            if (pos.order === null) {
                $('.cart-item-count').html(0);
                $('.pos-sub-total').data('value',0).html(currency.symbol+'0.00');
                $('.pos-tax-total').data('value',0).html(currency.symbol+'0.00');
                $('.pos-shipping-total').data('value',0).html(currency.symbol+'0.00');
                $('.pos-dcc-total').data('value',0).html(currency.symbol+'0.00');
                $('.pos-grand-total').data('value',0).html(currency.symbol+'0.00');
                $('.pos-show-payment').removeClass('disabled').prop('disabled', false);
                $('.pos-shipping-label').data('value',null).html('');
                $('.pos-account').html('<strong><i class="fa fa-user"></i> Guest</strong>');
                $('.pos-billing-address')
                    .addClass('empty')
                    .data('value',null)
                    .html('None');
                $('.pos-shipping-address')
                    .addClass('empty')
                    .data('value',null)
                    .html('None');
                $('.pos-source-and-date-string').addClass('empty').html('None');
                $('input[name=bill_first]').val('');
                $('input[name=bill_last]').val('');
                $('input[name=bill_address]').val('');
                $('input[name=bill_address2]').val('');
                $('input[name=bill_city]').val('');
                $('select[name=bill_state]').val('');
                $('input[name=bill_zip]').val('');
                $('select[name=bill_country]').val(window._settings.default_country).change();
                $('input[name=bill_phone]').val('');
                $('input[name=bill_email]').val('');
                $('input[name=ship_first_name]').val('');
                $('input[name=ship_last_name]').val('');
                $('input[name=ship_address]').val('');
                $('input[name=ship_address2]').val('');
                $('input[name=ship_city]').val('');
                $('select[name=ship_state]').val('');
                $('input[name=ship_zip]').val('');
                $('select[name=ship_country]').val(window._settings.default_country).change();
                $('input[name=ship_phone]').val('');
                $('input[name=ship_email]').val('');
                $('.pos-total-savings').data('value',0).html(currency.symbol+'0.00');
                $('.pos-total-savings').parents('.pos-totals-line').removeClass('text-bold text-danger');
                return;
            }

            // make sure there are no null values
            if (pos.order.total_savings === null)   pos.order.total_savings = 0;
            if (pos.order.subtotal === null)        pos.order.subtotal = 0;
            if (pos.order.taxtotal === null)        pos.order.taxtotal = 0;
            if (pos.order.shipping_amount === null) pos.order.shipping_amount = 0;
            if (pos.order.dcc_total_amount === null)pos.order.dcc_total_amount = 0;
            if (pos.order.totalamount === null)     pos.order.totalamount = 0;

            // set all values
            $('.cart-item-count').html(pos.order.total_qty);
            $('.pos-sub-total').data('value',pos.order.subtotal).html(currency.symbol+pos.order.subtotal.formatMoney());
            $('.pos-tax-total').data('value',pos.order.taxtotal).html(currency.symbol+pos.order.taxtotal.formatMoney());
            $('.pos-shipping-total').data('value',pos.order.shipping_amount).html(currency.symbol+pos.order.shipping_amount.formatMoney());
            $('.pos-dcc-total').data('value',pos.order.dcc_total_amount).html(currency.symbol+pos.order.dcc_total_amount.formatMoney());
            $('.pos-grand-total').data('value',pos.order.totalamount).html(currency.symbol+pos.order.totalamount.formatMoney());

            // if total savings
            if (pos.order.total_savings > 0) {
                $('.pos-total-savings').data('value',pos.order.total_savings).html('('+currency.symbol+pos.order.total_savings.formatMoney()+')');
                $('.pos-total-savings').parents('.pos-totals-line').addClass('text-bold text-danger');
            } else {
                $('.pos-total-savings').data('value',pos.order.total_savings).html(currency.symbol+'0.00');
                $('.pos-total-savings').parents('.pos-totals-line').removeClass('text-bold text-danger');
            }

            // tax label
            if ($.trim(pos.order.tax_address1) != '' || $.trim(pos.order.tax_state) != '' || $.trim(pos.order.tax_city) != '' || $.trim(pos.order.tax_zip) != '') {
                var labels = [];

                if ($.trim(pos.order.tax_address1) !== '')
                    labels.push(pos.order.tax_address1);

                if ($.trim(pos.order.tax_address2) !== '')
                    labels.push(pos.order.tax_address2);

                if ($.trim(pos.order.tax_city) !== '')
                    labels.push(pos.order.tax_city);

                if ($.trim(pos.order.tax_state) !== '')
                    labels.push(pos.order.tax_state);

                if ($.trim(pos.order.tax_zip) !== '')
                    labels.push(pos.order.tax_zip);

                if ($.trim(pos.order.tax_country) !== '')
                    labels.push(pos.order.tax_country);

                $('.pos-tax-label').html(labels.join(', '));
            } else {
                $('.pos-tax-label').html('');
            }

            var $promo_select = $('#promocodes').data('selectize');
            $promo_select.clear();

            // promo label & field
            if (pos.order.promocodes && pos.order.promocodes.length > 0) {
                var promos = [];

                $.each(pos.order.promocodes, function(i, promo){

                    // build promo string for label
                    promos.push(promo.code);

                    // select items
                    $promo_select.addItem(promo.code, true);

                });

                $('.pos-promo-label').html(promos.join(', '));
            } else {
                $('.pos-promo-label').html('');
            }

            // enable payment
            $('.pos-show-payment').removeClass('disabled').prop('disabled', false);

            if (pos.order.shipping_method !== null)
                $('.pos-shipping-label').data('value',pos.order.shipping_method.name).html(pos.order.shipping_method.name);
            else if (pos.order.courier_method !== null)
                $('.pos-shipping-label').data('value',pos.order.courier_method).html(pos.order.courier_method);
            else if (pos.order.is_free_shipping !== null)
                $('.pos-shipping-label').data('value','Free Shipping').html('Free Shipping');
            else
                $('.pos-shipping-label').data('value',null).html('');

            if (pos.order.member_id === null) {
                $('.pos-account').html('<strong><i class="fa fa-user"></i> Guest</strong>');
            } else {
                $('.pos-account').html('<strong><i class="fa ' + pos.order.member.fa_icon + '"></i> ' + pos.order.member.display_name + '</strong>');
            }

            if ($.trim(pos.order.billing_address_html) !== '') {
                $('.pos-billing-address')
                    .removeClass('empty')
                    .data('value',pos.order.billing_address_html)
                    .html(pos.order.billing_address_html);
            } else {
                $('.pos-billing-address')
                    .addClass('empty')
                    .data('value',null)
                    .html('None');
            }

            if ($.trim(pos.order.shipping_address_html) !== '') {
                $('.pos-shipping-address')
                    .removeClass('empty')
                    .data('value',pos.order.shipping_address_html)
                    .html(pos.order.shipping_address_html);
            } else {
                $('.pos-shipping-address')
                    .addClass('empty')
                    .data('value',null)
                    .html('None');
            }

            $('.pos-source-and-date-string').removeClass('empty').html(pos.order.source_and_date_string);

            var html = '<div class="form-group"><div data-toggle="buttons">';

            // populate available shipping options
            if (pos.order.shippable_items) {
                if (pos.order.available_shipping_methods.length === 0) {
                       $('.pos-shipping-options').html('<span class="text-muted text-center">No Shipping Options Available</span>');
                } else {
                        var prevHeading = null;

                        if (!pos.order.is_free_shipping){
                            $.each(pos.order.available_shipping_methods, function(i, method){

                                var heading = (method.courier === null) ? 'Flat-Rate Options' : courier_name(method.courier);
                                var selected = ((pos.order.shipping_method_id !== null && pos.order.shipping_method_id === method.shipping_method_id)
                                                || (pos.order.courier_method !== null && pos.order.courier_method == method.title)) ? true : false;

                                if (heading !== prevHeading) {
                                    if (i > 0) {
                                        html += '<br><br>';
                                    }
                                    html += '<label>' + heading + ':</label><br>';
                                }

                                html += '<label class="btn btn-default btn-lg ' + ((selected)?'active':'') + ' ">' +
                                            '<input type="radio" ' + ((selected)?'selected':'') + ' name="' + ((method.courier !== null)?'courier_method':'shipping_method_id') + '" value="' + ((method.courier !== null)?method.title:method.shipping_method_id) + '" autocomplete="off"> <i class="fa fa-check"></i> '+method.title+' ('+currency.symbol+method.cost.formatMoney()+')' +
                                        '</label>';

                                prevHeading = heading;
                            });
                        }
                        else{
                        // free shipping
                        html += '<label class="btn btn-default btn-lg ' + ((pos.order.is_free_shipping)?'active':'') + ' ">' +
                                    '<input type="radio" ' + ((pos.order.is_free_shipping)?'selected':'') + ' name="is_free_shipping" value="1" autocomplete="off"> <i class="fa fa-check"></i> ' + ( ($.trim(pos.order.courier_method) != '') ? pos.order.courier_method : 'Free Shipping' ) +
                                '</label>';
                        }

                        html += '</div></div>';

                        $('.pos-shipping-options').html(html);
                    }
            }
            else{
                 $('.pos-shipping-options').html('<span class="text-muted text-center">No Shippable Items</span>');
            }

            // update the cover the fees button labels
            const coverFeesAmounts = pos.order.items.reduce((amounts, item) => {
                if (item.is_eligible_for_dcc) {
                    const costs = window.Givecloud.Dcc.getCosts(item.total);
                    amounts.most_costs += costs.most_costs;
                    amounts.more_costs += costs.more_costs;
                    amounts.minimum_costs += costs.minimum_costs;
                }
                return amounts;
            }, {
                most_costs: 0,
                more_costs: 0,
                minimum_costs: 0,
            });

            $('#dcc_type_buttons .btn:nth-child(1) span').text(`${pos.currency.active.symbol}${coverFeesAmounts.most_costs} - Most Costs`)
            $('#dcc_type_buttons .btn:nth-child(2) span').text(`${pos.currency.active.symbol}${coverFeesAmounts.more_costs} - More Costs`)
            $('#dcc_type_buttons .btn:nth-child(3) span').text(`${pos.currency.active.symbol}${coverFeesAmounts.minimum_costs} - Minimum Costs`)

            // billing/shipping address update
            $('select[name=bill_title]').val(pos.order.billing_title);
            $('input[name=bill_first_name]').val(pos.order.billing_first_name);
            $('input[name=bill_last_name]').val(pos.order.billing_last_name);
            $('input[name=bill_organization_name]').val(pos.order.billing_organization_name);
            $('select[name=account_type_id]').val(pos.order.account_type_id);
            $('input[name=bill_address]').val(pos.order.billingaddress1);
            $('input[name=bill_address2]').val(pos.order.billingaddress2);
            $('input[name=bill_city]').val(pos.order.billingcity);
            $('select[name=bill_state]').val(pos.order.billingstate);
            $('select[name=bill_country]').val(pos.order.billingcountry).change();
            $('input[name=bill_zip]').val(pos.order.billingzip);
            $('input[name=bill_phone]').val(pos.order.billingphone);
            $('input[name=bill_email]').val(pos.order.billingemail);
            $('select[name=ship_title]').val(pos.order.shipping_title);
            $('input[name=ship_first_name]').val(pos.order.shipping_first_name);
            $('input[name=ship_last_name]').val(pos.order.shipping_last_name);
            $('input[name=ship_organization_name]').val(pos.order.shipping_organization_name);
            $('input[name=ship_address]').val(pos.order.shipaddress1);
            $('input[name=ship_address2]').val(pos.order.shipaddress2);
            $('input[name=ship_city]').val(pos.order.shipcity);
            $('select[name=ship_state]').val(pos.order.shipstate);
            $('select[name=ship_country]').val(pos.order.shipcountry).change();
            $('input[name=ship_zip]').val(pos.order.shipzip);
            $('input[name=ship_phone]').val(pos.order.shipphone);
            $('input[name=ship_email]').val(pos.order.shipemail);
            $('input[name=email_opt_in]').val(pos.order.email_opt_in);

            // taxes
            $('input[name=tax_address1]').val(pos.order.tax_address1);
            $('input[name=tax_address2]').val(pos.order.tax_address2);
            $('input[name=tax_city]').val(pos.order.tax_city);
            $('input[name=tax_zip]').val(pos.order.tax_zip);
            $('select[name=tax_country]').val(pos.order.tax_country).change();
            $('select[name=tax_state]').val(pos.order.tax_state);

            $('#input_comments,#input_special_notes').val(pos.order.comments);
            $('#input_is_anonymous,#input_is_anonymous_2').prop('checked', pos.order.is_anonymous);

            if (fromDefaults && pos.orderDefaults.ordered_at) {
                pos.cart.setSaleDate(pos.order.ordered_at);
            }

            pos.cart.setSource(pos.order.source);
            pos.cart.setReferralSource(pos.order.referral_source);
            pos.cart.setDccEnabledByCustomer(pos.order.dcc_enabled_by_customer);
            pos.cart.setDccType(pos.order.dcc_type);

            pos.events.resizePanels();
        },
        itemHtml:function(item){
            var currency = pos.currency.active;
            return '<div class="pos-cart-item row clearfix ' + ((item.is_locked) ? 'item-locked' : '') + '">' +
                '<div class="col-xs-8">' +
                    '<div class="item-thumb" style="background-image:url(\'' + item.image_thumb + '\');">' +
                        ((item.qty > 1)?'<div class="badge">' + item.qty + '</div>':'') +
                    '</div>' +
                    '<div class="item-desc">' +
                        '<div class="item-name">'+item.description+'</div>' +
                        '<div class="item-code">'+item.reference+'</div>' +
                        //(($.trim(item.payment_string) !== '')?'<div class="item-meta">'+item.payment_string+'</div>':'') +
                        (($.trim(item.promocode) != '') ? '<div class="item-promo">' + item.promocode + '</div>' : '') +
                    '</div>' +
                '</div>' +
                '<div class="col-xs-4 text-right">' +
                    '<span class="item-amount">' + currency.symbol + item.total.formatMoney() + '</span>' +
                    '<span class="item-remove"><i class="fa fa-times fa-fw"></i></span><br>' +
                    ((item.qty > 1 || (item.is_price_reduced))?'<div class="item-qty">'+item.qty+' @ ' + ((item.is_price_reduced) ? ('<span class="price-sale">'+currency.symbol+item.price.formatMoney()+'</span> <span class="price-strike">'+currency.symbol+item.undiscounted_price.formatMoney()+'</span>') : (currency.symbol+item.price.formatMoney())) + '</div>':'') +
                    ((item.recurring_amount > 0)?'<div class="item-qty">'+ item.payment_string + '</div>':'') +
                '</div>' +
            '</div>';
        }
    },

    payment:{

        init:function(){
            $('#modal-payment').bind('shown.bs.modal',function(){
                if (pos.paymentApp) {
                    pos.paymentApp.$parent.$destroy();
                }
                var $el = $('#modal-payment > .modal-dialog > .modal-content').html(
                    '<web-pos-payment-modal ref="paymentApp" :key="orderId"></web-pos-payment-modal>'
                );
                new Vue({
                    el: $el[0],
                    data: {
                        orderId: pos.order.id
                    },
                    mounted() {
                        pos.paymentApp = this.$refs.paymentApp;
                    }
                });
            });

            $('#modal-payment').bind('hidden.bs.modal',function(){

                // save state for next window open
                pos.lastPaymentType = pos.paymentApp.input.payment_type;
                pos.paymentApp.resetData();

                pos.payment.modalState('pay');
            });
        },

        showModal:function(ev){
            if (typeof ev !== 'undefined') {
                ev.preventDefault();
                if ($(this).prop('disabled')) return false;
            }

            // JS validation
            if (!pos.validateOrder())
                return;

            // clear all fields.
            $('.pos-cc-number, .pos-cc-expiry, .pos-cc-cvv, .pos-cash-received, .pos-check-number, .pos-check-date, .pos-check-amount').val('');

            $('#modal-payment').modal();
        },

        finalize:function(ev){
            if (typeof ev !== 'undefined') {
                ev.preventDefault();
                if ($(this).prop('disabled')) return false;
            }

            var opts = {
                'payment_type'        : null
            };

            // process FREE (no payment) order
            if (pos.paymentApp.no_payment_required) {
                opts.payment_type = 'free';
                pos.payment.finish(opts);

            // process CASH
            } else if (pos.paymentApp.input.payment_type === 'cash') {
                opts.payment_type  = 'cash';
                opts.cash_received = pos.paymentApp.cash_received;
                opts.cash_change   = pos.paymentApp.cash_change;

                if (opts.cash_received < pos.order.totalamount)
                    return toastr['error']('Insufficient cash paid.');

                pos.payment.finish(opts);

            // proess CHECK
            } else if (pos.paymentApp.input.payment_type === 'check') {
                opts.payment_type = 'check';
                opts.check_number = pos.paymentApp.input.check_number;
                opts.check_date   = pos.paymentApp.input.check_date;
                opts.check_amt    = pos.paymentApp.input.check_amt;

                if ($.trim(opts.check_number) == '')
                    return toastr['error']('Please enter the number on the check received.');

                if ($.trim(opts.check_date) == '')
                    return toastr['error']('Please enter the date on the check received.');

                if (opts.check_amt == 0)
                    return toastr['error']('Please enter the amount on the check received.');

                pos.payment.finish(opts);

            // proess OTHER
            } else if (pos.paymentApp.input.payment_type === 'other') {
                opts.payment_type            = 'other';
                opts.payment_other_reference = pos.paymentApp.input.payment_other_reference;
                opts.payment_other_note      = pos.paymentApp.input.payment_other_note;

                if ($.trim(opts.payment_other_reference) == '')
                    return toastr['error']('Please provide a reference number for proof of payment.');

                pos.payment.finish(opts);

            // process CREDIT
            } else if (pos.paymentApp.input.payment_type === 'credit_card') {
                pos.initiateCheckout(pos.paymentApp.input.payment_type);

            // process BANK
            } else if (pos.paymentApp.input.payment_type === 'bank_account') {
                pos.initiateCheckout(pos.paymentApp.input.payment_type);

            // process PAYMENT METHOD
            } else if (pos.paymentApp.input.payment_type === 'payment_method') {
                pos.initiateCheckout(pos.paymentApp.input.payment_type);

            // fallback - should never happen
            } else {
                toastr['error']('Unable to finalize payment. Payment type is invalid.');
            }
        },

        finish:function(opts){

            pos.payment.modalState('loading');

            var error = function(json){
                toastr['error'](json.message);

                pos.payment.modalState('pay');
            }

            var success = function(json){
                if (json.status == 'error')
                    return error(json);

                pos.payment.onFinished(json.order);
            }

            // make sure opts obj exists
            if (typeof opts === 'undefined') {
                opts = {};
            }

            opts.mark_as_complete = pos.paymentApp.input.mark_as_complete ? 1 : 0;
            opts.send_confirmation_emails = pos.paymentApp.input.send_confirmation_emails ? 1 : 0;

            $.ajax({
                type     : 'post',
                url      : '/jpanel/pos/'+pos.order.id+'/complete',
                data     : opts,
                success  : success,
                error    : error,
                dataType : 'json'
            });
        },

        modalState:function(state){
            pos.paymentApp.setScreen(state);
        },

        onFinished : function (order) {
            pos.paymentApp.$nextTick(() => {
                pos.paymentApp.receipt = order;
            });

            pos.payment.modalState('finished');
            pos.reset();

        }
    },

    // reset the entire POS UI
    reset:function(){
        pos.order = null;
        pos.search.reset();
        pos.cart.draw();

        $([
            '#order-details-form',
            '#order-promo-form',
            '#order-select-child',
            '#order-select-fundraiser',
        ].join(',')).each(function (index, form) {
            form.reset();
            $(form).find('[data-toggle="buttons"] .active').removeClass('active');
            $(form).find('[data-toggle="buttons"] input:checked').parent().addClass('active');
        });

        if (pos.orderDefaults) {
            pos.cart.newCartFromDefaultsCart();
        }
    },

    // basic JS validation to make sure the order is good for payment
    validateOrder:function(){

        // no items in the cart
        if (pos.order == null || pos.order.items.length == 0)
        {
            toastr['error']('There are no items in this order.');
            return false;
        }

        // downloads but no email address
        if (pos.order.download_items > 0 && $.trim(pos.order.billingemail) == '')
        {
            toastr['error']('There are downloads included in this contribution that require a billing email address to send the files to.');
            return false;
        }

        // memberships MUST HAVE either:
        // - a member account <<<<< MAKE SURE THIS IS WORKING
        // - all billing info
        if (pos.order.member_id == null){
            var num_memberships = 0;
            (pos.order.items).forEach(function(item) {
               if (item.variant && item.variant.membership_id != null)
                   num_memberships++;
            });

            if(num_memberships > 0
               && ($.trim(pos.order.billing_first_name) == ''
               || $.trim(pos.order.billing_last_name) == ''))
            {
                toastr['error']('Memberships require either:<br>- A supporter or<br>- All billing information');
                $('.pos-show-bill-address').click();
                return false;
            }
        }

        // recurring donations MUST HAVE atleast
        // - a member account, or
        // - a name
        if (pos.order.recurring_items > 0
            && pos.order.member_id == null
            && ($.trim(pos.order.billing_first_name) == ''
               || $.trim(pos.order.billing_last_name) == ''))
        {
            toastr['error']('Recurring payments require either:<br>- A supporter or<br>- A billing name');
            $('.pos-show-bill-address').click();
            return false;
        }

        return true;
    },

    initiateCheckout : function(payment_type) {
        pos.payment.modalState('loading');

        var cart = {
            id: pos.order.client_uuid,
            payment_type: payment_type || 'credit_card',
            should_complete: pos.paymentApp.input.mark_as_complete,
            send_confirmation_emails: pos.paymentApp.input.send_confirmation_emails,
            requires_payment: pos.order.totalamount > 0 || pos.order.recurring_items > 0,
            total_price: pos.order.totalamount,
        };

        var payment = {
            name: $.trim($('input[name=bill_first_name]').val() + ' ' + $('input[name=bill_last_name]').val()),
            address_line1: $('input[name=bill_address]').val(),
            address_line2: $('input[name=bill_address2]').val(),
            address_city: $('input[name=bill_city]').val(),
            address_state: $('select[name=bill_state]').val(),
            address_zip: $('input[name=bill_zip]').val(),
            address_country: $('select[name=bill_country]').val(),
            currency: pos.paymentApp.payment.currency,
            number: (pos.paymentApp.payment.number || '').replace(/\D/g,''),
            exp: (pos.paymentApp.payment.exp || '').replace(/\D/g,''),
            cvv: (pos.paymentApp.payment.cvv || '').replace(/\D/g,''),
            account_holder_type: (pos.paymentApp.payment.account_holder_type || ''),
            account_type: (pos.paymentApp.payment.account_type || ''),
            transit_number: (pos.paymentApp.payment.transit_number || '').replace(/\D/g,''),
            institution_number: (pos.paymentApp.payment.institution_number || '').replace(/\D/g,''),
            routing_number: (pos.paymentApp.payment.routing_number || '').replace(/\D/g,''),
            account_number: (pos.paymentApp.payment.account_number || '').replace(/\D/g,''),
            ach_agree_tos: (pos.paymentApp.payment.ach_agree_tos || false),
            payment_method: pos.paymentApp.payment.payment_method,
        };

        if (pos.paymentApp.payment.account_holder_name) {
            payment.name = pos.paymentApp.payment.account_holder_name;
        }

        var gateway = window.Givecloud.PaymentTypeGateway(cart.payment_type);

        var updateCart = cart.send_confirmation_emails
            ? Promise.resolve()
            : axios.post('/jpanel/pos/'+pos.order.id+'/update', { send_confirmation_emails: false });

        updateCart.then(function(){
                if (cart.requires_payment) {
                    return gateway.getCaptureToken(cart, payment, cart.payment_type)
                        .then(function(token) {
                            return gateway.chargeCaptureToken(cart, token);
                        });
                } else {
                    return window.Givecloud.Cart(cart.id).complete();
                }
            }).then(function() {
                if (cart.should_complete) {
                    pos.payment.finish();
                } else {
                    pos.payment.onFinished({
                        id: pos.order.id,
                        invoicenumber: pos.order.client_uuid
                    });
                }
            }).catch(function(err) {
                console.log(err);
                if (Sugar.Object.isError(err) || Sugar.Object.isObject(err)) {
                    try {
                        err = Sugar.Object.get(err, 'response.data.error')
                            || Sugar.Object.get(err, 'response.data.message')
                            || Sugar.Object.get(err, 'response.message')
                            || Sugar.Object.get(err, 'error.message')
                            || Sugar.Object.get(err, 'message')
                            || Sugar.Object.get(err, 'error')
                            || err;
                    } catch(e) {
                        err = 'Unknown error (521)';
                    }
                }
                err = String(err).replace(/[\n\r]/, '<br>');
                toastr['error'](err);
                pos.payment.modalState('pay');
            });
    }
};

function courier_name ($str) {
    switch ($str) {
        case 'canadapost': return 'Canada Post';
        case 'ups':        return 'UPS';
        case 'usps':       return 'US Postal Service';
        case 'fedex':      return 'FedEx';
    }
}

function stThRd (i) {
    i = parseInt(i);
    var j = i % 10,
        k = i % 100;
    if (j == 1 && k != 11) {
        return i + "st";
    }
    if (j == 2 && k != 12) {
        return i + "nd";
    }
    if (j == 3 && k != 13) {
        return i + "rd";
    }
    return i + "th";
}

window.pos = pos;

export default pos;
