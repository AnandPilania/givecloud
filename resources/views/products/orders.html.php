
<script>
    exportRecords = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '<?= e(route('backend.reports.products.export', $productModel)) ?>?' + $.param(d);
    }
    exportRecordsWith = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        d.product_ids = $('#export-with-product-ids').val();
        window.location = '<?= e(route('backend.reports.products.export_with_items', $productModel)) ?>?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($productModel->name) ?> <small>All Contributions</small>
            <div class="pull-right">
                <a href="<?= $productModel->isFundraisingForm ? route('backend.fundraising.forms.view', $productModel->hashid) : route('backend.products.edit', ['i' => $productModel->getKey()]); ?>" class="btn btn-info"><i class="fa fa-search fa-fw"></i>
                    View Product
                </a>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-download fa-fw"></i> Export <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li><a onclick="exportRecords(); return false;"><i class="fa fa-fw fa-download"></i> Export</a></li>
                        <li><a href="#modal-export-with" data-toggle="modal"><i class="fa fa-fw fa-download"></i> Export With...</a></li>
                    </ul>
                </div>
            </div>
        </h1>
    </div>
</div>

<!-- /.row -->
<div class="row">
    <div class="col-lg-8 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> 60 Day Sales
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="row">
                    <div id="product-sales-chart" style="height:285px;"></div>
                    <script type="application/json" id="product-sales-chart-data" data-currency-symbol="<?= e(currency()->symbol) ?>"><?= dangerouslyUseHTML(json_encode($chart_data)) ?></script>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-shopping-cart fa-fw"></i> Sales Stats
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="row">

                    <div class="col-sm-6 stat">
                        <div id="stats_total_quantity_sold" class="stat-value">--</div>
                        <div class="stat-label">Total Qty Sold</diV>
                    </div>

                    <div class="col-sm-6 stat">
                        <div id="stats_total_orders" class="stat-value">--</div>
                        <div class="stat-label">Total Contributions</diV>
                    </div>

                    <div class="col-sm-6 stat">
                        <div id="stats_total_sales" class="stat-value"><?= e(currency()->symbol) ?><span>-.--</span></div>
                        <div class="stat-label">Total Sales</diV>
                    </div>

                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
</div>

<div class="row">
    <form class="datatable-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">


            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" value="<?= e($filters->search) ?>" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter contributions by:<br><i class='fa fa-check'></i> Billing or Shipping Name<br><i class='fa fa-check'></i> Contribution Number" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Search</label>
                <select class="form-control" name="c" id="c" placeholder="Contribution Status">
                    <option value=""  <?= dangerouslyUseHTML((request('c') === '')?'selected="selected"':'') ?>>Any Status</option>
                    <option value="1" <?= dangerouslyUseHTML((request('c') === '1')?'selected="selected"':'') ?>>Complete Contributions</option>
                    <option value="0" <?= dangerouslyUseHTML((request('c') === '0')?'selected="selected"':'') ?>>Incomplete Contributions</option>
                    <option value="2" <?= dangerouslyUseHTML((request('c') === '2')?'selected="selected"':'') ?>>Refunded Contributions</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Search</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="ordered_at_str" value="<?= e($filters->ordered_at_str) ?>" placeholder="Ordered on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="ordered_at_end" value="<?= e($filters->ordered_at_end) ?>" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-dollar fa-fw"></i></div>
                    <input type="text" class="form-control" name="total_str" value="<?= e($filters->total_str) ?>" placeholder=">= Total" />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="total_end" value="<?= e($filters->total_end) ?>" placeholder="<= Total" />
                </div>
            </div>

            <?php if (count($currencies) > 1) { ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Search</label>
                    <select class="form-control" name="cc" placeholder="Currency">
                        <option value="">All Currencies</option>
                        <?php foreach($currencies as $currency): ?>
                        <option value="<?= e($currency->code) ?>" <?= e((request('cc') == $currency->code) ? 'selected' : '') ?>><?= e($currency->code) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php } ?>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Search</label>
                <select class="form-control" name="foi">
                    <option value="">Any IP Country</option>
                    <?php foreach($countries as $country): ?>
                        <option value="<?= e($country['code']) ?>"><?= e($country['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (sys_get('gift_aid') == 1) { ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Search</label>
                    <select class="form-control" name="fga">
                        <option value="">Any Gift Aid Status</option>
                        <option value="1">Gift Aid eligible</option>
                        <option value="0">Gift Aid ineligible</option>
                    </select>
                </div>
            <?php } ?>

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="productOrdersDatatable" data-product-id="<?= e($productModel->id) ?>" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th width="150">Contribution Date</th>
                        <th>Contribution#</th>
                        <th>Billing First Name</th>
                        <th>Billing Last Name</th>
                        <th>Variant</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade modal-info" tabindex="-1" role="dialog" id="modal-export-with">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-download fa-fw"></i> Export with...</h4>
            </div>

            <form>
                <div class="modal-body">
                    <p>Export all sales of '<strong><?= e($productModel->name) ?></strong>' and include the following items only if they were included on the same order:</p>

                    <div class="form-group">
                        <select class="ds-products" id="export-with-product-ids" name="product_ids[]" multiple placeholder="Select items..."></select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="exportRecordsWith(); return false;" class="btn btn-info" type="button">Export</button>
                </div>
            </form>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
