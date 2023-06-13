
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?= e($pageTitle) ?>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a href="/jpanel/pledges/campaigns/new/modal" data-toggle="ajax-modal" class="btn btn-success"><i class="fa fa-plus"></i> Add</a>
                <a href="https://help.givecloud.com/en/articles/2841187-pledges-promises-to-give" target="_blank" class="btn btn-default" rel="noreferrer"><i class="fa fa-book fa-fw"></i> Learn More</a>
            </div>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<div class="row hide">
    <form class="datatable-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control delay-filter" name="search" value="" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter fundraising pages by:<br><i class='fa fa-check'></i> Name, Description, Url<br><i class='fa fa-check'></i> Author Name, Team Member Names" />
                </div>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="pledge-type-list" class="table table-striped table-hover responsive">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Name</th>
                        <th>Starts</th>
                        <th>Ends</th>
                        <th>Tracking</th>
                        <th>Pledges</th>
                        <th>Total Amount</th>
                        <th>Funded Amount</th>
                        <th width="120">Progress</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    #pledge-type-list tr td .title .label { margin-top:5px; margin-right:3px; }
    #pledge-type-list tr td .title .label.label-outline { margin-top:4px; }
</style>
<script>
spaContentReady(function() {
    var pledges_list = $('#pledge-type-list').DataTable({
        "dom": 'rtpi',
        "sErrMode":'throw',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "order": [[ 1, "asc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 0, "class" : "text-center" },
            { "orderable": true, "targets": 1, "class" : "text-left" },
            { "orderable": true, "targets": 2, "class" : "text-left" },
            { "orderable": true, "targets": 3, "class" : "text-left" },
            { "orderable": true, "targets": 4, "class" : "text-left" },
            { "orderable": true, "targets": 5, "class" : "text-center" },
            { "orderable": true, "targets": 6, "class" : "text-right" },
            { "orderable": true, "targets": 7, "class" : "text-right" },
            { "orderable": true, "targets": 8, "class" : "text-center" }
        ],
        "ajax": {
            "url": "/jpanel/pledges/campaigns.json",
            "type": "POST",
            "data": function (d) {
                d.search = $('input[name=search]').val();
            }


                /*d.status = $('select[name=status]').val();
                d.product_id = $('select[name=product_id]').val();
                d.progress = $('select[name=progress]').val();
                d.category = $('select[name=category]').val();
                d.created_start = $('input[name=created_start]').val();
                d.created_end = $('input[name=created_end]').val();*/
        },
        "stateSave": false,


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
            /*$('.sparkline').each(function(i, el){
                $el = $(el);
                console.log($el.sparkline);
                $el.sparkline(
                    $el.data('spark').split(','),
                    {
                        type: 'line',
                        height:55,
                        barColor: '#999',
                        spotColor: false,
                        minSpotColor: false,
                        maxSpotColor: false,
                        highlightSpotColor: '#337ab7',
                        highlightLineColor: false
                    }
                );
            });*/

            j.ui.datatable.formatRows($('#pledge-type-list'));
            $.ajaxModals();
            return true;
        },

        "initComplete" : function(){
            j.ui.datatable.formatTable($('#pledge-type-list'));
        }
    });

    j.ui.datatable.enableFilters(pledges_list);

    $('.datatable-export').on('click', function(ev){
        ev.preventDefault();

        var data = j.ui.datatable.filterValues('#pledge-type-list');
        window.location = '/jpanel/pledges.csv?'+$.param(data);
    });
});
</script>
