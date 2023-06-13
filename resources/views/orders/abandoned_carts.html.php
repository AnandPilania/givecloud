<script>
    exportRecords = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '<?= e(route('backend.orders.abandoned_carts_csv')) ?>?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>

            <div class="pull-right">
                <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <form class="datatable-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="fO" id="fO" value="<?= e(request('fO')); ?>" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter contributions by:<br><i class='fa fa-check'></i> Bill-To name &amp; email<br><i class='fa fa-check'></i> Ship-To name &amp; email<br><i class='fa fa-check'></i> Response text." />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Checkout Status</label>
                <select class="form-control" id="fs" name="fs">
                    <option value="">Any Checkout Status</option>
                    <option value="1" <?= dangerouslyUseHTML((request('fs') == 1) ? 'selected="selected"' : '') ?>>Pre-Checkout</option>
                    <option value="2" <?= dangerouslyUseHTML((request('fs') == 2) ? 'selected="selected"' : '') ?>>Checking Out</option>
                    <option value="3" <?= dangerouslyUseHTML((request('fs') == 3) ? 'selected="selected"' : '') ?>>Failed Payment</option>
                    <option value="4" <?= dangerouslyUseHTML((request('fs') == 4) ? 'selected="selected"' : '') ?>>Spam/Fraud</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Started</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="fd1" value="<?= e(request('fd1')); ?>" placeholder="Started..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="fd2" value="<?= e(request('fd2')); ?>" />
                </div>
            </div>

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="abandonedCartsTable" class="table table-striped table-bordered table-hover responsive">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Bill To</th>
                        <th>Email</th>
                        <th width="80">Items</th>
                        <th>Total ($)</th>
                        <th>Response Text</th>
                        <th>Browser</th>
                        <th width="130">IP</th>
                        <th width="150">Started Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
