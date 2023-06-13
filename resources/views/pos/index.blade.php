<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <meta name="pinterest" content="nopin">
    @include('layouts/app-icons')

    <title>GC Virtual POS | <?= e(sys_get('clientName')); ?></title>

    <meta name="csrf-token" content="<?= e(csrf_token()); ?>">

    <!-- Google Fonts -->
    <link href="//fonts.googleapis.com/css?family=Lato:400,900,700,300|Source+Code+Pro:300,400,700|Open+Sans:400italic,700italic,400,300,700" rel="stylesheet" type="text/css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= e(jpanel_asset_url('dist/css/vendor.css')); ?>">
    <link rel="stylesheet" href="<?= e(jpanel_asset_url('css/pos.css')); ?>">

    <!-- Core JS -->
    <script charset="utf-8" src="https://cdn.givecloud.co/npm/jquery@3.3.1/dist/jquery.min.js"></script>

    <!-- Google Analytics -->
    <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-21510149-17']);
    _gaq.push(['_trackPageview']);
    (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
    </script>
</head>
<body>
    <div class="preloader">
        <div class="preloader-message"><i class="fa fa-spin fa-2x fa-circle-o-notch"></i><br>Loading POS</div>
    </div>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-dismiss="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" title="<?php if (is_super_user()): ?><?= e(app()->environment()); ?><?php endif; ?>">
                    <img src="https://cdn.givecloud.co/static/etc/givecloud-logo-mark-full-color-rgb.svg" class="ds-logo" width="27" height="26"> Virtual POS
                    <span class="pos-ajax-indicator hide text-muted">&nbsp;&nbsp;<i class="fa fa-spin fa-circle-o-notch"></i></span>
                </a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <li id="currencyDropdown" class="dropdown"></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle border-right" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-gear"></i> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">Default Contribution Status</li>
                        <li><a href="#" class="save-defaults">Save as Default</a></li>
                        <li><a href="#" class="clear-defaults">Clear Defaults</a></li>
                    </ul>
                </li>
                <li><a href="/jpanel/sessions/logout" class="pos-close"><i class="fa fa-times fa-fw"></i><span class="hidden-xs"> Quit &amp; Logout</span></a></li>
            </ul>
            <!-- /.navbar-top-links -->
        </nav>

        <div class="pos-wrapper">

            <div class="col-lg-8 col-md-7 pos-full-height">

                <div class="input-group input-group-lg">
                    <input type="search" placeholder="Search..." class="product-search form-control input-lg" tabindex="1">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default product-search-reset"><i class="fa fa-times"></i></button>
                    </div>
                </div>

                <div class="bookmark-list row row-padding-sm">
                </div>

                <div class="product-list row <?= e((feature('sponsorship')) ? 'sponsorships-enabled' : ''); ?> <?= e((feature('fundraising_pages')) ? 'p2p-enabled' : ''); ?>">

                    <div class="text-placeholder text-center col-sm-6 col-sm-offset-3">
                        <i class="fa fa-arrow-up fa-4x"></i>
                        <h1>Find a Product</h1>
                    </div>

                </div>

            </div>

            <div class="col-lg-4 col-md-5 pos-full-height">

                <div class="pos-cart panel panel-default">
                    <div class="panel-heading"><i class="fa fa-shopping-cart"></i> Shopping Cart <span class="badge cart-item-count">0</span></div>
                    <div class="panel-body">

                        <div class="pos-cart-items">

                            <div class="text-placeholder text-center col-sm-6 col-sm-offset-3">
                                <h1>Shopping Cart is Empty</h1>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="pos-totals panel panel-default">
                    <div class="panel-body">

                        <div class="pos-totals-line row clearfix">
                            <div class="col-xs-7">
                                <span class="line-label">Subtotal</span>
                                <span class="line-meta"></span>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span class="line-amount pos-sub-total">$0.00</span>
                                <i class="fa fa-fw"></i>
                            </div>
                        </div>

                        <div class="pos-totals-line row clearfix clickable <?= e((! feature('shipping')) ? 'hide' : ''); ?>" data-toggle="modal" data-target="#modal-shipping">
                            <div class="col-xs-7">
                                <span class="line-label">Shipping</span>
                                <span class="line-meta pos-shipping-label"></span>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span class="line-amount pos-shipping-total">$0.00</span>
                                <i class="fa fa-fw fa-pencil"></i>
                            </div>
                        </div>

                        <div class="pos-totals-line row clearfix clickable <?= e((! feature('taxes')) ? 'hide' : ''); ?>" data-toggle="modal" data-target="#modal-taxes">
                            <div class="col-xs-7">
                                <span class="line-label">Tax</span>
                                <span class="line-meta pos-tax-label"></span>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span class="line-amount pos-tax-total">$0.00</span>
                                <i class="fa fa-fw fa-pencil"></i>
                            </div>
                        </div>

                        <div class="pos-totals-line row clearfix clickable" data-toggle="modal" data-target="#modal-details">
                            <div class="col-xs-7">
                                <span class="line-label"><?= e(sys_get('dcc_label')); ?></span>
                                <span class="line-meta"></span>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span class="line-amount pos-dcc-total">$0.00</span>
                                <i class="fa fa-fw fa-pencil"></i>
                            </div>
                        </div>

                        <div class="pos-totals-line row lg clearfix">
                            <div class="col-xs-7">
                                <span class="line-label">Grand Total</span>
                                <span class="line-meta"></span>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span class="line-amount pos-grand-total">$0.00</span>
                                <i class="fa fa-fw"></i>
                            </div>
                        </div>

                        <hr style="margin:10px 0;" class="<?= e((! feature('promos')) ? 'hide' : ''); ?>" />

                        <div class="pos-totals-line row clearfix clickable <?= e((! feature('promos')) ? 'hide' : ''); ?>" data-toggle="modal" data-target="#modal-promo">
                            <div class="col-xs-7">
                                <span class="line-label">Promotions</span>
                                <span class="line-meta pos-promo-label"></span>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span class="line-amount pos-total-savings">$0.00</span>
                                <i class="fa fa-fw fa-pencil"></i>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="pos-invoice panel panel-default">
                    <div class="panel-body">

                        <div class="pos-invoice-line row clearfix clickable" data-toggle="modal" data-target="#modal-details">
                            <div class="col-xs-4">
                                <span>Source &amp; Date</span>
                            </div>
                            <div class="col-xs-8 text-right">
                                <span class="line-icon"><i class="fa fa-fw fa-pencil"></i></span>
                                <div class="line-value empty pos-source-and-date-string">None</div>
                            </div>
                        </div>

                        <hr style="margin:10px 0;">

                        <div class="pos-invoice-line row clearfix clickable" data-toggle="modal" data-target="#modal-account">
                            <div class="col-xs-4">
                                <span>Supporter</span>
                            </div>
                            <div class="col-xs-8 text-right">
                                <span class="line-icon"><i class="fa fa-fw fa-pencil"></i></span>
                                <div class="line-value pos-account"><strong><i class="fa fa-user"></i> Guest</strong></div>
                            </div>
                        </div>

                        <div class="pos-invoice-line row clearfix pos-show-bill-address">
                            <div class="col-xs-4">
                                <span>Bill to</span>
                            </div>
                            <div class="col-xs-8 text-right">
                                <span class="line-icon"><i class="fa fa-fw fa-pencil"></i></span>
                                <div class="line-value empty pos-billing-address">None</div>
                            </div>
                        </div>

                        <div class="pos-invoice-line row clearfix pos-show-ship-address <?= e((! feature('shipping')) ? 'hide' : ''); ?>">
                            <div class="col-xs-4">
                                <span>Ship to</span>
                            </div>
                            <div class="col-xs-8 text-right">
                                <span class="line-icon"><i class="fa fa-fw fa-pencil"></i></span>
                                <div class="line-value empty pos-shipping-address">None</div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="btn btn-block btn-lg btn-success pos-show-payment"><i class="fa fa-check"></i> Complete Contribution</div>

            </div>

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<form id="order-details-form">

<div class="modal fade modal-success" id="modal-details" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-body">

                <div class="form-group">
                    <label>Sale Date:</label>
                    <div class="input-group input-group-lg">
                        <div class="input-group-addon"><i class="fa fa-calendar-o fa-fw"></i></div>
                        <input type="text" class="form-control input-lg date" name="ordered_at" placeholder="Contribution date">
                    </div>
                </div>

                <div id="input_sources" class="form-group">
                    <label>Source:</label>
                    <div id="source_buttons" data-toggle="buttons">
                        <?php foreach (explode(',', sys_get('pos_sources')) as $ix => $source): ?>
                        <label class="btn btn-default btn-lg <?= e(($ix == 0) ? 'active' : ''); ?>">
                            <input type="radio" name="source" id="input_source_<?= e($ix); ?>" value="<?= e($source); ?>" autocomplete="off" <?= e(($ix == 0) ? 'checked' : ''); ?>> <i class="fa fa-check"></i> <?= e($source); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (sys_get('referral_sources_isactive')): ?>
                    <div class="form-group">
                        <label>Referral Source:</label>
                        <div id="referral_source_buttons" data-toggle="buttons">
                            <?php if (sys_get('referral_sources_options')): ?>
                                <?php foreach (explode(',', sys_get('referral_sources_options')) as $ix => $referral_source): ?>
                                <label class="btn btn-default btn-lg <?= e(($ix == 0) ? 'active' : ''); ?>">
                                    <input type="radio" name="referral_source" id="input_referral_source_<?= e($ix); ?>" value="<?= e($referral_source); ?>" autocomplete="off" <?= e(($ix == 0) ? 'checked' : ''); ?>> <i class="fa fa-check"></i> <?= e($referral_source); ?>
                                </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (sys_get('referral_sources_other') == 1): ?>
                                <label class="btn btn-default btn-lg <?= e(($ix == 0) ? 'active' : ''); ?>">
                                    <input type="radio" name="referral_source" id="input_referral_source" value="Other" autocomplete="off"> <i class="fa fa-check"></i> Other
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group hide" id="other-referral-source">
                        <input type="text" class="form-control input-lg" name="other_referral_source" value="" placeholder="Other...">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="input_special_notes"> Special Notes:</label>
                    <textarea id="input_special_notes" class="form-control" name="special_notes"></textarea>
                    <div class="checkbox">
                        <label for="input_is_anonymous_2">
                            <input id="input_is_anonymous_2" type="checkbox" name="is_anonymous" value="1" checked>
                            Keep me anonymous
                        </label>
                    </div>
                </div>

                <div id="input_dcc_enabled_by_customer" class="form-group">
                    <label><?= e(sys_get('dcc_checkout_label')); ?>:</label>

                    <?php if (sys_get('dcc_ai_is_enabled')): ?>
                        <input type="hidden" name="dcc_enabled_by_customer" value="">

                        <div id="dcc_type_buttons" data-toggle="buttons">
                            <label class="btn btn-default btn-lg">
                                <input type="radio" name="dcc_type" value="most_costs"> <i class="fa fa-check"></i> <span>Most Costs</span>
                            </label>
                            <label class="btn btn-default btn-lg">
                                <input type="radio" name="dcc_type" value="more_costs"> <i class="fa fa-check"></i> <span>More Costs</span>
                            </label>
                            <label class="btn btn-default btn-lg">
                                <input type="radio" name="dcc_type" value="minimum_costs"> <i class="fa fa-check"></i> <span>Minimum Costs</span>
                            </label>
                            <label class="btn btn-default btn-lg active">
                                <input type="radio" name="dcc_type" value="" checked> <i class="fa fa-check"></i> No Thank You
                            </label>
                        </div>
                    <?php else: ?>
                        <div id="dcc_enabled_by_customer_buttons" data-toggle="buttons">
                            <label class="btn btn-default btn-lg active">
                                <input type="radio" name="dcc_enabled_by_customer" value="0" checked> <i class="fa fa-check"></i> No
                            </label>
                            <label class="btn btn-default btn-lg">
                                <input type="radio" name="dcc_enabled_by_customer" value="1"> <i class="fa fa-check"></i> Yes
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-lg btn-bold pos-save-details"><i class="fa fa-check"></i></button>
                <button type="button" class="btn btn-default btn-lg btn-bold" data-dismiss="modal"><i class="fa fa-times"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-account" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-body">

                <div class="form-group">
                    <label for="member_id" class="control-label">Supporter</label>
                    <input type="text" class="form-control ds-members input-lg" id="member_id" name="member_id" autocomplete="none" placeholder="Search for a supporter...">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-lg btn-bold pos-save-account"><i class="fa fa-check"></i> Use Supporter</button>
                <button type="button" class="btn btn-default btn-lg btn-bold pos-save-guest">Continue as <i class="fa fa-user"></i> Guest</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-addresses" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-body">

                <ul class="nav nav-tabs <?= e((! feature('shipping')) ? 'hide' : ''); ?>" role="tablist">
                    <li role="presentation" class="active"><a href="#pos-bill-address-tab" aria-controls="pos-bill-address-tab" role="tab" data-toggle="tab"><i class="fa fa-envelope"></i> Billing Address</a></li>
                    <li role="presentation"><a href="#pos-ship-address-tab" aria-controls="pos-ship-address-tab" role="tab" data-toggle="tab"><i class="fa fa-truck"></i> Shipping Address</a></li>
                </ul>

                <div class="tab-content">
                    <br>

                    <div role="tabpanel" class="tab-pane active" id="pos-bill-address-tab">

                        <label for="" class="control-label">Billing Name &amp; Contact</label>
                        <label for="" class="control-label pull-right"><a class="copy-shipping <?= e((! feature('shipping')) ? 'hide' : ''); ?>" href="#" tabindex="-1"><i class="fa fa-copy"></i> Copy from Shipping</a>&nbsp;</label>

                        <div class="row row-padding-sm">

                            <?php if (sys_get('donor_title') != 'hidden'): ?>
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <?php if (sys_get('donor_title_options') == ''): ?>
                                            <input type="text" class="form-control input-lg" id="input_bill_title" name="bill_title" placeholder="Mr/Ms">
                                        <?php else: ?>
                                            <select name="bill_title" id="input_bill_title" class="form-control input-lg">
                                                <option value="">--</option>
                                                <?php foreach (explode(',', sys_get('donor_title_options')) as $option): ?>
                                                    <option value="<?= e(trim($option)); ?>"><?= e(trim($option)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col-xs-4">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_bill_first_name" name="bill_first_name" placeholder="First Name">
                                </div>
                            </div>

                            <div class="col-xs-<?= e((sys_get('donor_title') != 'hidden') ? '5' : '8'); ?>">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_bill_last_name" name="bill_last_name" placeholder="Last Name">
                                </div>
                            </div>

                            <div class="col-xs-7">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_bill_organization_name" name="bill_organization_name" placeholder="Organization">
                                </div>
                            </div>

                            <div class="col-xs-5">
                                <div class="form-group">
                                    <select name="account_type_id" class="form-control input-lg">
                                        <?php foreach ($account_types as $type): ?>
                                            <option value="<?= e($type->id); ?>" <?= e(($type->is_default) ? 'selected' : ''); ?>><?= e($type->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row row-padding-sm">
                            <div class="col-sm-7">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></div>
                                        <input type="email" class="form-control input-lg" id="input_bill_email" name="bill_email" placeholder="email@address.com">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top:-10px">
                                    <div class="checkbox" style="margin:0">
                                        <label><input type="checkbox" name="email_opt_in" value="1"> Send me emails and updates</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-phone fa-fw"></i></div>
                                        <input type="tel" class="form-control input-lg" id="input_bill_phone" name="bill_phone" placeholder="555-555-5555">
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label for="" class="control-label">Billing Address</label>
                                    <input type="text" class="form-control input-lg" id="input_bill_address" name="bill_address" placeholder="Address Line 1">
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_bill_address2" name="bill_address2" placeholder="Address Line 2">
                                </div>
                            </div>

                            <div class="col-xs-8">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_bill_city" name="bill_city" placeholder="City">
                                </div>
                            </div>

                            <div class="col-xs-4">
                                <div class="form-group">
                                    <select name="bill_state" class="form-control input-lg"></select>
                                </div>
                            </div>

                            <div class="col-xs-4">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_bill_zip" name="bill_zip" placeholder="ZIP/Postal">
                                </div>
                            </div>

                            <div class="col-xs-8">
                                <div class="form-group">
                                    <select name="bill_country" class="form-control input-lg">
                                        <?php if (sys_get('pinned_countries')): ?>
                                            <?php foreach (sys_get('list:pinned_countries') as $iso_code): ?>
                                                <option value="<?= e($iso_code); ?>"><?= e(cart_countries()[$iso_code]); ?></option>
                                            <?php endforeach; ?>
                                            <option disabled>--</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div role="tabpanel" class="tab-pane" id="pos-ship-address-tab">

                        <label for="" class="control-label">Shipping Name &amp; Contact</label>
                        <label for="" class="control-label pull-right"><a class="copy-billing" href="#" tabindex="-1"><i class="fa fa-copy"></i> Copy from Billing</a></label>

                        <div class="row row-padding-sm">

                            <?php if (sys_get('donor_title') != 'hidden'): ?>
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <?php if (sys_get('donor_title_options') == ''): ?>
                                            <input type="text" class="form-control input-lg" id="input_ship_title" name="ship_title" placeholder="Mr/Ms">
                                        <?php else: ?>
                                            <select name="ship_title" id="input_ship_title" class="form-control input-lg">
                                                <option value="">--</option>
                                                <?php foreach (explode(',', sys_get('donor_title_options')) as $option): ?>
                                                    <option value="<?= e(trim($option)); ?>"><?= e(trim($option)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col-xs-4">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_ship_first_name" name="ship_first_name" placeholder="First Name">
                                </div>
                            </div>

                            <div class="col-xs-<?= e((sys_get('donor_title') != 'hidden') ? '5' : '8'); ?>">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_ship_last_name" name="ship_last_name" placeholder="Last Name">
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_ship_organization_name" name="ship_organization_name" placeholder="Organization">
                                </div>
                            </div>
                        </div>

                        <div class="row row-padding-sm">
                            <div class="col-sm-7">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></div>
                                        <input type="email" class="form-control input-lg" id="input_ship_email" name="ship_email" placeholder="email@address.com">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-phone fa-fw"></i></div>
                                        <input type="tel" class="form-control input-lg" id="input_ship_phone" name="ship_phone" placeholder="555-555-5555">
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label for="" class="control-label">Shipping Address</label>
                                    <input type="text" class="form-control input-lg" id="input_ship_address" name="ship_address" placeholder="Address Line 1">
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_ship_address2" name="ship_address2" placeholder="Address Line 2">
                                </div>
                            </div>

                            <div class="col-xs-8">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_ship_city" name="ship_city" placeholder="City">
                                </div>
                            </div>

                            <div class="col-xs-4">
                                <div class="form-group">
                                    <select name="ship_state" class="form-control input-lg"></select>
                                </div>
                            </div>

                            <div class="col-xs-4">
                                <div class="form-group">
                                    <input type="text" class="form-control input-lg" id="input_ship_zip" name="ship_zip" placeholder="ZIP/Postal">
                                </div>
                            </div>

                            <div class="col-xs-8">
                                <div class="form-group">
                                    <select name="ship_country" class="form-control input-lg">
                                        <?php if (sys_get('pinned_countries')): ?>
                                            <?php foreach (sys_get('list:pinned_countries') as $iso_code): ?>
                                                <option value="<?= e($iso_code); ?>"><?= e(cart_countries()[$iso_code]); ?></option>
                                            <?php endforeach; ?>
                                            <option disabled>--</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-lg btn-bold pos-save-addresses"><i class="fa fa-check"></i></button>
                <button type="button" class="btn btn-default btn-lg btn-bold" data-dismiss="modal"><i class="fa fa-times"></i></button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-shipping" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-body pos-shipping-options">

                <span class="text-muted text-center">No Shipping Options Available</span>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-lg btn-bold"><i class="fa fa-check"></i></button>
                <button type="button" class="btn btn-default btn-lg btn-bold" data-dismiss="modal"><i class="fa fa-times"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-taxes" tabindex="-1">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-body pos-tax-options tab-pane">

                <div class="row">
                    <div class="form-group col-sm-12">
                        <label for="" class="control-label">Taxable Address</label>
                        <input type="text" class="form-control" id="input_tax_address1" name="tax_address1" placeholder="Address Line 1">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-sm-12">
                        <input type="text" class="form-control" id="input_tax_address2" name="tax_address2" placeholder="Address Line 2">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-sm-8">
                        <label for="" class="control-label">City</label>
                        <input type="text" class="form-control" id="input_tax_city" name="tax_city" placeholder="City">
                    </div>

                    <div class="form-group col-sm-4">
                        <label for="" class="control-label">State</label>
                        <select name="tax_state" class="form-control"></select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-sm-8">
                        <label for="" class="control-label">ZIP</label>
                        <input type="text" class="form-control" id="input_tax_zip" name="tax_zip" placeholder="ZIP">
                    </div>

                    <div class="form-group col-sm-4">
                        <label for="" class="control-label">Country</label>
                        <select class="form-control" name="tax_country">
                            <option value=""></option>
                            <?php if (sys_get('pinned_countries')): ?>
                                <?php foreach (sys_get('list:pinned_countries') as $iso_code): ?>
                                    <option value="<?= e($iso_code); ?>"><?= e($iso_code); ?></option>
                                <?php endforeach; ?>
                                <option disabled>--</option>
                            <?php endif; ?>
                            <?php foreach (cart_countries() as $iso_code => $name): ?>
                                <option value="<?= e($iso_code); ?>"><?= e($iso_code); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-lg btn-bold"><i class="fa fa-check"></i></button>
                <button type="button" class="btn btn-default btn-lg btn-bold" data-dismiss="modal"><i class="fa fa-times"></i></button>
            </div>

        </div>
    </div>
</div>

</form>

<div class="modal fade modal-success" id="modal-select-child" tabindex="-1">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <form id="order-select-child">

                <div class="modal-body">

                    <div class="row">
                        <div class="form-group form-group-lg col-sm-12">
                            <label for="" class="control-label">Child Reference Number</label>
                            <input type="text" class="form-control" id="input_reference_number" name="reference_number" placeholder="Child Reference Number">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group form-group-lg col-sm-12">
                            <label for="" class="control-label">Payment Option</label>
                            <select name="payment_option_id" class="form-control">
                                <?php foreach ($payment_option_groups as $payment_option_group): ?>
                                    <optgroup label="<?= e($payment_option_group->name); ?>">
                                        <?php foreach ($payment_option_group->options as $option): ?>
                                            <option value="<?= e($option->id); ?>"><?= e($option->description); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                <?php if (sys_get('rpp_default_type') === 'natural'): ?>
                    <input type="hidden" name="recurring_with_initial_charge" value="1">
                <?php else: ?>
                    <div class="row">
                        <div class="form-group form-group-lg col-sm-12">
                            <label class="checkbox-inline"><input type="checkbox" name="recurring_with_initial_charge" value="1"> Make first payment today (recurring only)</label>
                        </div>
                    </div>
                <?php endif; ?>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-lg btn-bold"><i class="fa fa-plus"></i> Add</button>
                    <button type="button" class="btn btn-default btn-lg btn-bold" data-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-select-fundraiser" tabindex="-1">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">

            <form id="order-select-fundraiser">

                <div class="modal-body">

                    <div class="form-group">
                        <label for="" class="control-label">Fundraising Page</label>
                        <input type="text" class="form-control ds-fundraisers" id="input_fundraising_page_id" name="fundraising_page_id" placeholder="Fundraising Page">
                    </div>

                    <div class="row">
                        <div class="form-group form-group-lg col-sm-12">
                            <label for="" class="control-label">One-Time Amount</label>
                            <input type="number" class="form-control" id="input_amount" name="amount" placeholder="Amount" step="0.01">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="input_comments">Special Notes</label>
                        <textarea class="form-control" id="input_comments" name="comments" style="height:80px;"></textarea>
                        <div class="checkbox">
                            <label for="input_is_anonymous">
                                <input id="input_is_anonymous" type="checkbox" name="is_anonymous" value="1">
                                Keep me anonymous
                            </label>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-lg btn-bold"><i class="fa fa-plus"></i> Add</button>
                    <button type="button" class="btn btn-default btn-lg btn-bold" data-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-promo" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <form id="order-promo-form">

                <div class="modal-body">

                    <div class="form-group">
                        <label for="promocodes" class="control-label">Promotions</label>
                        <input type="text" class="form-control ds-promocodes input-lg" id="promocodes" name="promocodes" placeholder="Apply promo code(s)...">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-lg btn-bold"><i class="fa fa-check"></i></button>
                    <button type="button" class="btn btn-default btn-lg btn-bold" data-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-payment" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content"></div>
    </div>
</div>

<!-- detect bs breakpoints in js -->
<div class="device-xs visible-xs"></div>
<div class="device-sm visible-sm"></div>
<div class="device-md visible-md"></div>
<div class="device-lg visible-lg"></div>
<script>
    $.isBreakpoint=function(alias){return $('.device-' + alias).is(':visible');};
</script>

<script src="<?= e($config->script_src); ?>"></script>
<script>
Givecloud.setConfig(<?= dangerouslyUseHTML(json_encode($config, JSON_PRETTY_PRINT)); ?>);
</script>

<?php

foreach (collect($gateways)->unique('id') as $provider) {
    if ($provider && $provider->gateway instanceof \Ds\Domain\Commerce\Contracts\Viewable) {
        echo $provider->gateway->getView() . PHP_EOL;
    }
}

?>

<!-- json data -->
<script>
window._settings = <?= dangerouslyUseHTML(json_encode([
    'default_country' => sys_get('default_country'),
    'force_country' => sys_get('force_country'),
    'pinned_countries' => sys_get('pinned_countries'),
    'rpp_default_type' => sys_get('rpp_default_type'),
    'payment_day_options' => explode(',', sys_get('payment_day_options')),
    'payment_day_of_week_options' => explode(',', sys_get('payment_day_of_week_options')),
    'dp_fields' => (dpo_is_enabled()) ? $dp_fields : false,
    'dp_udfs' => (dpo_is_enabled()) ? $dp_udfs : false,
    'dp_codes' => (dpo_is_enabled()) ? $dp_codes : false,
    'product_bookmarks' => $product_bookmarks,
    'use_fulfillment' => sys_get('use_fulfillment'),
], JSON_PRETTY_PRINT)); ?>;
</script>

@include('_settings')

<script charset="utf-8" src="<?= e(jpanel_asset_url('dist/js/vendor.js')); ?>"></script>
<script charset="utf-8" src="<?= e(jpanel_asset_url('dist/js/app.js')); ?>"></script>

<script>
    $(pos.init);
</script>

</body>
</html>
