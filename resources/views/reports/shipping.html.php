
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>

            <div class="pull-right">
                <a href="shipping.csv?fd1=<?= e(request('fd1')); ?>&fd2=<?= e(request('fd2')); ?>" class="btn btn-default"><i class="fa fa-download fa-fw"></i> Export</a>
            </div>
        </h1>
    </div>
</div>

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

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-shopping-cart fa-4x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= e(numeralFormat($shipping_total_orders, '0[.]0A')) ?></div>
                        <div>Shipped Contributions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-truck fa-4x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= e(money($shipping_total_amount)->format('$0.a')) ?></div>
                        <div>Shipping Charges</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width:110px;">Date</th>
                        <th style="width:110px;">Contribution</th>
                        <th>Ship-To</th>
                        <th style="width:90px; text-align:right;">Total</th>
                        <th style="width:90px; text-align:right; font-weight:bold;">Shipping</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($shipping as $order): ?>
                    <tr>
                        <td><?= e(toLocalFormat($order->confirmationdatetime, 'Y-m-d')) ?></td>
                        <td><?= e($order->invoicenumber) ?></td>
                        <td><?= e($order->shipcity) ?>, <?= e($order->shipstate) ?>, <?= e($order->shipzip) ?></td>
                        <td style="text-align:right;"><?= e(number_format($order->totalamount,2)) ?>&nbsp;<span class="text-muted"><?= e($order->currency_code) ?></span></td>
                        <td style="text-align:right; font-weight:bold;"><?= e(number_format($order->shipping_amount,2)) ?>&nbsp;<span class="text-muted"><?= e($order->currency_code) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
