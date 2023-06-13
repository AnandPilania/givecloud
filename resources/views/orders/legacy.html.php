<style>
    html, body { height:100%; }

    #spaContent #mainContent {
        height:100% !important;
    }

    .account-panel {
        position:relative;
        padding:30px; margin: 0 0 30px 0px; border-radius: 8px; box-sizing: border-box; color:#ddd;
        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#eaeaea+0,777777+6,777777+100 */
        background: #666666; /* Old browsers */
        background: -moz-linear-gradient(left, #666666 0%, #777777 7%, #777777 100%); /* FF3.6-15 */
        background: -webkit-linear-gradient(left, #666666 0%,#777777 7%,#777777 100%); /* Chrome10-25,Safari5.1-6 */
        background: linear-gradient(to right, #666666 0%,#777777 7%,#777777 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#666666', endColorstr='#777777',GradientType=1 ); /* IE6-9 */
    }
    .account-panel a,
        .account-panel .text-lg { color:#fff; }
        .account-panel .dropdown-menu a { color:#000; }
        .account-panel .popover { color:#000; }
        .section-header { font-size:11px; font-weight: bold; color:#ddd; text-transform: uppercase; padding-bottom:4px; border-bottom:1px solid #999; margin-bottom:7px; }
        .account-item { margin:12px 0px; border-bottom:1px dotted #888; padding-bottom:12px; }
        .account-item:last-child { border-bottom:none; padding-bottom:12px; }

    .my-1 { margin-top:4px; margin-bottom:4px; }

    .text-muted { opacity:0.6; }
</style>


<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix" style="border-bottom:none;">
            <span class="page-header-text"><?= e($pageTitle) ?></span>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if($orderModel->is_paid && $orderModel->userCan(['edit','fullfill','refund'])): ?>

                    <div class="btn-group" role="group" aria-label="...">
                        <?php if($orderModel->is_refundable && $orderModel->userCan('refund')): ?>
                            <?php if ($orderModel->totalamount > 0): ?>
                                <button type="button" data-target="#refund-modal" data-toggle="modal" class="btn btn-default" title="Refund" data-popover-bottom="<strong>Refund</strong><br>Perform a full or partial refund of this contribution."><i class="fa fa-fw fa-reply text-danger"></i></button>
                            <?php else: ?>
                                <a href="javascript:void(0);" data-popover-bottom="<strong>Refund</strong><br>Perform a full or partial refund of this contribution." onclick="$.alert('You cannot refund this contribution as there was nothing charged to the customer at the time of checkout.', 'danger', 'fa-reply');" class="btn btn-default" title="Refund"><i class="fa fa-fw fa-reply text-danger"></i></a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($orderModel->userCan('edit')): ?>
                            <a href="#delete-order-modal" data-popover-bottom="<strong>Delete</strong><br>Completely and permanently delete this contribution and anything it my have created (tax receipts, recurring payments, etc)." class="btn btn-default" data-toggle="modal"><i class="text-danger fa fa-trash"></i></a>
                        <?php endif; ?>

                        <?php if (feature('givecloud_pro') && $orderModel->userCan(['edit','fullfill'])): ?>
                            <?php if($orderModel->is_fulfillable): ?>
                            <a href="<?= e(route('backend.orders.packing_slip', ['id' => $orderModel->getKey()])) ?>" target="_blank" class="btn btn-default" data-popover-bottom="<strong>Print Packing Slip</strong><br>Print a packing slip used to pick, pack and fulfill a physical contribution. This is different from an invoice."><i class="fa fa-print fa-fw"></i></a>
                            <?php endif; ?>

                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-popover-bottom="<strong>Re-Send Email Notifications</strong><br>Choose who you'd like to renotify about this contribution via email.">
                                    <i class="fa fa-envelope-o fa-fw"></i>  <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="<?= e(route('backend.orders.reprocess_product_specific_emails', ['o' => $orderModel->invoicenumber, 'i' => $orderModel->getKey()])) ?>">
                                            <i class="fa fa-envelope fa-fw"></i> Renotify Supporter Only
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= e(route('backend.orders.notify_site_owner', ['o' => $orderModel->invoicenumber, 'i' => $orderModel->id])) ?>">
                                            <i class="fa fa-envelope fa-fw"></i> Renotify Staff Only
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (feature('givecloud_pro')): ?>
                        <a href="<?= e(secure_site_url(route('order_review', $orderModel->invoicenumber, false))) ?>" target="_blank" class="btn btn-default" data-popover-bottom="<strong>View Customer Receipt</strong><br>View the link that the customer/donor see's when reviewing/tracking their payment. (opens in a new window)"><i class="fa fa-fw fa-external-link"></i></a>
                        <?php endif; ?>
                    </div>

                    <?php if ($orderModel->user_can_fulfill): ?>
                        <?php if(!$orderModel->iscomplete): ?>
                            <a href="<?= e(route('backend.orders.complete', $orderModel)) ?>" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm hiddem-md"> Mark Fulfilled</span></a>
                        <?php else: ?>
                            <a href="<?= e(route('backend.orders.incomplete', $orderModel)) ?>" class="btn btn-success btn-outline"><i class="fa fa-check-square-o"></i><span class="hidden-xs hidden-sm hiddem-md"> Fulfilled</span></a>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

            <?php if($orderModel->confirmationdatetime): ?>
                <div class="text-secondary">
                    <?php if($orderModel->is_pos): ?>
                        <div class="pull-right"><i class="fa fa-calculator"></i> POS entry by <?= e($orderModel->createdBy->full_name) ?></div>
                    <?php endif; ?>

                    Via <?= e($orderModel->source) ?> <?php if ($orderModel->kiosk): ?>(<?= e($orderModel->kiosk->name) ?>)<?php endif ?> on
                    <?php if ($orderModel->ordered_at): ?>
                        <?= e(toLocalFormat($orderModel->ordered_at, 'l, F j, Y')) ?> (<?= e(toLocalFormat($orderModel->ordered_at, 'humans')) ?>)
                    <?php else: ?>
                        <?= e(toLocalFormat($orderModel->started_at, 'l, F j, Y')) ?> (<?= e(toLocalFormat($orderModel->started_at, 'humans')) ?>)
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<?php if($orderModel->trashed()): ?>
    <div class="alert alert-danger"><i class="fa fa-trash fa-fw"></i> Deleted by <?= e($orderModel->deletedBy->full_name) ?> on <?= e(toLocalFormat($orderModel->deleted_at, 'M j, Y \a\t g:ia')) ?> <small>(<?= e(toLocalFormat($orderModel->deleted_at, 'humans')) ?>)</small>.</div>
<?php endif; ?>

<?php if (request('re') == '1'): ?>
    <div class="alert alert-success">
        <i class="fa fa-check fa-fw"></i> Customer notified successfully.
    </div>
<?php elseif (request('re') == '0'): ?>
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle fa-fw"></i> Customer notification failed.
    </div>
<?php endif; ?>

<?php if (request('ss')): ?>
    <div class="alert alert-success"><i class="fa fa-check fa-fw"></i> <?= e(request('ss')); ?></div>
<?php elseif (request('sf')): ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle fa-fw"></i> <?= e(request('sf')); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-basic">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <?php if(!$orderModel->is_view_only): ?><a href="#edit-order-modal" data-toggle="modal" class="pull-right btn btn-info btn-xs btn-outline"><i class="fa fa-pencil"></i> Edit</a><?php endif; ?>
                    <div class="panel-sub-title">Invoice</div>
                </div>

                <div style="margin:10px;">
                    <div class="row bottom-gutter">
                        <div class="col-sm-6">
                            <strong>Billing Address</strong><br>
                            <ul class="fa-ul" style="margin-left:21px;">
                                <?php if ($orderModel->billingaddress1): ?>
                                    <li><i class="fa fa-li fa-home"></i>
                                        <?= e($orderModel->billing_first_name . ' ' . $orderModel->billing_last_name) ?>
                                        <br>
                                        <?php if($orderModel->billing_organization_name): ?>
                                            <?= e($orderModel->billing_organization_name) ?><br>
                                        <?php endif; ?>
                                        <?php if($orderModel->has_avs_address_failure): ?>
                                            <div class="label label-warning" data-popover-bottom="<strong>Address Failed AVS</strong><br>Your payment processor has indicated that the address entered during payment does not match what's on file with the bank."><i class="fa fa-exclamation-triangle"></i> Address Failed AVS</div><br>
                                        <?php endif; ?>
                                        <?= dangerouslyUseHTML(nl2br(e(address_format(
                                            $orderModel->billingaddress1,
                                            $orderModel->billingaddress2,
                                            $orderModel->billingcity,
                                            $orderModel->billingstate,
                                            $orderModel->billingzip,
                                            null
                                        )))); ?>
                                        <?php if($orderModel->has_avs_zip_failure): ?>
                                            <br><div class="label label-warning" data-popover-bottom="<strong>Failed AVS</strong><br>Your payment processor has indicated that the ZIP entered during payment does not match what's on file with the bank."><i class="fa fa-exclamation-triangle"></i> ZIP Failed AVS</div>
                                        <?php endif; ?>
                                    </li>
                                <?php elseif(trim($orderModel->billing_first_name)): ?>
                                    <li class="text-muted"><i class="fa fa-li fa-home"></i>
                                        <?= e($orderModel->billing_first_name . ' ' . $orderModel->billing_last_name) ?></li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-home"></i> N/A</li>
                                <?php endif; ?>

                                <?php if ($orderModel->billingcountry): ?>
                                    <li><i class="fa fa-li"><img src="<?= e(flag($orderModel->billingcountry)) ?>" style="margin-right:3px; width:16px; height:16px; vertical-align:middle;"></i>
                                        <?= e(cart_countries()[$orderModel->billingcountry]) ?> <?php if($orderModel->has_ip_geography_mismatch): ?><span class="label label-warning" data-popover-bottom="<strong>Country May Not Match IP (<?= e($orderModel->ip_country) ?>)</strong><br>The billing address may not match the IP address from which the contribution was placed."><i class="fa fa-fw fa-exclamation-triangle"></i> May Not Match IP (<?= e($orderModel->ip_country) ?>)</span><?php endif; ?>
                                    </li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-globe"></i> N/A</li>
                                <?php endif; ?>

                                <?php if ($orderModel->billingemail): ?>
                                    <li><i class="fa fa-li fa-envelope-o"></i>
                                        <a href="mailto:<?= e($orderModel->billingemail) ?>"><?= e($orderModel->billingemail) ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-envelope-o"></i> N/A</li>
                                <?php endif; ?>

                                <?php if ($orderModel->billingphone): ?>
                                    <li><i class="fa fa-li fa-phone"></i>
                                        <a href="tel:<?= e($orderModel->billingphone) ?>"><?= e($orderModel->billingphone) ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-phone"></i> N/A</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php if (feature('shipping')): ?>
                        <div class="col-sm-6">
                            <strong>Shipping Address</strong><br>
                            <ul class="fa-ul" style="margin-left:21px;">
                                <?php if ($orderModel->shipaddress1): ?>
                                    <li><i class="fa fa-li fa-home"></i>
                                        <?= e($orderModel->shipping_first_name . ' ' . $orderModel->shipping_last_name) ?><br>
                                        <?php if($orderModel->shipping_organization_name): ?>
                                            <?= e($orderModel->shipping_organization_name) ?><br>
                                        <?php endif; ?>
                                        <?= dangerouslyUseHTML(nl2br(e(address_format(
                                            $orderModel->shipaddress1,
                                            $orderModel->shipaddress2,
                                            $orderModel->shipcity,
                                            $orderModel->shipstate,
                                            $orderModel->shipzip,
                                            null
                                        )))); ?>
                                    </li>
                                <?php elseif(trim($orderModel->shipping_first_name)): ?>
                                    <li class="text-muted"><i class="fa fa-li fa-home"></i>
                                        <?= e($orderModel->shipping_first_name . ' ' . $orderModel->shipping_last_name) ?></li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-home"></i> N/A</li>
                                <?php endif; ?>

                                <?php if ($orderModel->shipcountry): ?>
                                    <li><i class="fa fa-li"><img src="<?= e(flag($orderModel->shipcountry)) ?>" style="margin-right:3px; width:16px; height:16px; vertical-align:middle;"></i>
                                        <?= e(cart_countries()[$orderModel->shipcountry]) ?>
                                    </li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-globe"></i> N/A</li>
                                <?php endif; ?>

                                <?php if ($orderModel->shipemail): ?>
                                    <li><i class="fa fa-li fa-envelope-o"></i>
                                        <a href="mailto:<?= e($orderModel->shipemail) ?>"><?= e($orderModel->shipemail) ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-envelope-o"></i> N/A</li>
                                <?php endif; ?>

                                <?php if ($orderModel->shipphone): ?>
                                    <li><i class="fa fa-li fa-phone"></i>
                                        <a href="tel:<?= e($orderModel->shipphone) ?>"><?= e($orderModel->shipphone) ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="text-muted"><i class="fa fa-li fa-phone"></i> N/A</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (feature('givecloud_pro')): ?>
                    <div class="row bottom-gutter">
                        <div class="col-sm-6">
                            <strong>Special Notes</strong><br>
                            <?php if($orderModel->comments): ?>
                                <?= e($orderModel->comments) ?>
                            <?php else: ?>
                                <small class="text-muted">None Provided</small>
                            <?php endif; ?>
                        </div>
                        <?php if($orderModel->customer_notes): ?>
                            <div class="col-sm-6">
                                <strong>Note to Customer</strong><br>
                                <?= dangerouslyUseHTML($orderModel->customer_notes) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <table class="table table-invoice">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th width="50" style="text-align:center;">Qty</th>
                                        <th width="80" style="text-align:right;">Price</th>
                                        <th width="80" style="text-align:right;">Total (<?= e($orderModel->currency->unique_symbol) ?>)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orderModel->items as $item): ?>
                                        <tr>
                                            <td align="left" style="<?= e($item->is_locked ? 'padding-left:40px;' : '') ?>">

                                                <!-- thumbail -->
                                                <div style="float:left; text-align:right; width:55px;">
                                                    <a <?php if ($item->admin_link): ?>href="<?= e($item->admin_link) ?>"<?php endif; ?> style="display:inline-block;">
                                                        <?php if ($item->is_fundraising_form_upgrade): ?>
                                                            <div class="flex items-center justify-center bg-transparent border-none text-yellow-300 mt-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="currentColor" class="w-6 h-8">
                                                                    <path d="M381.2 172.8C377.1 164.9 368.9 160 360 160h-156.6l50.84-127.1c2.969-7.375 2.062-15.78-2.406-22.38S239.1 0 232 0h-176C43.97 0 33.81 8.906 32.22 20.84l-32 240C-.7179 267.7 1.376 274.6 5.938 279.8C10.5 285 17.09 288 24 288h146.3l-41.78 194.1c-2.406 11.22 3.469 22.56 14 27.09C145.6 511.4 148.8 512 152 512c7.719 0 15.22-3.75 19.81-10.44l208-304C384.8 190.2 385.4 180.7 381.2 172.8z"/>
                                                                </svg>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="avatar-<?= e($item->is_locked ? 'lg' : 'xl') ?>" style="background-image:url('<?= e($item->image_thumb) ?>');"></div>
                                                        <?php endif; ?>
                                                    </a>
                                                </div>

                                                <!-- details -->
                                                <div style="margin-left:65px;">

                                                        <?php if (feature('edit_order_items') && !$orderModel->is_view_only && !$orderModel->isForFundraisingForm() && (count($item->fields) > 0 || $item->variant)): ?>
                                                            <div class="btn-group pull-right">
                                                                <button type="button" class="btn btn-xs btn-outline btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <i class="fa fa-pencil"></i> Edit &nbsp;<span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu pull-right">
                                                                    <?php if (count($item->fields) > 0): ?>
                                                                        <li><a href="#" class="change-custom-fields" data-item-id="<?= e($item->id) ?>"><i class="fa fa-fw fa-pencil"></i> Change Custom Fields</a></li>
                                                                    <?php endif; ?>
                                                                    <?php if ($item->variant): ?>
                                                                        <li><a href="#" class="change-product" data-item-id="<?= e($item->id) ?>"><i class="fa fa-fw fa-pencil"></i> Change Product</a></li>
                                                                    <?php endif; ?>
                                                                    <?php if (sys_get('gift_aid') == 1 && $item->variant->product->is_tax_receiptable == 1): ?>
                                                                        <li><a href="#" class="change-gift-aid-eligibility" data-item-id="<?= e($item->id) ?>" data-gift-aid-eligible="<?= e($item->gift_aid ? 1 : 0) ?>"><i class="fa fa-fw fa-pencil"></i> Change Gift Aid Eligibility</a></li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        <?php endif; ?>

                                                    <!-- product -->
                                                    <?php if ($item->variant && $item->is_locked && $item->lockedToItem->upgraded_to_recurring): ?>

                                                        <span class="inline-flex items-center mb-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Monthly Upgrade</span>

                                                    <?php elseif ($item->variant): ?>

                                                        <strong style="font-size:14px;">
                                                            <?php if ($item->admin_link): ?>
                                                                <a href="<?= e($item->admin_link) ?>"><?= e($item->variant->product->name) ?></a>
                                                            <?php else: ?>
                                                                <?= e($item->variant->product->name) ?>
                                                            <?php endif; ?>
                                                        </strong>
                                                        <?= dangerouslyUseHTML((trim($item->variant->variantname) != '') ? '(' . $item->variant->variantname . ')' : '') ?>
                                                        <span class="code"><?= e($item->code) ?><br /><?php if(is_numeric($item->variant->weight) && $item->variant->weight > 0): ?>(Weight: <?= e($item->variant->weight) ?>lbs)<?php endif ; ?></span>

                                                    <?php else: ?>

                                                        <strong style="font-size:14px;">
                                                            <?php if ($item->admin_link): ?>
                                                                <a href="<?= e($item->admin_link) ?>"><?= e($item->description) ?></a>
                                                            <?php else: ?>
                                                                <?= dangerouslyUseHTML($item->description) ?>
                                                            <?php endif; ?>
                                                        </strong>
                                                        <span class="code"><?= e($item->code) ?></span>

                                                    <?php endif; ?>

                                                        <?php if($item->promocode != ''): ?>
                                                            <div class="pc"><span class="code">PROMO: <strong><?= e($item->promo->code) ?></strong></span><span class="desc"> <?= e($item->promo->description) ?></span></div>
                                                        <?php endif; ?>

                                                        <?php if($item->is_recurring): ?>
                                                            <div class="recurring_desc"><?= e($item->payment_string) ?></div>
                                                            <?php if($item->dcc_eligible && $item->dcc_recurring_amount > 0): ?>
                                                                <div class="text-muted">Includes <?= e(money($item->dcc_recurring_amount, $orderModel->currency)) ?> for <?= e(sys_get('dcc_label')) ?></div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php if ($item->gl_code && ! ($item->is_locked && $item->lockedToItem->upgraded_to_recurring)): ?>
                                                            <span class="code">GL: <strong><?= e($item->gl_code) ?></strong></span>
                                                        <?php endif; ?>

                                                        <?php if($item->tribute && $item->tribute->userCan('view')): ?>
                                                            For: <?= e($item->tribute->name) ?> (<?= e($item->tribute->tributeType->label) ?>)
                                                            <?php if($item->tribute->notify == 'email'): ?>
                                                                <br>Send email to: <?= e($item->tribute->notify_name) ?>
                                                                <br><?= e($item->tribute->notify_email) ?>
                                                            <?php elseif($item->tribute->notify == 'letter'): ?>
                                                                <br>Send letter to <?= e($item->tribute->notify_name) ?>
                                                                <br><?= dangerouslyUseHTML(nl2br(e(address_format($item->tribute->notify_address, null, $item->tribute->notify_city, $item->tribute->notify_state, $item->tribute->notify_zip, $item->tribute->notify_country)))) ?>
                                                            <?php else: ?>
                                                                <br>No notification.
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php foreach($item->fields as $field): ?>
                                                            <div class="custom_field_desc"><?= e($field->name) ?>:&nbsp;<strong><?= e($field->value_formatted) ?></strong></div>
                                                        <?php endforeach; ?>

                                                    <!-- honor roll comments -->
                                                    <?php if ($item->public_message): ?>
                                                        <div class="top-gutter-sm">
                                                            &ldquo;<?= e($item->public_message) ?>&rdquo;<br>
                                                            <?php if($orderModel->is_anonymous): ?>
                                                                <small class="text-muted">- Anonymous</small>
                                                            <?php else: ?>
                                                                <small class="text-muted">- <?= e($orderModel->member->display_name) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- gift aid -->
                                                    <?php if($item->gift_aid): ?>
                                                        <div class="top-gutter-sm"><i class="fa fa-check-square-o"></i> Gift Aid</div>
                                                    <?php endif; ?>

                                                    <!-- original variant -->
                                                    <?php if ($item->variant && $item->original_variant_id !== $item->variant->id): ?>

                                                        <?php $item->load('originalVariant.product'); ?>
                                                        <div class="text-muted" style="font-size:10px; font-style:italic; margin:7px 0px;">Original Item: <?= e($item->originalVariant->product->name) ?><?= e(($item->originalVariant->variantname) ? ' - '.$item->originalVariant->variantname : '') ?> (<?= e($item->originalVariant->product->code) ?>)</div>
                                                    <?php endif; ?>

                                                    <!-- buttons -->
                                                    <div style="margin-top:10px">
                                                        <?php if($item->recurringPaymentProfile): ?>
                                                            <a href="/jpanel/recurring_payments/<?= e($item->recurringPaymentProfile->profile_id) ?>" class="btn btn-xs btn-info"><i class="fa fa-refresh"></i> View Recurring Payment (<?= e($item->recurringPaymentProfile->payment_string) ?>)</a>
                                                        <?php endif; ?>
                                                        <?php if($item->tribute && $item->tribute->userCan('view')): ?>
                                                            <a href="#" class="btn btn-xs btn-info ds-tribute" data-tribute-id="<?= e($item->tribute->id) ?>"><i class="fa fa-gift"></i> View Tribute</a>
                                                        <?php endif; ?>
                                                        <?php if(!$orderModel->is_view_only && $item->variant && $item->variant->product->allow_check_in == 1): ?>
                                                            <a href="<?= e(route('backend.orders.checkin', ['o' => $orderModel->id, 'i' => $item->id])) ?>" class="btn btn-info btn-xs"><i class="fa fa-qrcode"></i> Check-In</a>
                                                        <?php endif; ?>
                                                        <?php if(!$orderModel->is_view_only && $item->variant && $item->variant->file): ?>
                                                            <a href="<?= e(route('backend.orders.reprocess_downloads', $orderModel)) ?>" class="btn btn-info btn-xs"><i class="fa fa-envelope"></i> Send Email</a>
                                                        <?php endif; ?>
                                                        <?php if($item->fundraisingPage): ?>
                                                            <a href="<?= e($item->fundraisingPage->absolute_url) ?>" target="_blank" class="btn btn-info btn-outline btn-xs"><i class="fa fa-users"></i> <?= e($item->fundraisingPage->title) ?></a>
                                                        <?php endif; ?>
                                                        <?php if($item->variant->membership && !$item->groupAccount): ?>
                                                            <a href="<?= e(route('backend.orders.applyGroup', $item)) ?>" class="btn btn-info btn-outline btn-xs"><i class="fa fa-plus"></i> Add to "<?= e($item->variant->membership->name) ?>"</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- a bundled sub-item -->
                                            <?php if ($item->is_locked): ?>
                                                <td class="text-center text-muted"><?= e($item->qty) ?></td>
                                                <td class="text-right text-muted"><?= e(money($item->price, $orderModel->currency)) ?></td>
                                                <td class="text-right text-muted">-</td>

                                            <!-- an item that has bundled items attached -->
                                            <?php elseif ($item->lockedItems->count() > 0): ?>
                                                <td class="text-center"><?= e($item->qty) ?></td>
                                                <td style="text-align:right;"><?= e(money($item->locked_variants_price, $orderModel->currency)) ?></td>
                                                <td style="text-align:right;"><?= e(money($item->locked_variants_total, $orderModel->currency)) ?></td>

                                            <!-- a standard line-item -->
                                            <?php else: ?>
                                                <td class="text-center"><?= e($item->qty) ?></td>
                                                <td style="text-align:right;"><?= e(money($item->price, $orderModel->currency)) ?></td>
                                                <td style="text-align:right;"><?= e(money($item->total, $orderModel->currency)) ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td rowspan="6"></td>
                                        <td colspan="2">Subtotal</td>
                                        <td style="text-align:right;"><?= e(money($orderModel->subtotal, $orderModel->currency)) ?></td>
                                    </tr>
                                    <?php if($orderModel->shippable_items > 0): ?>
                                    <tr>
                                        <td colspan="2">Shipping
                                            <?php if($orderModel->shipping_method_name): ?>
                                                <small> <?= e($orderModel->shipping_method_name) ?></small>
                                            <?php endif ?>
                                        </td>
                                        <td style="text-align:right;"><?= e(money($orderModel->shipping_amount, $orderModel->currency)) ?></td>
                                    </tr>
                                    <?php endif ?>
                                    <?php if($orderModel->taxtotal): ?>
                                        <tr>
                                            <td colspan="2"><a href="#taxes-modal" data-toggle="modal">Taxes</a></td>
                                            <td style="text-align:right;"><a href="#taxes-modal" data-toggle="modal"><?= e(money($orderModel->taxtotal, $orderModel->currency)) ?></a></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if($orderModel->dcc_total_amount): ?>
                                        <tr>
                                            <td colspan="2"><?= e(sys_get('dcc_label')) ?></td>
                                            <td style="text-align:right;"><?= e(money($orderModel->dcc_total_amount, $orderModel->currency)) ?></a></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="text-bold">
                                        <td colspan="2">Total</td>
                                        <td style="text-align:right;"><?= e(money($orderModel->totalamount, $orderModel->currency)) ?></td>
                                    </tr>
                                    <?php if($orderModel->refunded_at): ?>
                                        <?php foreach(data_get($orderModel, 'successfulPayments.0.successfulRefunds') as $refund): ?>
                                            <tr class="text-bold danger">
                                                <td colspan="2">
                                                    Refund<br>
                                                    <small>on <?= e(toLocalFormat($refund->created_at, 'M j, Y')) ?> by <?= e($refund->refundedBy->full_name) ?></small>
                                                </td>
                                                <td style="text-align:right;"><?= e(money(-$refund->amount, $refund->currency)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="text-bold">
                                            <td colspan="2">Balance</td>
                                            <td style="text-align:right;"><?= e(money($orderModel->balance_amt, $orderModel->currency)) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="col-lg-3">

        <?php if($orderModel->is_test): ?>
            <div class="alert alert-warning text-bold">
                <i class="fa fa-exclamation-triangle"></i> Test Contribution
            </div>
        <?php endif; ?>

        <?php if($orderModel->refunded_at): ?>
            <?php foreach(data_get($orderModel, 'successfulPayments.0.successfulRefunds') as $refund): ?>
                <div class="alert alert-danger">
                    Refunded <strong><?= e(money($refund->amount, $refund->currency)) ?></strong> on <strong><?= e(toLocalFormat($orderModel->refunded_at, 'M j, Y')) ?></strong><br><small><?= e($refund->refundedBy->full_name) ?> (Txn ID: <?= e($refund->reference_number) ?>)</small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if($orderModel->warning_count > 0): ?>
            <div class="alert alert-warning">
                <a href="#warning-list" data-toggle="collapse" class="pull-right btn btn-warning btn-xs">Expand</a>
                <i class="fa fa-exclamation-triangle"></i> <strong>(<?= e($orderModel->warning_count) ?>) warnings</strong> to review.
                <div id="warning-list" class="collapse top-gutter">
                    <ul class="fa-ul">
                        <?php if($orderModel->has_cvc_failure): ?>
                            <li data-popover-bottom="<strong>CVC Verification</strong><br>Givecloud receives data from your payment gateway about whether or not the CVC entered matches what's actually on the card (CVC Check)."><i class="fa fa-li fa-times"></i> The <strong>CVC code does not match</strong> what the bank has on file.</li>
                        <?php endif; ?>
                        <?php if($orderModel->has_avs_address_failure): ?>
                            <li data-popover-bottom="<strong>AVS Address Verification</strong><br>Givecloud receives data from your payment gateway about whether or not the address on the card matches the address on file with the bank (AVS Check). It's not uncommon for donors to misspell their address or mistype their CVC. However, its important to pay special attention when the verification fails as it may be an indicator of fraud."><i class="fa fa-li fa-times"></i>  The <strong>Billing Address</strong> does not match what the bank has on file.</li>
                        <?php endif; ?>
                        <?php if($orderModel->has_avs_zip_failure): ?>
                            <li data-popover-bottom="<strong>AVS Address Verification</strong><br>Givecloud receives data from your payment gateway about whether or not the address on the card matches the address on file with the bank (AVS Check). It's not uncommon for donors to misspell their address or mistype their CVC. However, its important to pay special attention when the verification fails as it may be an indicator of fraud."><i class="fa fa-li fa-times"></i> The <strong>Billing ZIP</strong> code does not match what the bank has on file.</li>
                        <?php endif; ?>
                        <?php if($orderModel->has_ip_geography_mismatch): ?>
                            <li data-popover-bottom="<strong>IP Geography Mismatch</strong><br>The country  of the device that this contribution originated from does not match the billing address country. This match is based on IP address and has a very small margin of error (~5%)."><i class="fa fa-li fa-times"></i>  The IP Address <strong>(<?= e(cart_countries()[$orderModel->ip_country]) ?>)</strong> does not match the Billing Address <strong>(<?= e(cart_countries()[$orderModel->billingcountry]) ?>)</strong>.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="panel panel-basic">
            <div class="panel-body">

                <div class="bottom-gutter-sm">
                    <div class="panel-sub-title">Payment</div>
                </div>

                    <?php if(!$orderModel->is_paid): ?>

                        <div class="row pointer payment-wrap" role="button" data-target="#payments-modal" data-toggle="modal">

                            <?php if($orderModel->response_text): ?>
                                <div class="col-xs-12 stat text-danger">
                                    <div class="stat-value-sm">
                                        <strong><i class="fa fa-exclamation-triangle"></i> <?= e($orderModel->response_text) ?></strong>
                                    </div>
                                    <div class="stat-label">Declined Response &nbsp;&nbsp;<a href="https://help.givecloud.com/en/articles/1541616-failed-or-declined-payments" target="_blank" rel="noreferrer"><i class="fa fa-question-circle"></i> How do I fix this?</a></div>
                                </div>
                            <?php endif; ?>

                            <div class="col-xs-12 stat">
                                <div class="stat-value-sm"><?= e(toLocalFormat($orderModel->started_at, 'M j, Y \a\t\ g:iA')) ?></div>
                                <div class="stat-label">Started At</diV>
                            </div>

                        </div>

                    <?php else: ?>

                        <?php $pending_count = $orderModel->payments->where('status','pending')->count() ?>

                        <div class="flex p-[15px] pt-0 cursor-pointer payment-wrap <?= e(($pending_count) ? 'warning' : '') ?> bg-transparent" role="button" data-target="#payments-modal" data-toggle="modal">

                            <div class="grow min-w-[0px]">
                                <div class="stat-value font-black whitespace-nowrap text-ellipsis"><?= e(money($orderModel->subtotal, $orderModel->currency)->format('$0,0[.]00 [$$$]')) ?></div>
                                <div class="stat-label font-bold">
                                    <?= e(money($orderModel->totalamount, $orderModel->currency)->format('$0,0[.]00 [$$$]')) ?> Charged
                                </diV>
                            </div>

                            <div class="grow-0 fit-content text-right">
                                <div class="stat-label flex items-start mt-[10px] min-h-[22px]">
                                    <?php if ($orderModel->used_apple_pay || $orderModel->used_google_pay): ?>
                                        <img src="<?= e(jpanel_asset_url('images/payment/' . ($orderModel->used_apple_pay ? 'apay' : 'gpay') . '.svg')) ?>" alt="" class="h-[22px] mt-[2px] mr-1">
                                    <?php elseif ($orderModel->fa_icon === 'fa-cc-visa'): ?>
                                        <svg width='67' height='22' class="inline-block -mr-[2px] mb-[9px]" viewBox='0 0 39 14' fill='none' xmlns='http://www.w3.org/2000/svg'>
                                            <path fillRule='evenodd' clipRule='evenodd' d='M9.69057 13.1462H6.40113L3.93445 3.45895C3.81737 3.01333 3.56878 2.61939 3.20311 2.43371C2.29054 1.96711 1.28494 1.59576 0.187927 1.40847V1.03551H5.48695C6.21829 1.03551 6.7668 1.59576 6.85822 2.24642L8.13807 9.23419L11.4259 1.03551H14.6239L9.69057 13.1462ZM16.4523 13.1462H13.3457L15.9038 1.03551H19.0104L16.4523 13.1462ZM23.0296 4.3907C23.121 3.73842 23.6695 3.36546 24.3094 3.36546C25.315 3.27181 26.4104 3.4591 27.3246 3.92409L27.8731 1.3166C26.9589 0.943635 25.9533 0.756348 25.0408 0.756348C22.0256 0.756348 19.8315 2.43386 19.8315 4.76204C19.8315 6.5332 21.3856 7.46318 22.4827 8.02343C23.6695 8.58206 24.1266 8.95502 24.0352 9.51366C24.0352 10.3516 23.121 10.7246 22.2084 10.7246C21.1114 10.7246 20.0144 10.4453 19.0104 9.97865L18.4619 12.5878C19.5589 13.0527 20.7457 13.24 21.8427 13.24C25.2236 13.3321 27.3246 11.6562 27.3246 9.1407C27.3246 5.97295 23.0296 5.78728 23.0296 4.3907ZM38.1969 13.1462L35.7302 1.03551H33.0807C32.5322 1.03551 31.9837 1.40847 31.8009 1.96711L27.2332 13.1462H30.4312L31.0695 11.3767H34.9989L35.3646 13.1462H38.1969ZM33.5378 4.297L34.4504 8.86132H31.8923L33.5378 4.297Z' fill='currentColor' />
                                        </svg>
                                    <?php else: ?>
                                        <i class="-mt-[3px] mb-[7px] fa fa-2x <?= e($orderModel->fa_icon) ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="stat-label font-bold">
                                    <?php if ($orderModel->used_apple_pay || $orderModel->used_google_pay): ?>
                                        <div class="mt-[8px]">
                                            <i class="fa <?= e($orderModel->fa_icon) ?> -mt-[3px] align-top text-[25px]"></i>
                                            <?= e($orderModel->billingcardlastfour ?? '') ?>
                                        </div>
                                    <?php else: ?>
                                        <?= e($orderModel->billingcardlastfour ?? '') ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                        <?php if($pending_count): ?>
                            <div class="flex -mt-1 -mx-[15px] px-[15px] pt-[4px] pb-[2px] bg-[#c3b28f] text-[10px] font-bold text-white">
                                PAYMENT PENDING
                            </div>
                        <?php endif; ?>

                    <div x-data="{ show: false }">
                        <div class="row overflow-hidden max-h-0 transition-all ease-in-out duration-300" x-ref="row" :style="show && 'max-height:' + $refs.row.scrollHeight + 'px'">

                            <?php if ($orderModel->using_application_fee_billing): ?>
                                <div class="px-[15px] py-4 border-t border-t-gray-300">
                                    <div class="flex items-center mb-2">
                                        <div class="grow">Amount Donated</div>
                                        <div><?= e(money($orderModel->subtotal, $orderModel->currency)) ?></div>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <div class="grow">+ Optional DCC</div>
                                        <div><?= e(money($orderModel->dcc_total_amount, $orderModel->currency)) ?></div>
                                    </div>
                                    <div class="flex items-center mb-2 font-bold">
                                        <div class="grow">Total Charged (<?= e($orderModel->payment_type_formatted) ?><?= e($orderModel->billingcardlastfour ? " {$orderModel->billingcardlastfour}" : '') ?>)</div>
                                        <div class="grow-0"><td><?= e(money($orderModel->totalamount, $orderModel->currency)) ?></div>
                                    </div>
                                    <div class="flex items-center mb-2 text-[#aaa]">
                                        <div class="grow">Stripe Fees</div>
                                        <div class="grow-0">(<?= e(money($orderModel->stripe_fee_amount, $orderModel->currency_code)) ?>)</div>
                                    </div>
                                    <div class="flex items-center mb-2 text-[#aaa]">
                                        <div class="grow">Givecloud Platform Fee</div>
                                        <div class="grow-0">(<?= e(money($orderModel->latestPayment->application_fee_amount, $orderModel->currency)) ?>)</div>
                                    </div>
                                    <div class="flex items-center font-bold">
                                        <div class="grow">Net Amount</div>
                                        <div class="grow-0"><?= e(money($orderModel->net_total_amount, $orderModel->currency)) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                          <div class="py-2 clearfix border-t border-t-gray-300">
                            <?php if($orderModel->is_pos && $orderModel->payment_type == 'check'): ?>

                                <div class="col-xs-6 stat">
                                    <div class="stat-value-sm text-ellipsis"><?= e($orderModel->check_number) ?></div>
                                    <div class="stat-label">Check Number</diV>
                                </div>
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-sm"><?= e(toLocalFormat($orderModel->check_date, 'M j, Y')) ?></div>
                                    <div class="stat-label">Check Date</diV>
                                </div>

                            <?php elseif($orderModel->is_pos && $orderModel->payment_type == 'cash'): ?>

                                <div class="col-xs-6 stat">
                                    <div class="stat-value-sm"><?= e(money($orderModel->cash_received, $orderModel->currency)) ?></div>
                                    <div class="stat-label">Cash Received</diV>
                                </div>
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-sm"><?= e(money(-$orderModel->cash_change, $orderModel->currency)) ?></div>
                                    <div class="stat-label">Change Given</diV>
                                </div>

                            <?php elseif($orderModel->is_pos && $orderModel->payment_type == 'other'): ?>

                                <div class="col-xs-6 stat">
                                    <div class="stat-value-sm text-ellipsis"><?= e($orderModel->payment_other_reference) ?></div>
                                    <div class="stat-label">Reference Number</diV>
                                </div>
                                <?php if($orderModel->payment_other_note): ?>
                                    <div class="col-xs-6 stat">
                                        <div class="stat-value-sm text-ellipsis" title="<?= e($orderModel->payment_other_note) ?>"><?= e($orderModel->payment_other_note) ?></div>
                                        <div class="stat-label">Payment Notes</diV>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($orderModel->paymentProvider): ?>

                                <?php if ($orderModel->confirmationnumber): ?>
                                    <div class="col-xs-6 stat">
                                        <div class="stat-value-xs text-ellipsis" title="<?= e($orderModel->confirmationnumber) ?>">
                                        <?php if (in_array($orderModel->paymentProvider->provider, ['nmi','safesave'])): ?>
                                            <a href="https://secure.nmi.com/merchants/reports.php?Action=Details&transaction_type=ck&report_id=0&transaction=<?= e($orderModel->confirmationnumber) ?>" target="_blank"><?= e($orderModel->confirmationnumber) ?></a>
                                        <?php elseif ($orderModel->paymentProvider->provider === 'paypalexpress'): ?>
                                            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=<?= e($orderModel->confirmationnumber) ?>" target="_blank"><?= e($orderModel->confirmationnumber) ?></a>
                                        <?php elseif ($orderModel->paymentProvider->provider === 'stripe'): ?>
                                            <a href="https://dashboard.stripe.com/payments/<?= e($orderModel->confirmationnumber) ?>" target="_blank"><?= e($orderModel->confirmationnumber) ?></a>
                                        <?php elseif ($orderModel->paymentProvider->provider === 'braintree'): ?>
                                            <a href="<?= e(sprintf(
                                                'https://%s/merchants/%s/transactions/%s',
                                                $orderModel->paymentProvider->test_mode ? 'sandbox.braintreegateway.com' : 'braintreegateway.com',
                                                $orderModel->paymentProvider->config('merchant_id'),
                                                $orderModel->confirmationnumber
                                            )); ?>" target="_blank"><?= e($orderModel->confirmationnumber) ?></a>

                                        <?php else: ?>
                                            <?= e($orderModel->confirmationnumber) ?>
                                        <?php endif ?>
                                        </div>
                                        <div class="stat-label">Gateway Auth</diV>
                                    </div>
                                <?php endif; ?>

                                <?php if($orderModel->vault_id != null): ?>
                                    <div class="col-xs-6 stat">
                                        <div class="stat-value-xs text-ellipsis">
                                            <?= e($orderModel->vault_id ? $orderModel->vault_id : 'Not Available') ?>
                                        </div>
                                        <div class="stat-label">Gateway Vault</diV>
                                    </div>
                                <?php endif; ?>

                                <div class="clearfix"></div>

                                <div class="col-xs-6 stat">
                                    <div class="stat-value-xs"><?= e(toLocalFormat($orderModel->started_at) . ' at ' . toLocalFormat($orderModel->started_at, 'g:iA')) ?></div>
                                    <div class="stat-label">Started At</diV>
                                </div>

                                <div class="col-xs-6 stat">
                                    <div class="stat-value-xs"><?= e(toLocalFormat($orderModel->createddatetime) . ' at ' . toLocalFormat($orderModel->createddatetime, 'g:iA')) ?></div>
                                    <div class="stat-label">Completed</diV>
                                </div>

                            <?php endif; ?>

                            <?php $failed_count = $orderModel->payments->where('status','failed')->count() ?>
                            <?php if($failed_count): ?>
                                <a class="col-xs-6 stat focus:no-underline hover:no-underline" href="#payments-modal" data-toggle="modal">
                                    <div class="inline-block text-danger stat-value-xs"><i class="fa fa-fw fa-exclamation-triangle"></i> <?= e($failed_count) ?></div>
                                    <div class="inline-block text-danger stat-label">Failed Attempts</diV>
                                </a>
                            <?php endif; ?>

                          </div>
                        </div>
                        <div class="row -mb-[15px] p-1 bg-gray-100 text-center cursor-pointer" @click="show = !show">
                            <div class="inline-block transition-all duration-300 ease-in-out transform" :class="{ 'rotate-180': show }">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>

            </div>
        </div>

        <div class="panel panel-basic">
            <div class="panel-body">
                <div class="bottom-gutter-sm">
                    <div class="panel-sub-title">Tracking</div>
                </div>

                <div class="row">

                <?php if (sys_get('referral_sources_isactive')): ?>
                    <?php if ($orderModel->referral_source): ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis"><?= e($orderModel->referral_source) ?></div>
                            <div class="stat-label">"How Did You Hear About Us"</diV>
                        </div>
                    <?php else: ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-muted">N/A</div>
                            <div class="stat-label">Referral Source</diV>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                    <?php if ($orderModel->http_referer): ?>
                        <?php
                            $http_referer_domain = parse_url($orderModel->http_referer)['host'];
                            $http_referer_domain = str_replace('www.', '', $http_referer_domain);
                        ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis" title="<?= e($orderModel->http_referer) ?>">
                                <a target="_blank" href="<?= e($orderModel->http_referer) ?>"><i class="fa <?= e(fa_social_icon($http_referer_domain)) ?>"></i> <?= e($http_referer_domain) ?></a>
                            </div>
                            <div class="stat-label">Referring Website</diV>
                        </div>
                    <?php else: ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-muted">Direct</div>
                            <div class="stat-label">Referring Website</diV>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>

                    <?php if(!$orderModel->is_pos && $orderModel->client_browser): ?>
                        <?php $ua = $orderModel->ua(); ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis">
                                <?php if (fa_ua_icon($ua->os->family)): ?><i class="fa <?= e(fa_ua_icon($ua->os->family)) ?>"></i><?php endif; ?>
                                <?= e($ua->os->family) ?> <small class="text-muted"><?= e($ua->os->toVersion()) ?></small>
                            </div>
                            <div class="stat-label">Operating System</diV>
                        </div>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis">
                                <?php if (fa_ua_icon($ua->ua->family)): ?><i class="fa <?= e(fa_ua_icon($ua->ua->family)) ?>"></i><?php endif; ?>
                                <?= e($ua->ua->family) ?> <small class="text-muted"><?= e($ua->ua->toVersion()) ?></small>
                            </div>
                            <div class="stat-label">Browser</diV>
                        </div>
                    <?php else: ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-muted">N/A</div>
                            <div class="stat-label">Operating System</diV>
                        </div>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-muted">N/A</div>
                            <div class="stat-label">Browser</diV>
                        </div>
                    <?php endif; ?>

                    <?php if($orderModel->client_ip): ?>
                        <?php $order_count = \Ds\Models\Order::whereNotNull('confirmationdatetime')->where('client_ip', $orderModel->client_ip)->count(); ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis" data-popover-bottom="<strong>IP Address (<?= e($orderModel->client_ip) ?>)</strong><br>This is the internet location from which this contribution was placed. Click to view other orders from the same IP address.<br><br>The flag indicates the country in which the IP is located to a 90% degree of accuracy.">
                                <?php if($orderModel->ip_country): ?><img src="<?= e(flag($orderModel->ip_country)) ?>" style="margin-right:3px; width:16px; height:16px; vertical-align:middle;"> <?php endif; ?><a href="<?= e(route('backend.orders.index', ['fO' => $orderModel->client_ip])) ?>"><?= e($orderModel->client_ip) ?></a> <?php if($order_count > 1): ?><span class="badge"><?= e($order_count) ?></span><?php endif; ?>

                            </div>
                            <?php if($orderModel->has_ip_geography_mismatch): ?><div class="label label-warning" data-popover-bottom="<strong>IP May Not Match Billing Address (<?= e($orderModel->ip_country) ?>)</strong><br>The country associated with the IP address may not match the billing address."><i class="fa fa-fw fa-exclamation-triangle"></i> May Not Match Billing (<?= e($orderModel->billingcountry) ?>)</div><?php endif; ?>
                            <div class="stat-label">IP Address</diV>
                        </div>
                    <?php endif; ?>

                    <?php if($orderModel->tracking_source): ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis">
                                <?= e($orderModel->tracking_source) ?: '<span class="text-muted">N/A</span>' ?>
                            </div>
                            <div class="stat-label">Source</diV>
                        </div>
                    <?php endif; ?>

                    <?php if($orderModel->tracking_medium): ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis">
                                <?= e($orderModel->tracking_medium) ?: '<span class="text-muted">N/A</span>' ?>
                            </div>
                            <div class="stat-label">Medium</diV>
                        </div>
                    <?php endif; ?>

                    <?php if($orderModel->tracking_campaign): ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis">
                                <?= e($orderModel->tracking_campaign) ?: '<span class="text-muted">N/A</span>' ?>
                            </div>
                            <div class="stat-label">Campaign</diV>
                        </div>
                    <?php endif; ?>

                    <?php if($orderModel->tracking_term): ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis">
                                <?= e($orderModel->tracking_term) ?: '<span class="text-muted">N/A</span>' ?>
                            </div>
                            <div class="stat-label">Term</diV>
                        </div>
                    <?php endif; ?>

                    <?php if($orderModel->tracking_content): ?>
                        <div class="col-xs-6 stat">
                            <div class="stat-value-xs text-ellipsis">
                                <?= e($orderModel->tracking_content) ?: '<span class="text-muted">N/A</span>' ?>
                            </div>
                            <div class="stat-label">Content</diV>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <?php if (sys_get('tax_receipt_pdfs') && user()->can('taxreceipt.view')): ?>
            <div class="panel panel-basic">
                <div class="panel-body">

                    <div class="bottom-gutter-sm">
                        <div class="panel-sub-title">Tax Receipt</div>
                    </div>

                    <div class="row">
                        <?php if($orderModel->taxReceipt): ?>
                            <div class="col-sm-8 stat">
                                <div class="stat-value-sm"><a href="/jpanel/tax_receipt/<?= e($orderModel->taxReceipt->id) ?>/pdf" target="_blank"><?= e(($orderModel->taxReceipt) ? $orderModel->taxReceipt->number : 'N/A') ?></a></div>
                                <div class="stat-label">Tax Receipt</diV>
                            </div>
                            <div class="col-sm-4 stat">
                                <div class="stat-value-sm"><?= e(money($orderModel->taxReceipt->amount, $orderModel->taxReceipt->currency_code)) ?></div>
                                <div class="stat-label">Amount</diV>
                            </div>
                        <?php elseif(!$orderModel->receiptable_amount): ?>
                            <div class="col-sm-12 stat text-muted">
                                <div class="stat-value-sm"><i class="fa fa-exclamation-circle"></i> No Receiptable Amount</div>
                            </div>
                        <?php elseif(!$orderModel->is_view_only && user()->can('taxreceipt.edit') && !$orderModel->is_refunded): ?>
                            <div class="col-sm-12 stat">
                                <a href="<?= e(route('backend.orders.generate_tax_receipt', $orderModel)) ?>" class="btn btn-sm btn-info"><i class="fa fa-fw fa-refresh"></i> Generate Tax Receipt</a>
                            </div>
                        <?php else: ?>
                            <div class="col-sm-12 stat text-muted">
                                <div class="stat-value-sm"><i class="fa fa-exclamation-circle"></i> No Tax Receipt Issued</div>
                                <div class="stat-label">Tax Receipt</diV>
                            </div>
                        <?php endif; ?>


                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if(dpo_is_enabled() && $orderModel->is_processed == 1): ?>
            <div class="panel panel-basic">
            <div class="panel-body">
                <div class="bottom-gutter-sm">
                    <?php if(user()->can('admin.dpo') && !$orderModel->is_view_only): ?>
                        <div class="btn-group pull-right">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-gear"></i>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li><a href="#" data-toggle="modal" data-target="#update-dp-data"><i class="fa fa-fw fa-pencil"></i> Edit Gift/Donor Data</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#" data-toggle="modal" data-target="#sync-to-dp-modal"><i class="fa fa-exchange fa-fw"></i> <?= e((trim($orderModel->alt_contact_id) === '') ? 'Sync' : 'Re-Sync') ?> to DPO</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="panel-sub-title">DonorPerfect</div>
                </div>
                <div class="row">
                    <?php if(!$orderModel->dp_sync_order): ?>
                        <div class="col-sm-12 stat text-muted">
                            <div class="stat-value-sm"><i class="fa fa-exclamation-circle"></i> Disabled for this contribution</div>
                        </div>
                    <?php elseif($orderModel->is_unsynced): ?>
                        <div class="col-sm-12 stat text-center text-danger">
                            <div class="stat-value-sm">
                                <i class="fa fa-exclamation-triangle"></i> Not Synced

                                <?php if($orderModel->dpo_status_message): ?>
                                    <p class="text-sm"><?= e($orderModel->dpo_status_message) ?></p>
                                <?php endif; ?>

                                <?php if(!$orderModel->is_view_only && user()->can('admin.dpo')): ?>
                                    <div style="margin-top:10px;"><a href="#" data-toggle="modal" data-target="#sync-to-dp-modal" class="btn btn-pill btn-outline btn-danger"><i class="fa fa-refresh"></i> Sync Now</a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-sm-8 stat">
                            <?php if($orderModel->alt_transaction_id): ?>
                                <?php
                                    $ids = collect(explode(',',$orderModel->alt_transaction_id));
                                    $orderModel->items->each(function($itm)use(&$ids){ if ($itm->alt_transaction_id) { $ids->push($itm->alt_transaction_id); } });
                                ?>
                                <div class="stat-value-xs">
                                    <?php foreach($ids->unique() as $i => $id): ?>
                                        <a href="#" class="btn btn-xs btn-info btn-outline dp-gift" style="margin:0px 3px 3px 0px;" data-gift="<?= e($id) ?>"><?= e($id) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="stat-value-xs text-muted"><i class="fa fa-exclamation-circle"></i> No Gifts</div>
                            <?php endif; ?>
                            <div class="stat-label">Gift IDs</diV>
                        </div>
                        <div class="col-sm-4 stat">
                            <div class="stat-value-xs"><a href="#" class="btn btn-xs btn-info btn-outline dp-donor" style="margin:0px 3px 3px 0px;" data-donor="<?= e($orderModel->alt_contact_id) ?>"><?= e($orderModel->alt_contact_id) ?></a></div>
                            <div class="stat-label">Donor ID</diV>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        <?php endif; ?>

        <?php if(sys_get('salesforce_enabled') && $orderModel->is_processed == 1): ?>
            <div class="panel panel-basic">
                <div class="panel-body">
                    <div class="panel-sub-title">Salesforce</div>
                    <div class="row">
                        <div class="col-sm-12">
                            <a
                                href="<?= e(sprintf('%s/lightning/r/%s/%s/view',
                                    app('forrest')->getInstanceURL(),
                                    app(\Ds\Domain\Salesforce\Models\Contribution::class)->getTable(),
                                    $salesforceReference->reference
                                )) ?>"
                                target="_blank">
                                View contribution in Salesforce
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if(sys_get('double_the_donation_enabled') && $orderModel->doublethedonation_registered): ?>
            <div class="panel panel-basic">
                <div class="panel-body">
                    <div class="panel-sub-title">Double the Donation</div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="flex justify-center my-4 text-gray-400" data-dtd="fetching">
                                <div class="text-xl"><i class="fa fa-spinner-third animate-spin mr-2"></i> Fetching...</div>
                            </div>
                            <div class="flex justify-center my-4 text-gray-400 hide" data-dtd="no-match">
                                <div class="text-xl"><i class="fa fa-circle-xmark mr-2"></i> No Match</div>
                            </div>
                            <div class="hide" data-dtd="loaded">
                                <p class="text-lg text-gray-800" data-dtd="company-name">N/D</p>
                                <p class="text-xs text-gray-400">Employer</p>

                                <p class="mt-4 text-gray-800" data-dtd="status">N/D</p>
                                <p class="text-xs text-gray-400">Status</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <div class="col-lg-3">
        <?php include('_account-bar.html.php'); ?>
    </div>

</div>







<?php if(!$orderModel->trashed() && $orderModel->userCan(['edit','fullfill'])): ?>
<div class="modal fade modal-info" id="edit-order-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Contribution</h4>
            </div>
            <form name="order" id="OrderForm" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="id" value="<?= e($orderModel->id) ?>" />

                <div class="modal-body">

                    <ul class="nav nav-tabs <?= e((feature('shipping') || feature('account_notes')) ? '' : 'hide') ?>" role="tablist">
                        <li role="presentation" class="active"><a href="#pos-bill-address-tab" aria-controls="pos-bill-address-tab" role="tab" data-toggle="tab"><i class="fa fa-envelope"></i> Billing Address</a></li>
                        <?php if (feature('shipping')): ?><li role="presentation"><a href="#pos-ship-address-tab" aria-controls="pos-ship-address-tab" role="tab" data-toggle="tab"><i class="fa fa-truck"></i> Shipping Address</a></li><?php endif; ?>
                        <?php if (feature('account_notes')): ?><li role="presentation"><a href="#pos-comments-tab" aria-controls="pos-comments-tab" role="tab" data-toggle="tab"><i class="fa fa-comments"></i> Notes</a></li><?php endif; ?>
                    </ul>

                    <div class="tab-content">
                        <br>

                        <div role="tabpanel" class="tab-pane active" id="pos-bill-address-tab">

                            <div class="row row-padding-sm">

                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12 <?= e(feature('givecloud_pro') ? '' : 'hide') ?>">
                                    <label>Supporter Type</label>
                                    <select name="account_type_id" id="accounttypeid" class="form-control" <?= e((!$orderModel->userCan('edit'))?'disabled':'') ?>>
                                        <?php foreach ($account_types as $type): ?>
                                                <option value="<?= e($type->id) ?>" data-organization="<?= e($type->is_organization) ?>" <?= dangerouslyUseHTML($type->id == $orderModel->account_type_id ? ' selected="selected"' : '') ?>><?= e($type->name) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="row"></div>

                                <?php if (sys_get('donor_title') != "hidden"): ?>
                                    <div class="form-group col-xs-3">
                                        <label>Title</label>
                                        <?php if (sys_get('donor_title_options') == ""): ?>
                                            <input type="text" name="billing_title" id="billingtitle" class="form-control" value="<?= e($orderModel->billing_title); ?>">
                                        <?php else: ?>
                                            <select name="billing_title" id="billingtitle" class="form-control">
                                                <option value="">Mr/Mrs</option>
                                                <?php foreach (explode(",",sys_get('donor_title_options')) as $option): ?>
                                                    <option value="<?= e($option) ?>" <?= e($option == $orderModel->billing_title ? ' selected="selected"' : '') ?>><?= e($option) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        <?php endif ?>
                                    </div>
                                <?php endif ?>

                                <div class="form-group <?= e((sys_get('donor_title') != "hidden") ? "col-xs-4" : "col-xs-7") ?>">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" value="<?= e($orderModel->billing_first_name); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>

                                <div class="form-group col-xs-5">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" name="billing_last_name" id="billing_last_name" value="<?= e($orderModel->billing_last_name); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>

                                <?php if (feature('givecloud_pro')): ?>
                                <div class="form-group organization-name col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label>Organization Name</label>
                                    <input type="text" class="form-control" name="billing_organization_name" value="<?= e($orderModel->billing_organization_name); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <?php endif; ?>

                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingemail">Email:</label>
                                    <input type="text" class="form-control" name="billingemail" id="billingemail" value="<?= e($orderModel->billingemail); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingaddress1">Address:</label>
                                    <input type="text" class="form-control" name="billingaddress1" id="billingaddress1" value="<?= e($orderModel->billingaddress1); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingaddress2">Address 2:</label>
                                    <input type="text" class="form-control" name="billingaddress2" id="billingaddress2" value="<?= e($orderModel->billingaddress2); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="billingcity">City:</label>
                                    <input type="text" class="form-control" name="billingcity" id="billingcity" value="<?= e($orderModel->billingcity); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                    <label for="billingstate"><?= e($billingSubdivisions['subdivision_type']); ?>:</label>
                                    <select type="text" class="form-control" name="billingstate" id="billingstate" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?>>
                                        <option value="" class="text-placeholder">Select <?= e($billingSubdivisions['subdivision_type']); ?></option>
                                        <?php foreach($billingSubdivisions['subdivisions'] as $stateCode => $stateName): ?>
                                            <option <?= e(volt_selected($orderModel->billingstate, $stateCode)) ?> value="<?= e($stateCode) ?>">
                                                <?= e($stateName) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                    <label for="billingzip">Zip/Postal Code:</label>
                                    <input type="text" class="form-control" name="billingzip" id="billingzip" value="<?= e($orderModel->billingzip); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="billingcountry">Country:</label>
                                    <select class="form-control" name="billingcountry" id="billingcountry" data-country-state="billingstate" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?>>
                                        <option value="" class="text-placeholder">Select Country</option>
                                        <?php foreach($countries as $countryCode => $countryName): ?>
                                            <option <?= e(volt_selected($orderModel->billingcountry, $countryCode)) ?> value="<?= e($countryCode) ?>">
                                                <?= e($countryName) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="billingphone">Phone:</label>
                                    <input type="text" class="form-control" name="billingphone" id="billingphone" value="<?= e($orderModel->billingphone); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>

                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="pos-ship-address-tab">

                            <div class="row row-padding-sm">

                                <?php if (sys_get('donor_title') != "hidden"): ?>
                                    <div class="form-group col-xs-3">
                                        <label>Title</label>
                                        <?php if (sys_get('donor_title_options') == ""): ?>
                                            <input type="text" name="shipping_title" id="shippingtitle" class="form-control" value="<?= e($orderModel->shipping_title); ?>">
                                        <?php else: ?>
                                            <select name="shipping_title" id="shippingtitle" class="form-control">
                                                <option value="">Mr/Mrs</option>
                                                <?php foreach (explode(",",sys_get('donor_title_options')) as $option): ?>
                                                    <option value="<?= e($option) ?>" <?= e($option == $orderModel->shipping_title ? ' selected="selected"' : '') ?>><?= e($option) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        <?php endif ?>
                                    </div>
                                <?php endif ?>

                                <div class="form-group <?= e((sys_get('donor_title') != "hidden") ? "col-xs-4" : "col-xs-7") ?>">
                                    <label for="shipping_first_name">First Name:</label>
                                    <input type="text" class="form-control" name="shipping_first_name" id="shipping_first_name" value="<?= e($orderModel->shipping_first_name); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-xs-5">
                                    <label for="shipping_last_name">Last Name:</label>
                                    <input type="text" class="form-control" name="shipping_last_name" id="shipping_last_name" value="<?= e($orderModel->shipping_last_name); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group organization-name col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label>Organization Name</label>
                                    <input type="text" class="form-control" name="shipping_organization_name" value="<?= e($orderModel->shipping_organization_name); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipemail">Email:</label>
                                    <input type="text" class="form-control" name="shipemail" id="shipemail" value="<?= e($orderModel->shipemail); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipaddress1">Address:</label>
                                    <input type="text" class="form-control" name="shipaddress1" id="shipaddress1" value="<?= e($orderModel->shipaddress1); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipaddress2">Address 2:</label>
                                    <input type="text" class="form-control" name="shipaddress2" id="shipaddress2" value="<?= e($orderModel->shipaddress2); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <label for="shipcity">City:</label>
                                    <input type="text" class="form-control" name="shipcity" id="shipcity" value="<?= e($orderModel->shipcity); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                    <label for="shipstate"><?= e($shippingSubdivisions['subdivision_type']); ?>:</label>
                                    <select type="text" class="form-control" name="shipstate" id="shipstate" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?>>
                                        <option value="" class="text-placeholder">Select <?= e($shippingSubdivisions['subdivision_type']); ?></option>
                                        <?php foreach($shippingSubdivisions['subdivisions'] as $stateCode => $stateName): ?>
                                            <option <?= e(volt_selected($orderModel->shipstate, $stateCode)) ?> value="<?= e($stateCode) ?>">
                                                <?= e($stateName) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                    <label for="shipzip">Zip/Postal Code:</label>
                                    <input type="text" class="form-control" name="shipzip" id="shipzip" value="<?= e($orderModel->shipzip); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="shipcountry">Country:</label>
                                    <select class="form-control" name="shipcountry" id="shipcountry" data-country-state="shipstate" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?>>
                                        <option value="" class="text-placeholder">Select Country</option>
                                        <?php foreach($countries as $countryCode => $countryName): ?>
                                            <option <?= e(volt_selected($countryCode, $orderModel->shipcountry)) ?> value="<?= e($countryCode) ?>">
                                                <?= e($countryName) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <label for="shipphone">Phone:</label>
                                    <input type="text" class="form-control" name="shipphone" id="shipphone" value="<?= e($orderModel->shipphone); ?>" <?= e((!$orderModel->userCan('edit'))?'readonly':'') ?> />
                                </div>
                            </div>
                        </div>


                        <div role="tabpanel" class="tab-pane" id="pos-comments-tab">
                            <div class="form-group">
                                <label for="shipaddress1">Special Notes:</label>
                                <p>These are the notes that were left by the customer.</p>
                                <textarea class="form-control" name="comments" style="height:80px;"><?= e($orderModel->comments) ?></textarea>
                                <div class="checkbox">
                                    <label for="inputIsAnonymous">
                                        <input id="inputIsAnonymous" type="checkbox" name="is_anonymous" value="1" <?= e($orderModel->is_anonymous ? 'checked' : '') ?>>
                                        Keep me anonymous
                                    </label>
                                </div>
                            </div>

                            <div class="form-group mt-6">
                                <label for="shipaddress1">Note to Customer:</label>
                                <p>This message will appear on the customer's receipt.</p>
                                <textarea class="form-control simple-html" name="customer_notes" style="height:80px;"><?= e($orderModel->customer_notes) ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">Update</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif ?>

<div class="modal fade modal-danger" id="refund-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-reply"></i> Refund</h4>
            </div>
            <form method="post" action="<?= e(route('backend.orders.refund', $orderModel)) ?>">
                <?= dangerouslyUseHTML(csrf_field()) ?>

                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fa fa-fw fa-exclamation-triangle"></i> <strong>This is a permanent action and cannot be undone.</strong> You can only issue a refund once.
                    </div>

                    <div class="form-group">
                        <div class="radio">
                            <label>
                                <input type="radio" name="refund_type" value="full" checked> <strong>Full Refund</strong> (<?= e(money($orderModel->totalamount - $orderModel->refunded_amt, $orderModel->currency)) ?>)
                                <?php if(dpo_is_enabled() && sys_get('dp_push_order_refunds')): ?><br><small>This will also adjust gifts in DonorPerfect.</small><?php endif; ?>
                            </label>
                        </div>
                    </div>

                    <?php if (!$orderModel->paymentProvider || ($orderModel->paymentProvider && $orderModel->paymentProvider->supports('partial_refunds'))): ?>
                        <div class="form-group">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="refund_type" value="custom" <?= e(feature('givecloud_pro') ? '' : 'disabled') ?>> <strong class="<?= e(feature('givecloud_pro') ? '' : 'text-muted') ?>">Partial refund</strong>
                                    <?php if (feature('givecloud_pro')): ?>
                                        (custom amount)
                                    <?php else: ?>
                                        <a class="upgrade-pill" href="https://calendly.com/givecloud-sales/givecloud-upgrade-call?month=<?= e(now()->format('Y-d')) ?>">UPGRADE</a>
                                    <?php endif; ?>
                                    <?php if(dpo_is_enabled() && sys_get('dp_push_order_refunds')): ?>
                                        <br><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> No adjustments will be made in DonorPerfect.</small>
                                    <?php endif; ?>
                                </label>
                            </div>

                            <div id="custom-refund-amount" class="form-group" style="display:none; margin-left:20px; width:140px;">
                                <div class="input-group">
                                    <div class="input-group-addon"><?= e($orderModel->currency->symbol) ?></div>
                                    <input type="tel" class="form-control text-right" name="amount" placeholder="0.00" <?php if ($orderModel->paymentProvider && !$orderModel->paymentProvider->supports('partial_refunds')) echo "readonly" ?>>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Refund Now</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-primary" id="linkAccount">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user"></i> Link a Supporter</h4>
            </div>
            <form class="form-horizontal" method="get" action="<?= e(route('backend.orders.link_member', $orderModel)) ?>">
                <div class="modal-body">
                    <p>Choose the supporter you want this contribution to become linked to.</p>

                    <p class="text-muted"><i class="fa fa-exclamation-circle"></i> Note: This will not change any information on the contribution. It will simply link the contribution to a supporter.</p>

                    <div class="form-group">
                        <label for="linkAccount-member_id" class="col-sm-2 control-label">Supporter</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control ds-members" id="linkAccount-member_id" name="member_id" placeholder="Search for a supporter...">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Link Supporter</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-info" id="update-dp-data" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Change Donor/Gift Data</h4>
            </div>
            <form class="form-horizontal" method="post" action="<?= e(route('backend.orders.editDPData', $orderModel)) ?>">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <div class="modal-body">

                    <div class="form-group">
                        <label class="col-md-3 control-label">Sync Contribution</label>
                        <div class="col-md-6">
                            <input type="checkbox" class="switch" value="1" name="dp_sync_order" <?= e((($orderModel->dp_sync_order) == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('.dp-fields-wrapper').removeClass('hide'); else $('.dp-fields-wrapper').addClass('hide');">
                            <br><small class="text-muted">Turn ON to enable syncing this contribution with DonorPerfect.</small>
                        </div>
                    </div>

                    <div class="dp-fields-wrapper <?= e((($orderModel->dp_sync_order) == 0) ? 'hide' : '') ?>">
                        <hr>
                        <div class="form-group">
                            <label for="name" class="col-md-3 control-label">Donor ID</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="donor_id" value="<?= e($orderModel->alt_contact_id) ?>" maxlength="11" />
                                <small class="text-muted">This is for reference purposes only. Changing this value will not impact your sync to DonorPerfect.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name" class="col-md-3 control-label">Gift IDs</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control selectize-tags" name="gift_ids" value="<?= e($orderModel->alt_transaction_id) ?>" />
                                <small class="text-muted">This is for reference purposes only. Changing this value will not impact your sync to DonorPerfect.</small>
                            </div>
                        </div>

                        <?php if($orderModel->alt_data_updated_by): ?>
                            <small class="text-muted">Last updated by <?= e($orderModel->altDataUpdatedBy->full_name) ?> on <?= e(toLocalFormat($orderModel->alt_data_updated_at, 'M j, Y')) ?>.</small>
                        <?php endif; ?>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-danger" id="sync-to-dp-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-exchange fa-fw"></i> <?= e((trim($orderModel->alt_contact_id) === '') ? 'Sync' : 'Re-Sync') ?> With DP</h4>
            </div>

            <form method="get" action="<?= e(route('backend.orders.push_to_dpo')) ?>">
                <input type="hidden" name="i" value="<?= e($orderModel->id) ?>">

                <div class="modal-body">

                    <!-- first sync -->
                    <?php if(!$orderModel->alt_contact_id): ?>

                        Are you sure you want to sync this contribution to DonorPerfect?

                    <!-- re-sync -->
                    <?php elseif($hasTributes): ?>

                        <span class="text-danger"><i class="fa fa-exclamation-circle"></i>
                            Note: Re-syncing may result in tributes being duplicated in DonorPerfect.</span>
                        <br><br>
                        Are you sure you want to re-sync this contribution to DonorPerfect?

                    <?php else: ?>
                        Are you sure you want to re-sync this contribution to DonorPerfect?
                    <?php endif; ?>

                    <hr>
                    <div class="form-group">
                        <label>Match to Donor ID: <small>(Optional)</small></label>
                        <input type="tel" class="form-control" name="donor_id" value="" maxlength="11" placeholder="DP Donor ID" />
                        <small class="text-muted">If you want Givecloud to use a specific donor, you can specify it here. <span class="text-info">Leave this blank to let Givecloud match the donor for you.</span></small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-exchange"></i> <?= e(($orderModel->alt_contact_id)?'Resync':'Sync') ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-info" id="update-item-fields" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Item Data</h4>
            </div>
            <form method="post" action="<?= e(route('backend.orders.editItemFields', $orderModel)) ?>">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <div class="modal-body" id="update-item-fields-container"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Save Changes</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-info" id="update-item" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Change Product</h4>
            </div>
            <form method="post" action="<?= e(route('backend.orders.editItem', $orderModel)) ?>">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="item_id" value="">
                <div class="modal-body">

                    <div class="alert alert-warning">
                        This function was designed to replace an item with an item with the same price. The price CANNOT be adjusted or refunded at the item level. If you need the price to change, you'll need to refund the whole contribution and have it reprocessed.
                    </div>

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>New Product</label>
                                <input class="form-control ds-variants" name="new_variant_id">
                            </div>
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Change Product</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-info" id="update-gift-aid_eligibility" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Update Gift Aid Eligibility</h4>
            </div>
            <form method="post" action="<?= e(route('backend.orders.editGiftAidEligibility', $orderModel)) ?>">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="item_id" value="">
                <div class="modal-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Gift Aid Eligibility for this Item:</label>
                                <select class="form-control" name="gift_aid_eligible">
                                    <option value="0">Ineligible</option>
                                    <option value="1">Eligible</option>
                                </select>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Update Eligibility</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-danger" id="delete-order-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-trash"></i> Delete <?= e($orderModel->is_test ? 'Test ' : '') ?>Contribution</h4>
            </div>

            <?php if($orderModel->is_trashable): ?>
                <form method="post" action="<?= e(route('backend.orders.destroy', $orderModel)) ?>">
                    <?= dangerouslyUseHTML(csrf_field()) ?>
                    <div class="modal-body">

                        <div class="alert alert-danger">
                            <i class="fa fa-fw fa-exclamation-triangle"></i> <strong>This is a permanent action and cannot be undone.</strong> Only delete contributions created in error. It's always better to void or refund a contribution.
                        </div>

                        <p>Are you sure you want to delete <strong><?= e(($orderModel->is_test) ? 'Test ' : '') ?>Contribution #<?= e($orderModel->invoicenumber) ?></strong> for <strong><?= e(money($orderModel->totalamount, $orderModel->currency)) ?></strong>.</p>

                        <?php
                            $delete_warnings = [];
                            if ($orderModel->taxReceipt) {
                                $delete_warnings[] = "Tax Receipt <strong>{$orderModel->taxReceipt->number}</strong> will be deleted.";
                            }
                            if ($tribute_count = $orderModel->items->reject(function($i){ return !$i->tribute; })->count()) {
                                $delete_warnings[] = "<strong>({$tribute_count})</strong> Tributes will be deleted.";
                            }
                            if ($rpp_count = $orderModel->items->reject(function($i){ return !$i->recurringPaymentProfile; })->count()) {
                                $delete_warnings[] = "<strong>({$rpp_count})</strong> Recurring Payment Profiles will be deleted.";
                            }
                            if ($group_count = $orderModel->items->reject(function($i){ return $i->groupAccount; })->count()) {
                                $delete_warnings[] = "<strong>({$group_count})</strong> Groups/Memberships assignments will be deleted.";
                            }
                            if ($orderModel->billingemail) {
                                $delete_warnings[] = sprintf('Any emails sent to <strong>%s</strong> will now contain broken links.', e($orderModel->billingemail));
                            }
                            if ($orderModel->member) {
                                $delete_warnings[] = sprintf("The supporter <strong>'%s' will NOT be deleted</strong>. You'll need to delete this manually afterwards.", e($orderModel->member->display_name));
                            }
                            if ($orderModel->payments->reject(function($p){ return in_array($p->type, ['cash','cheque','unknown']); })->count() > 0) {
                                $delete_warnings[] = "Any test transactions created in the payment gateway <strong>cannot and will NOT be deleted</strong>.";
                            }
                        ?>

                        <?php if (count($delete_warnings) > 0): ?>
                            <ul class="fa-ul">
                                <?php foreach($delete_warnings as $warn): ?>
                                    <li><i class="fa fa-li fa-exclamation-triangle"></i> <?= dangerouslyUseHTML($warn) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Permanently Delete</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="modal-body">
                    <p>This contribution cannot be deleted.</p>

                    <ul class="fa-ul">
                        <?php foreach($orderModel->trashable_messages as $msg): ?>
                            <li><i class="fa fa-li fa-fw fa-times"></i> <?= e($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (user()->can_live_chat): ?>
                        <p>If you believe you should still be able to delete this contribution, please <a href="javascript:Intercom('showNewMessage');">chat with support</a>.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade modal-info" id="payments-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Payments</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table" style="margin-bottom:10px;">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Source</th>
                                <th>Message</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orderModel->payments as $payment): ?>

                                <?php
                                    $row_class = '';
                                    if ($payment->status === 'succeeded') {
                                        $row_class = 'text-bold';
                                    } elseif ($payment->status === 'failed') {
                                        $row_class = 'text-danger';
                                    }
                                ?>

                                <tr class="<?= e($row_class) ?>">
                                    <td class="whitespace-nowrap"><?= e(toLocalFormat($payment->created_at, 'g:i:s a')) ?></td>
                                    <td class="whitespace-nowrap"><?= e($payment->source_description) ?></td>
                                    <td>
                                        <?php if($payment->status === 'succeeded'): ?>
                                            Approved
                                        <?php elseif($payment->status === 'pending'): ?>
                                            Pending
                                        <?php else: ?>
                                            <?php if($payment->failure_message): ?>
                                                <div class="leading-tight"><?= e($payment->failure_message) ?></div>
                                            <?php else: ?>
                                                Failed
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="whitespace-nowrap"><?= e(implode(', ', $payment->verification_messages)) ?></td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-info" id="taxes-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 style="margin-top:10px; margin-bottom:20px; margin-left:5px; color:#666; font-weight:light; ">
                    Taxes
                    <?php if ($orderModel->is_pos): ?>
                        <small class="text-sm text-muted">(<?= e($orderModel->taxable_address) ?>)</small>
                    <?php endif ?>
                </h3>
                <div class="table-responsive">
                    <table class="table" style="margin-bottom:10px;">
                        <thead>
                            <tr>
                                <th width="80">Code</th>
                                <th>Products</th>
                                <th width="80" style="text-align:right;">Cost ($)</th>
                                <th width="80" style="text-align:right;">Rate (%)</th>
                                <th width="80" style="text-align:right;">Tax (<?= e($orderModel->currency->unique_symbol) ?>)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // taxes
                                $qT = db_query(sprintf("SELECT p.id AS productid, iv.id AS productinventoryid, it.taxid, it.amount AS taxamount, t.code, p.code AS productsku, p.name AS productname, (i.price*i.qty) AS amount, t.rate
                                        FROM productorderitemtax it
                                        INNER JOIN productorderitem i ON i.id = it.orderitemid
                                        INNER JOIN productinventory iv ON iv.id = i.productinventoryid
                                        INNER JOIN producttax t ON t.id = it.taxid
                                        INNER JOIN product p ON p.id = iv.productid
                                        WHERE i.productorderid = %d",
                                    db_real_escape_string($orderModel->id)
                                ));

                                // aggregate query
                                $taxQry = array();
                                while ($t = db_fetch_assoc($qT)) {
                                    if (!isset($taxQry[$t['taxid']])) {
                                        $taxQry[$t['taxid']] = array(
                                            'id' => 0,
                                            'code' => '',
                                            'product' => array(),
                                            'totalItems' => 0,
                                            'totalPaid' => 0
                                        );
                                    }
                                    $arr = &$taxQry[$t['taxid']];

                                    $arr['id'] = $t['taxid'];
                                    $arr['code'] = $t['code'];
                                    $arr['rate'] = $t['rate'];
                                    array_push($arr['product'],'<a href="/jpanel/products/edit?i='.$t['productid'].'" title="'.$t['productname'].' ('.$t['productsku'].')">'.$t['productname'].'</a> ('.money($t['amount'], $orderModel->currency).')');
                                    $arr['totalItems'] += floatval($t['amount']);
                                    $arr['totalPaid'] += floatval($t['taxamount']);
                                }

                                $totalTaxPaid_onItems = 0;
                                foreach ($taxQry as $tax) {
                                    echo '<tr>';
                                        echo '<td valign="top">'.$tax['code'].'</td>';
                                        echo '<td valign="top">'.implode('<br />',$tax['product']).'</td>';
                                        echo '<td valign="top" style="text-align:right;">'.money($tax['totalItems'], $orderModel->currency).'</td>';
                                        echo '<td valign="top" style="text-align:right;">'.$tax['rate'].'%'.'</td>';
                                        echo '<td valign="top" style="text-align:right;">'.money($tax['totalPaid'], $orderModel->currency).'</td>';
                                    echo '</tr>';
                                    $totalTaxPaid_onItems += $tax['totalPaid'];
                                }
                            ?>

                            <?php if($orderModel->shippable_items > 0): ?>
                                <tr>
                                    <td></td>
                                    <td>Shipping</td>
                                    <td style="text-align:right;"><?= e(money($orderModel->shipping_amount, $orderModel->currency)) ?></td>
                                    <td style="text-align:right;"></td>
                                    <td style="text-align:right;"><?= e(money(bcsub($orderModel->taxtotal, $totalTaxPaid_onItems, 2), $orderModel->currency)) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align:right;"><?= e(money($orderModel->taxtotal, $orderModel->currency)) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
spaContentReady(function() {

    <?php if (sys_get('double_the_donation_enabled') && $orderModel->doublethedonation_registered) : ?>
        axios.get('/jpanel/api/v1/double-the-donation/<?= e($orderModel->id) ?>/status')
        .then(function(res) {
            $('[data-dtd="fetching"]').hide();

            if (! res.data.data.company_name) {
                $('[data-dtd="no-match"]').removeClass('hide');
            }
            else {
                $('[data-dtd="loaded"]').removeClass('hide');
            }

            $('[data-dtd="status"]').html(res.data.data.status_label || 'N/D');
            $('[data-dtd="company-name"]').html(res.data.data.company_name || 'N/D');
        });
    <?php endif; ?>

    $('input[name=refund_type]').on('change', function (ev) {
        if (($(ev.target).val() == 'custom')) {
            $('#custom-refund-amount').css('display', 'block');
            $('#full-refund-option').css('display', 'none');
            $('#custom-refund-amount input').val('').focus();
        } else {
            $('#custom-refund-amount').css('display', 'none' );
            $('#full-refund-option').css('display', 'block');
        }
    });

    $('.change-product').on('click', function(ev){
        ev.preventDefault();

        var item_id    = $(ev.target).data('item-id');
        var $modal     = $('#update-item');
        $modal.find('input[name=item_id]').val(item_id);

        $modal.modal();
    });

    $('.change-gift-aid-eligibility').on('click', function(ev){
        ev.preventDefault();

        var $el = $(ev.target);
        var item_id = $el.data('item-id');
        var gift_aid_eligible = $el.data('gift-aid-eligible');
        var $modal = $('#update-gift-aid_eligibility');
        var $form = $modal.find('form');
        var $submitBtn = Ladda.create($form.find('button[type=submit]')[0]);
        var $cancelBtn = $form.find('button[type=button]');
        $modal.find('input[name=item_id]').val(item_id);
        $modal.find('select[name=gift_aid_eligible]').val(gift_aid_eligible);

        $modal.modal();

        $form.on('submit', function(event) {
            $submitBtn.start();
            $cancelBtn.prop('disabled', true);
        });
    });

    $('.change-custom-fields').on('click', function(ev){
        ev.preventDefault();

        var item_id = $(ev.target).data('item-id');
        var $modal = $('#update-item-fields');
        var $container = $('#update-item-fields-container');

        $modal.on('show.bs.modal', function(){
            $container.html('<div class="text-center text-muted" style="margin:50px;"><i class="fa fa-4x fa-spin fa-circle-o-notch"></i></div>');
            $container.load('<?= e(route('backend.orders.getItemFields', $orderModel)) ?>?item_id='+item_id);
        });

        $modal.modal();
    });
});
</script>
