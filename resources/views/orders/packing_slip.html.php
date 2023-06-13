<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Packing Slips</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    /** top of the packingslip **/
    .top_right { float:right; text-align:right; color:#999; }
    .invoice_number { font-size:20pt; }
    .invoice_date { font-size:12pt;}
    .bill_ship_wrap { height:120pt; margin-top:20pt; position:relative; }
    .bill_to { position:absolute; top:0px; left:0px; }
    .ship_to { position:absolute; top:0px; left:50%; }

    /** product list **/
    .product_list {}
    .product_list table { border:0px; border-collapse:collapse; width:100%; margin:0px; padding:0px; }
    .product_list table td, .product_list table th { margin:0px; padding:0px; border:0px; }
    .product_list table thead th { padding:5pt; font-weight:bold; border:1px solid #000; border-bottom:2px solid #000; }
    .product_list table tbody td { padding:5pt; border:1px solid #000; }
    .product_list td.product_list-product, th.product_list-product  {  }
    .product_list td.product_list-qty, th.product_list-qty { text-align:center; }
    .product_list td.product_list-pricepaid, th.product_list-pricepaid { text-align:right; }
    .product_list td.product_list-total, th.product_list-total { text-align:right; }
    .product_list tfoot th { font-weight:bold; border:1px solid #000; text-align:right; padding:5pt; }
    .product_list tfoot td { font-weight:normal; border:1px solid #000; padding:5pt; text-align:right; }
    .item_image { float:left; margin:0 5pt 0 0; }
    .item_name { font-weight:bold; font-size:12pt; }
    .item_code { font-size:12pt; }
    .item_field {  }
    .item_field_label { font-weight:bold; }
    .item_field_value { }
    .shipping { margin:14pt 25% 0pt 25%; border:2px solid #000; font-size:12pt; padding:4pt; text-align:center; }
    .price-strike { text-decoration:line-through; }

    /** PAGE SETUP **/
    html { width:100%; }
    body { width:8.5in; margin:0 auto; padding:0px; background-color:#f7f7f7; font-family:Arial, Helvetica, sans-serif; font-size:10pt; }
    .pg_wrap { width:100%; margin:20pt 0 0 0; border:1px solid transparent; box-shadow:0px 7px 40px rgba(0,0,0,0.3); page-break-after:always; background-color:#fff; }
    .pg { margin:20pt; }
    .print_page { margin:20pt auto 0 auto; text-align:center; }
    @media print {
        body { width:100%; letter-spacing:0.5pt; font-size:12pt; background-color:#fff; }
        .pg_wrap { border:0px; margin:0pt; box-shadow:none; }
        .pg { margin:0pt; }
        .top_right { color:#000; }
        .print_page { display:none; }
    }
</style>
</head>
<body>

<div class="print_page">
    <a href="javascript:print();"><i class="fa fa-print fa-fw"></i>Print</a>
</div>

<?php foreach ($orders as $order): ?>

<div class="pg_wrap">
    <div class="pg">

        <div class="top_right">
            <div class="invoice_number">
            <?= e(strtoupper(sys_get('packing_slip_contribution_syn'))) ?> #<?= e($order->invoicenumber) ?>
            </div>
            <div class="invoice_date">
                <?= e(toLocalFormat($order->ordered_at, 'M j, Y')) ?>
            </div>
        </div>

        <div class="corporate_header">
            <?= dangerouslyUseHTML(sys_get('packing_slip_corporate_header')) ?>
        </div>

        <div class="bill_ship_wrap">
            <div class="bill_to">
                <strong>BILL TO</strong><br />
                <?= e($order->billing_title ? $order->billing_title . ' ' : '') ?>
                <?= dangerouslyUseHTML($order->billing_display_name ? e($order->billing_display_name) . '<br>' : '') ?>
                <?= dangerouslyUseHTML(nl2br(e(address_format($order->billingaddress1, $order->billingaddress2, $order->billingcity, $order->billingstate, $order->billingzip, $order->billingcountry)))) ?>
                <?= dangerouslyUseHTML($order->billingphone ? '<br>Phone: ' . e($order->billingphone) : '') ?>
                <?= dangerouslyUseHTML($order->billingemail ? '<br>Email: ' . e($order->billingemail) : '') ?>
            </div>

            <?php if(feature('shipping') && $order->shippable_items): ?>
                <div class="ship_to">
                    <strong>SHIP TO</strong><br />
                    <?= e($order->shipping_title ? $order->shipping_title . ' ' : '') ?>
                    <?= dangerouslyUseHTML($order->shipping_display_name ? e($order->shipping_display_name) . '<br>' : '') ?>
                    <?= dangerouslyUseHTML($order->shipping_organization_name ? e($order->shipping_organization_name) . '<br>' : '') ?>
                    <?= dangerouslyUseHTML(nl2br(e(address_format($order->shipaddress1, $order->shipaddress2, $order->shipcity, $order->shipstate, $order->shipzip, $order->shipcountry)))) ?>
                    <?= dangerouslyUseHTML($order->shipphone ? '<br>Phone: ' . e($order->shipphone) : '') ?>
                    <?= dangerouslyUseHTML($order->shipemail ? '<br>Email: ' . e($order->shipemail) : '') ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product_list">
            <table>
                <thead>
                    <tr>
                        <th class="product_list-product">Product</th>
                        <th class="product_list-qty">Qty</th>
                        <th class="product_list-pricepaid">Price</th>
                        <th class="product_list-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($order->items as $item): ?>
                    <tr>
                        <td class="product_list-product">
                            <div class="item_image" style="<?= e(($item->is_locked) ? 'margin-left:40px;' : '') ?>"><img src="<?= e(media_thumbnail($item->variant->product)) ?>" width="70" border="0" /></div>
                            <div style="<?= e(($item->is_locked) ? 'margin-left:125px;' : 'margin-left:85px;') ?>">
                                <div class="item_name">
                                    <?= dangerouslyUseHTML($item->variant->product->name) ?><?= dangerouslyUseHTML((trim($item->variant->variantname) != '')?' ('.$item->variant->variantname.')':'') ?>
                                </div>
                                <div class="item_code"><?= e($item->code) ?></div>

                                <div class="item_field">
                                    <?php if($item->variant->product->isrecurring == 1): ?>
                                        <div class="recurring_desc">
                                            <?= e($item->payment_string) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($item->variant->product->istribute == 1 && trim($item->tribute_name) !== ''): ?>
                                        <div class="tribute_desc">In Memory of: <?= e($item->tribute_name) ?></div>
                                    <?php endif; ?>
                                </div>

                                <?php foreach($item->fields as $field): ?>
                                    <div class="item_field"><span class="item_field_label"><?= dangerouslyUseHTML($field->name) ?>:&nbsp;</span><span class="item_field_value"><?= e($field->value_formatted) ?></span></div>
                                <?php endforeach; ?>

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

                                <?php if($item->public_message): ?>
                                    <div class="top-gutter-sm">
                                        &ldquo;<?= e($item->public_message) ?>&rdquo;<br>
                                        <?php if($order->is_anonymous): ?>
                                            <small class="text-muted">- Anonymous</small>
                                        <?php else: ?>
                                            <small class="text-muted">- <?= e($order->member->display_name) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </td>
                        <td class="product_list-qty"><?= e($item->qty) ?></td>
                        <?php if($item->is_locked): ?>
                            <td class="product_list-pricepaid">-</td>
                            <td class="product_list-total">-</td>
                        <?php elseif($item->lockedItems->count() > 0): ?>
                            <td class="product_list-pricepaid">
                                <?= e(number_format($item->locked_variants_price,2)) ?>
                                <?php if($item->locked_variants_price < $item->locked_variants_original_price): ?><br><small class="price-strike"><?= e(number_format($item->locked_variants_undiscounted_price,2)) ?></small><?php endif; ?>
                            </td>
                            <td class="product_list-total"><?= e(number_format($item->locked_variants_total,2)) ?></td>
                        <?php else: ?>
                            <td class="product_list-pricepaid"><?= e(number_format($item->price,2)) ?><?php if($item->is_price_reduced): ?><br><small class="price-strike"><?= e(number_format($item->undiscounted_price,2)) ?></small><?php endif; ?></td>
                            <td class="product_list-total"><?= e(number_format($item->total,2)) ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td rowspan="7" colspan="2" style="text-align:left;">
                            <?php if($order->comments): ?>
                                <strong>Special Notes:</strong><br>
                                <?= e($order->comments) ?>
                            <?php endif; ?>
                        </td>
                        <th>Subtotal</th>
                        <td><?= e(number_format($order->subtotal,2)) ?></td>
                    </tr>
                    <?php if((feature('shipping') && $order->shippable_items) || $order->shipping_amount): ?>
                        <tr>
                            <th>Shipping</th>
                            <td><?= e(number_format($order->shipping_amount,2)) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if(feature('taxes') || $order->taxtotal): ?>
                        <tr>
                            <th>Taxes</th>
                            <td><?= e(number_format($order->taxtotal,2)) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if($order->dcc_total_amount): ?>
                        <tr>
                            <th><?= e(sys_get('dcc_invoice_label')) ?></th>
                            <td><?= e(number_format($order->dcc_total_amount,2)) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Total</th>
                        <td><?= e(number_format($order->totalamount,2)) ?></td>
                    </tr>
                    <?php if($order->refunded_at): ?>
                        <tr>
                            <th>
                                Refund<br>
                                <small>on <?= e(toLocalFormat($order->refunded_at, 'M j, Y')) ?> by <?= e($order->refundedBy->full_name) ?></small>
                            </th>
                            <td style="text-align:right;"><?= e(number_format(-$order->refunded_amt,2)) ?></td>
                        </tr>
                        <tr>
                            <th>Balance</th>
                            <td style="text-align:right;"><?= e(number_format($order->balance_amt,2)) ?></td>
                        </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>

        <?php if (feature('shipping') && $order->shipping_method_name): ?>
            <div class="shipping">
                <strong><?= e($order->shipping_method_name) ?></strong><br />
                <?= e($order->shippingMethod->description ?? '') ?>
            </div>
        <?php endif ?>

    </div>
</div>
<?php endforeach ?>

<?php if (request()->exists('print')): ?>
    <script>print();</script>
<?php endif; ?>

</body>
</html>
