
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>

            <div class="pull-right">
                <a href="/jpanel/reports/check_ins/audit.csv?i=<?= e($variant_id); ?>" class="btn btn-default"><i class="fa fa-download fa-fw"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th style="width:110px;">Contribution</th>
                        <th>Bill-To</th>
                        <th>Ship-To</th>
                        <th style="width:70px; text-align:center;">Checked-In</th>
                        <th style="width:70px; text-align:center;">Check-In Count</th>
                        <th style="width:120px;">Last Check-In</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($check_ins as $check_in): ?>
                        <tr>
                            <td width="16"><a href="<?= e(route('backend.orders.checkin', ['o' => $check_in->order_id, 'i' => $check_in->order_item_id])) ?>"><i class="fa fa-search"></i></a></td>
                            <td><a href="<?= e(route('backend.orders.edit', $check_in->order_id)) ?>" target="_blank"><?= e($check_in->order_number) ?></a></td>
                            <td><?= e($check_in->billing_last_name) ?>, <?= e($check_in->billing_first_name) ?></td>
                            <td><?= e($check_in->shipping_last_name) ?>, <?= e($check_in->shipping_first_name) ?></td>
                            <td style="width:70px; text-align:center;"><?= e(($check_in->check_ins > 0) ? 'Yes' : '') ?></td>
                            <td style="width:70px; text-align:center;"><?= e($check_in->check_ins) ?></td>
                            <td data-order="<?= e(toLocalFormat($check_in->last_check_in, 'U')) ?>" style="width:120px;"><?= e(toLocalFormat($check_in->last_check_in, 'M j, Y H:i')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
