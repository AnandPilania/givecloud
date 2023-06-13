
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>

            <div class="pull-right">
                <a href="/jpanel/check-ins" class="btn btn-primary btn-outline"><i class="fa fa-search"></i> Look-Up <span class="badge">NEW</span></a>
                <a href="/jpanel/reports/check_ins.csv" class="btn btn-default"><i class="fa fa-download fa-fw"></i><span class="hidden-xs hidden-sm"> Export</span></a>
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
                        <th rowspan="2" width="16"></th>
                        <th rowspan="2">Code</th>
                        <th rowspan="2">Product</th>
                        <th rowspan="2">Variant</th>
                        <th rowspan="2" style="width:70px; text-align:center;">Ticket Count</th>
                        <th colspan="3" style="text-align:center;">Checked-In</th>
                        <th rowspan="2">First Check-In</th>
                        <th rowspan="2">Last Check-In</th>
                    </tr>
                    <tr>
                        <th>Once</th>
                        <th>Multiple</th>
                        <th>Not Yet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($check_ins as $check_in): ?>
                        <tr>
                            <td width="16"><a href="/jpanel/reports/check_ins/audit?i=<?= e($check_in->variant_id) ?>"><i class="fa fa-search"></i></a></td>
                            <td><?= e($check_in->product_code) ?></td>
                            <td><?= e($check_in->product_name) ?></td>
                            <td><?= e($check_in->variant_name) ?></td>
                            <td style="width:70px; text-align:center;"><?= e($check_in->ticket_count) ?></td>
                            <td style="width:70px; text-align:center;"><?= e($check_in->checked_in_count) ?></td>
                            <td style="width:70px; text-align:center;"><?= e($check_in->multi_checked_in_count) ?></td>
                            <td style="width:70px; text-align:center;"><?= e($check_in->ticket_count-$check_in->checked_in_count) ?></td>
                            <td data-order="<?= e(toLocalFormat($check_in->first_check_in, 'U')) ?>" style="width:120px;"><?= e(toLocalFormat($check_in->first_check_in, 'M j, Y')) ?></td>
                            <td data-order="<?= e(toLocalFormat($check_in->last_check_in, 'U')) ?>" style="width:120px;"><?= e(toLocalFormat($check_in->last_check_in, 'M j, Y')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
