
<script>
    function exportRecords() {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/reports/settlements.csv?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Settlement Batches
            <div style="display:inline-block;width:180px;margin-top:5px;vertical-align:top;">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control date" id="settlement_date" name="date" value="<?= e(fromLocalFormat($date, 'Y-m-d')) ?>" autocomplete="off">
                </div>
            </div>
            <input type="hidden" class="form-control" id="settlement_mode" name="settlement_mode" value="items">
            <div class="pull-right">
                <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

<div id="includes-external-transactions" class="alert alert-warning hidden">
    <i class="fa fa-exclamation-triangle mr-2"></i>
    This settlement batch includes transactions that originated outside of Givecloud.
</div>

<div class="row">
    <div class="col-md-5">
        <div class="panel panel-default">

            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 stat">
                        <div class="stat-value"><span data-dash="batch_date">&nbsp;</span></div>
                        <div class="stat-label">Batch Date</diV>
                    </div>

                    <div class="col-xs-6 stat">
                        <div class="stat-value"><span data-dash="batch_count">&nbsp;</span></div>
                        <div class="stat-label">Charges</diV>
                    </div>

                    <div class="col-xs-6 stat">
                        <div class="stat-value-bold">$<span data-dash="batch_total">&nbsp;</span></div>
                        <div class="stat-label">Total Desposit</diV>
                    </div>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
    <div class="col-md-7">
        <div class="panel panel-default">

            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 stat">
                        <div class="stat-value">&nbsp;</div>
                        <div class="stat-label">&nbsp;</diV>
                    </div>

                    <div class="col-xs-6 stat">
                        <div class="stat-value">&nbsp;</div>
                        <div class="stat-label">&nbsp;</diV>
                    </div>

                    <div class="col-xs-6 stat">
                        <div class="stat-value-bold">&nbsp;</div>
                        <div class="stat-label">&nbsp;</diV>
                    </div>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="settlements_table" class="table table-striped table-bordered table-hover responsive">
                <thead>
                    <tr>
                        <th width="90">Batch ID</th>
                        <th width="100">Response</th>
                        <th width="140">Ref #</th>
                        <th width="100">Amount</th>
                        <th>Description</th>
                        <th>Item Description</th>
                        <th>Supporter</th>
                        <th width="120">GL</th>
                        <th width="120">Campaign</th>
                        <th width="120">Solicit</th>
                        <th width="120">Sub Solicit</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
spaContentReady(function($) {
    var settlements_table = $('#settlements_table').DataTable({
        dom: 'rtpi',
        paging: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        stateSave: true,
        ordering: false,
        columns: [
            { data: 'batch_id' },
            { data: 'response' },
            { data: 'reference_number' },
            { data: 'amount' },
            { data: 'html_description' },
            { data: 'item_description' },
            { data: 'account' },
            { data: 'gl' },
            { data: 'campaign' },
            { data: 'solicit' },
            { data: 'sub_solicit' },
        ],
        ajax: {
            url: '/jpanel/reports/settlements.ajax',
            type: 'POST',
            data: function (d) {
                d.date = $('#settlement_date').val();
                d.mode = $('#settlement_mode').val();
            },
            dataSrc: function (json) {
                for (var i=0; i<json.data.length; i++) {
                    json.data[i].html_description = json.data[i].description.replace(
                        /Contribution #(\w+)/,
                        'Contribution <a href="<?= e(route('backend.orders.edit_without_id')) ?>?c=$1" target="_blank">#$1</a>'
                    );
                    json.data[i].html_description = json.data[i].html_description.replace(
                        /Transaction #(\d+)/,
                        'Transaction <a href="#" class="ds-txn" data-txn-id="$1">#$1</a>'
                    );
                }
                return json.data;
            },
        },
        drawCallback: function(d) {
            if (d.json.dash) {
                $.each(d.json.dash, function(key, val){
                    $('[data-dash="'+key+'"]').html(val);
                });
            }

            $('#includes-external-transactions').toggleClass('hidden', d.json.no_external_transactions)

            j.ui.table.init();
            return true;
        }
    });

    $('#settlement_date,#settlement_mode').change(function(){
        settlements_table.draw();
    });
});
</script>
