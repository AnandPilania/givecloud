
<script>
    function exportRecords() {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/reports/platform-fees.csv?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Platform Fee Statements
            <div class="visible-xs-block"></div>

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
                    <input type="text" class="form-control" name="search" id="filterSearch" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter Platform Fees by:<br><i class='fa fa-check'></i> Description" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Statement Date</label>
                <select name="period" class="form-control selectize" placeholder="Billing Period">
                    <?php
                        $first_date = \Ds\Models\TransactionFee::min('created_at');
                        $loop_date = \Carbon\Carbon::today()->startOfMonth();
                        if ($first_date) {
                            $loop_date = \Carbon\Carbon::parse($first_date)->startOfMonth();
                        }
                    ?>
                    <?php while ($loop_date->isPast()): ?>
                        <option value="<?= e($loop_date->format('Y-m')) ?>" <?= e((request('period',\Carbon\Carbon::today()->subMonth()->format('Y-m')) == $loop_date->format('Y-m')) ? 'selected' : '') ?> ><?= e($loop_date->format('M Y')) ?></option>
                        <?php $loop_date->addMonth(1); ?>
                    <?php endwhile; ?>
                </select>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="grid grid-cols-3 gap-8 mb-8">
            <div class="col-span-2 bg-white drop-shadow-xl rounded-lg py-4 px-6">
                <p class="text-lg text-neutral-600 mb-4">Fee Summary</p>
                <div id="summary-panel"></div>
            </div>
            <div class="bg-white rounded drop-shadow-xl rounded-lg py-4 px-6">
                <p class="text-lg text-neutral-600 mb-4">DCC Summary</p>
                <div id="dcc-panel"></div>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="transactionFeesTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Ref #</th>
                        <th>Description</th>
                        <th>Rate</th>
                        <th>Fee</th>
                        <th>Exchange Rate</th>
                        <th>Settled Fee</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
