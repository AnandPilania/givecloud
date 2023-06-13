<script>
    function exportRecords() {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/reports/donor-covers-costs.csv?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Donor Covers Costs
            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <dl class="my-5 grid grid-cols-1 gap-5 sm:grid-cols-3" id="donorCoversCostsStats">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6" data-stats="totals">
                <dt class="truncate text-base font-medium text-gray-900">Total DCC</dt>
                <div class="animate-pulse hidden" data-loading>
                    <div class="h-7 w-40 mt-1 mb-2 bg-slate-200 rounded max-w-xs"></div>
                    <div class="h-3 bg-slate-200 rounded max-w-xs"></div>
                </div>
                <div class="hidden" data-loaded></div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6" data-stats="average">
                <dt class="truncate text-base font-medium text-gray-900">Average DCC Amount</dt>
                <div class="animate-pulse hidden" data-loading>
                    <div class="h-7 w-40 mt-1 mb-2 bg-slate-200 rounded max-w-xs"></div>
                    <div class="h-3 bg-slate-200 rounded max-w-xs"></div>
                </div>
                <div class="hidden" data-loaded></div>
            </div>

            <div class="relative rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6" data-stats="conversions">
                <dt class="truncate text-base font-medium text-gray-900">Conversions</dt>
                <div class="animate-pulse hidden" data-loading>
                    <div class="h-7 w-40 mt-1 mb-2 bg-slate-200 rounded max-w-xs"></div>
                    <div class="h-3 bg-slate-200 rounded max-w-xs"></div>
                </div>
                <div class="hidden" data-loaded></div>
                <i class="absolute top-5 right-5 text-xl fa fa-question-circle" data-popover-size="large" data-placement="left" data-trigger="hover" data-toggle="popover" title="DCC Conversion is calculated against all contributions eligible for DCC." data-content="If you sell items that have DCC disabled, the total number of eligible DCC contributions may not match the total number of contributions in the same period. Contributions with multiple line items eligible for DCC are counted as a single contribution, while the report below is listing all line items."></i>
            </div>
        </dl>
    </div>
</div>

<div class="row">
    <form class="datatable-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter DCC by:<br><i class='fa fa-check'></i> Description" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Created</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="period_start" value="<?= e(toLocalFormat(\Carbon\Carbon::now()->subMonth(), 'Y-m-d')) ?>" placeholder="Created at..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="period_end" value="<?= e(toLocalFormat(\Carbon\Carbon::now(), 'Y-m-d')) ?>" />
                </div>
            </div>

        </div>
    </form>
</div>



<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="donorCoversCostsTable">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Supporter</th>
                        <th>Description</th>
                        <th>Gateway</th>
                        <th>Method</th>
                        <th width="150">Amount</th>
                        <th width="150">DCC Amount</th>
                        <th width="150">Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
