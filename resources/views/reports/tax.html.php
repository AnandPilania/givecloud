
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>

            <div class="pull-right">
                <a href="tax.csv?fd1=<?= e(request('fd1')); ?>&fd2=<?= e(request('fd2')); ?>" class="btn btn-default"><i class="fa fa-download fa-fw"></i> Export</a>
            </div>
        </h1>
    </div>
</div>

<?php if(sys_get('taxcloud_api_key')): ?>
    <div class="alert alert-warning">
        <strong><i class="fa fa-exclamation-circle"></i> Note</strong> - TaxCloud is currently managing your sales tax. This report is limited to contributions received prior to your TaxCloud integration.
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <i class="fa fa-filter fa-fw"></i> Filter Options
            </div>
            <form>
                <div class="panel-body">

                    <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <label for="exampleInputPassword2">Start Date</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            <input type="text" class="form-control date" name="fd1" id="fd1" value="<?= e(request('fd1')); ?>" />
                        </div>
                    </div>

                    <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <label for="exampleInputPassword2">End Date</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            <input type="text" class="form-control date" name="fd2" id="fd2" value="<?= e(request('fd2')); ?>" />
                        </div>
                    </div>

                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary"><i></i>Filter List</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php foreach ($taxes as $tax): ?>

<div class="row">
    <div class="col-lg-12">

        <h2><?= e($tax->code) ?> <span style="font-weight:normal; color:#999;"><?= e($tax->rate) ?>%</span></h2>

        <?php
            $orders = $getOrders($tax);

            $total_price = 0;
            $total_tax   = 0;
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width:110px;">Date</th>
                        <th style="width:110px;">Contribution</th>
                        <th>Product</th>
                        <th style="width:90px; text-align:right;">Price</th>
                        <th style="width:60px; text-align:center;">Qty</th>
                        <th style="width:90px; text-align:right;">Total</th>
                        <th style="width:90px; text-align:right;">Tax</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $item): ?>
                    <?php $total_price += $item->total_amount ?>
                    <?php $total_tax   += $item->amount ?>
                    <tr>
                        <td><?= e(toLocalFormat($item->confirmationdatetime, 'Y-m-d')) ?></td>
                        <td><?= e($item->invoicenumber) ?></td>
                        <td><?= e($item->name) ?><?php if(trim($item->variantname) !== ''): ?>(<?= e($item->variantname) ?>)<?php endif; ?></td>
                        <td style="text-align:right;"><?= e(number_format($item->price,2)) ?></td>
                        <td style="text-align:center;"><?= e($item->qty) ?></td>
                        <td style="text-align:right;"><?= e(number_format($item->total_amount,2)) ?></td>
                        <td style="text-align:right;"><?= e(number_format($item->amount,2)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align:right;"><?= e(number_format($total_price,2)) ?></td>
                        <td style="text-align:right;"><?= e(number_format($total_tax,2)) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php endforeach; ?>
