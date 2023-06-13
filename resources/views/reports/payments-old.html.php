
<script>
    exportRecords = function () {
        $('#payment-report-filters').attr('action', '/jpanel/reports/payments-old.csv').submit();
    }
</script>

<form id="payment-report-filters">

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                <?= e($title) ?>

                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
                </div>

                <div class="text-secondary"><span class="text-warning"><i class="fa fa-exclamation-triangle"></i> This report is being deprecated. Try our <a href="/jpanel/reports/payments" class="text-bold">New Payments Report</a> (no date restrictions, more filters, more accurate!).</span></div>
            </h1>
        </div>
    </div>

    <?php if($dates_fixed): ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> This report can only report on a maximum of a 31 day period.
        </div>
    <?php endif; ?>


    <div class="row">
        <div class="datatable-filters">
            <div class="datatable-filters-label">
                <div class="form-control-static no-wrap"><strong><i class="fa fa-filter"></i> Filters</strong></div>
            </div>

            <div class="datatable-filters-fields">
                <div class="row">

                    <div class="form-group col-lg-4 col-md-6 col-sm-12 col-xs-12">
                        <div class="input-group input-daterange-pretty">
                            <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                            <input type="text" class="form-control" name="start_date" value="<?= e($filters['start_date']); ?>" placeholder="Payment on..." />
                            <span class="input-group-addon">to</span>
                            <input type="text" class="form-control" name="end_date" value="<?= e($filters['end_date']); ?>" />
                        </div>
                    </div>
                    <div class="form-group col-xs-1">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</form>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-pie-chart"></i> Payments by Method</div>
                </div>

                <div class="row">
                    <div class="col-sm-4">
                        <div id="payments-by-type-donut" style="height:<?= e((count($payments_by_type) > 6) ? '215px' : '180px') ?>" data-chart-data="<?= e(json_encode($payments_by_card_type_chart), ENT_QUOTES, 'UTF-8') ?>"></div>
                    </div>

                    <div class="col-xs-12 col-sm-8">
                        <div class="row">
                            <?php $_ix = 0; foreach($payments_by_type as $type => $amount): ?>
                                <div class="col-xs-6 col-md-6 col-lg-4 stat">
                                    <div class="stat-value"><?= e(money($amount)) ?></div>
                                    <div class="stat-label" style="color:<?= e($color_array[$_ix++ % 7]) ?>;"><i class="fa fa-fw <?= e(fa_payment_icon($type)) ?>"></i> <?= e($type ?: 'Unknown') ?></diV>
                                </div>
                            <?php endforeach; ?>

                            <div class="col-xs-6 col-md-6 col-lg-4 stat">
                                <div class="stat-value"><?= e(money($payments_total)) ?></div>
                                <div class="stat-label">Total</diV>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="payments-listing">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Transaction</th>
                        <th width="120">Amount</th>
                        <th>Name</th>
                        <th width="120">Method</th>
                        <th width="120">Account #</th>
                        <th>Reference</th>
                        <!--<th width="150">Avg Amount</th>
                        <th width="150">Total Amount</th>-->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $payment): ?>
                        <tr class="<?= e(($payment->amount < 0) ? 'text-danger' : '') ?>">
                            <td data-sort="<?= e($payment->transaction_date) ?>"><?= e(fromLocalFormat($payment->transaction_time,'M j Y g:i:sa')) ?></td>
                            <td><?= e($payment->transaction_id) ?></td>
                            <td><?= e(number_format($payment->amount,2)) ?></td>
                            <td><?= e($payment->first_name) ?> <?= e($payment->last_name) ?></td>
                            <td><?= e($payment->account_type) ?></td>
                            <td><?= e($payment->account_number) ?></td>
                            <td>
                                <?php if ($payment->reference == 'order'): ?>
                                    Contribution <a href="<?= e(route('backend.orders.order_number', $payment->reference_number)) ?>" target="_blank">#<?= e($payment->reference_number) ?></a>
                                <?php elseif ($payment->reference == 'rpp'): ?>
                                    Recurring Profile <a href="/jpanel/recurring_payments/<?= e($payment->reference_number) ?>" target="_blank"><?= e($payment->reference_number) ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
spaContentReady(function() {

    Morris.Donut({
        element   : 'payments-by-type-donut',
        data      : $('#payments-by-type-donut').data('chart-data'),
        colors    : <?= dangerouslyUseHTML(json_encode($color_array)) ?>,
        resize    : true
    });

    var payments_list = $('#payments-listing').DataTable({
        "dom": 'rtpi',
        "sErrMode":'throw',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        //"serverSide": true,
        "order": [[ 0, "asc" ]],
        "columnDefs": [
            { "orderable": true, "targets": 0, "class" : "text-left" },
            { "orderable": true, "targets": 1, "class" : "text-left" },
            { "orderable": true, "targets": 2, "class" : "text-right" },
            { "orderable": true, "targets": 3, "class" : "text-left" },
            { "orderable": true, "targets": 4, "class" : "text-center" },
            { "orderable": true, "targets": 5, "class" : "text-center" },
            { "orderable": true, "targets": 6, "class" : "text-left" }
        ],
        "stateSave": true,

        // colors/styles
        "fnRowCallback": function( nRow, aData ) {
            /*var iscomplete = aData[0];
            var isUnsynced = aData[1];
            var refundAmt = aData[12];

            var $nRow = $(nRow); // cache the row wrapped up in jQuery

            if (iscomplete)
                $nRow.addClass('success');

            if (isUnsynced)
                $nRow.addClass('danger');

            if (refundAmt > 0)
                $nRow.addClass('text-danger');

            return nRow;*/
        },

        "drawCallback" : function(){
            j.ui.datatable.formatRows($('#payments-listing'));
            return true;
        },

        "initComplete" : function(){
            j.ui.datatable.formatTable($('#payments-listing'));
        }
    });

    j.ui.datatable.enableFilters(payments_list);
});
</script>
