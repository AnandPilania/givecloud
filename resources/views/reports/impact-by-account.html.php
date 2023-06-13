<script>
    function exportRecords() {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '<?= e(route('backend.reports.impact_by_supporter.export')) ?>?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Impact by Supporter
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
                    <input type="text" class="form-control" name="search" id="filterSearch" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter Supporters by:<br><i class='fa fa-check'></i> Description" />
                </div>
            </div>

        </div>
    </form>
</div>



<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="donorImpactTable">
                <thead>
                    <tr>
                        <th colspan="2"></th>
                        <th colspan="6" class="text-center">Personal Lifetime Totals</th>
                        <th colspan="4" class="text-center">Secondary Impact Lifetime Totals</th>
                    </tr>
                    <tr>
                        <th width="16"></th>
                        <th>Display Name</th>
                        <th>Donation Amount</th>
                        <th>Donation Count</th>
                        <th>Purchase Amount</th>
                        <th>Purchase Count</th>
                        <th>Fundraising Amount</th>
                        <th>Fundraising Count</th>
                        <th>Donation Amount</th>
                        <th>Donation Count</th>
                        <th>Site Visits</th>
                        <th>Email Signups</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
