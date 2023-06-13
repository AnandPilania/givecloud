
<script>
    exportRecords = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/reports/referral_sources.csv?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($title) ?>
            <div class="visible-xs-block"></div>

            <?php if(sys_get('referral_sources_isactive')): ?>
                <div class="pull-right">
                    <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
                </div>
            <?php endif; ?>
        </h1>
    </div>
</div>

<?php if(!sys_get('referral_sources_isactive')): ?>

<div class="text-muted text-center top-gutter">
    <h2>"How'd you hear about us?"</h2>
    <p>Track how people heard about your organization, run insightful reports and optionally export the data.</p>
    <a href="<?= e(route('backend.settings.supporters')) ?>" class="btn btn-primary btn-sm"><i class="fa fa-gear"></i> Manage Settings</a>
</div>

<?php else: ?>

<div class="row">
    <div class="col-lg-8 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> 12 Month Revenue
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="text-muted" style="height:160px;"><p>Coming Soon</p></div>
                <!--<div class="row">
                    <div id="referral-sources-90day-sales" style="height:160px;">Coming Soon</div>
                </div>-->
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-shopping-cart fa-fw"></i> Total Sales
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="row">
                    <div id="referral-sources-total-sales" style="height:160px;" data-chart-data="<?= e(json_encode($total_sales), ENT_QUOTES, 'UTF-8') ?>"></div>
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
                    <input type="text" class="form-control" name="search" id="filterSearch" value="" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter Referral Sources by:<br><i class='fa fa-check'></i> Source Name" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Ordered on</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="ordered_at_str" value="" placeholder="Ordered on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="ordered_at_end" value="" />
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
            <table class="table table-striped table-bordered table-hover" id="referral-sources-listing">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th width="120">First Sale</th>
                        <th width="120">Last Sale</th>
                        <th width="120">Sales</th>
                        <!--<th width="150">Avg Amount</th>
                        <th width="150">Total Amount</th>-->
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
spaContentReady(function() {
    Morris.Donut({
        element   : 'referral-sources-total-sales',
        data      : $('#referral-sources-total-sales').data('chart-data'),
        colors    : ['#8064A2','#F79646','#4F81BD','#C0504D','#9BBB59','#2C4D75','#4BACC6'],
        resize    : true
    });

    var referral_sources_list_table = $('#referral-sources-listing').DataTable({
        "dom": 'rtpi',
        "sErrMode":'throw',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "order": [[ 3, "desc" ]],
        "columnDefs": [
            { "orderable": true, "targets": 0, "class" : "text-left" },
            { "orderable": true, "targets": 1, "class" : "text-left" },
            { "orderable": true, "targets": 2, "class" : "text-left" },
            { "orderable": true, "targets": 3, "class" : "text-center" }

            /*,
            { "orderable": true, "targets": 4, "class" : "text-right" },
            { "orderable": true, "targets": 5, "class" : "text-right" }*/
        ],
        "stateSave": true,
        "ajax": {
            "url": "/jpanel/reports/referral_sources.ajax",
            "type": "POST",
            "data": function (d) {
                d.search         = $('input[name=search]').val();
                d.ordered_at_str = $('input[name=ordered_at_str]').val();
                d.ordered_at_end = $('input[name=ordered_at_end]').val();
            }
        },

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
            j.ui.datatable.formatRows($('#referral-sources-listing'));
            return true;
        },

        "initComplete" : function(){
            j.ui.datatable.formatTable($('#referral-sources-listing'));
        }
    });

    j.ui.datatable.enableFilters(referral_sources_list_table);
});
</script>

<?php endif; ?>
