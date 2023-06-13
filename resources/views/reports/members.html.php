<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>
            <div class="visible-xs-block"></div>
            <div class="pull-right">
                <a href="#" class="btn btn-default datatable-export"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <form class="datatable-filters" id="memberships-report-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter Memberships by:<br><i class='fa fa-check'></i> Supporter" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Group</label>
                <select class="form-control" name="group">
                    <option value="">Any Group</option>
                    <?php foreach($memberships as  $group): ?>
                    <option value="<?= e($group->id) ?>" <?= e(volt_selected(request('group'), $group->id)); ?>><?= e($group->name) ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Status</label>
                <select class="form-control" name="status">
                    <option value="">Any Status</option>
                    <option value="active" <?= e(volt_selected(request('status'), 'active')); ?>>Active</option>
                    <option value="expired" <?= e(volt_selected(request('status'), 'expired')); ?>>Expired</option>
                    <option value="expiring" <?= e(volt_selected(request('status'), 'expiring')); ?>>Expiring in the next 30 days</option>
                    <option value="recently_expired" <?= e(volt_selected(request('status'), 'recently_expired')); ?>>Expired in the last 30 days</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Start Date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="startDateAfter" value="<?= e(request('startDateAfter')) ?>" placeholder="Start date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="startDateBefore" value="<?= e(request('startDateBefore')) ?>" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">End Date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="endDateAfter" value="<?= e(request('endDateAfter')) ?>" placeholder="End date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="endDateBefore" value="<?= e(request('endDateBefore')) ?>" />
                </div>
            </div>

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>


<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover" id="memberships-list">
        <thead>
            <tr>
                <th data-orderable="false" width="16"></th>
                <th>Name</th>
                <th>Email</th>
                <th>Group Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
spaContentReady(function() {
    var memberships_table = $('#memberships-list').DataTable({
        "dom": 'rtpi',
        "sErrMode":'throw',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "order": [[ 4, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 0, "class" : "text-center" },
            { "orderable": true, "targets": 1, "class" : "text-left" },
            { "orderable": true, "targets": 2, "class" : "text-left" },
            { "orderable": true, "targets": 3, "class" : "text-left" },
            { "orderable": true, "targets": 4, "class" : "text-left" },
            { "orderable": true, "targets": 5, "class" : "text-left" },
            { "orderable": false, "targets": 6, "class" : "text-left" },
        ],
        "stateSave": false,
        "ajax": {
            "url": "<?= e(route('backend.reports.members.ajax')) ?>",
            "type": "POST",
            "data": function (d) {
                var filters = {};
                _.forEach($('.datatable-filters').serializeArray(), function(field) {
                    filters[field.name] = filters[field.name] ? filters[field.name] + ',' + field.value : field.value;
                });
                _.forEach(filters, function(value, key) {
                    d[key] = value;
                });
                j.filtersToQueryString(filters);
            }
        },
        // colors/styles
        "fnRowCallback": function( nRow, aData ) {
            var captured = aData[1],
                $nRow = $(nRow);

            if (!captured) {
                $nRow.addClass('text-muted');
            }

            return nRow;
        },
        "drawCallback" : function(d){
            $('#aggregate_html').html(d.json.aggregate_html);
            j.ui.datatable.formatRows($('#order-list'));
            return true;
        },
        "initComplete" : function(){
            j.ui.datatable.formatTable($('#order-list'));
        }
    });

    j.ui.datatable.enableFilters(memberships_table);


    $('.datatable-filters input, .datatable-filters select').not(':hidden').add('select.selectize').each(function(i, input){

        if ($(input).data('datepicker'))
            $(input).on('changeDate', function () {
                memberships_table.draw();
            });

        else if ($(input).hasClass('selectize'))
            $(input).selectize().on('change', function () {
                memberships_table.draw();
            });

        else
            $(input).change(function(){
                memberships_table.draw();
            });
    });



    $('.datatable-export').on('click', function(ev){
        ev.preventDefault();

        var data = _.omitBy(j.ui.datatable.filterValues('#memberships-list'), function(value) {
            return !value;
        });

        window.location = '<?= e(route('backend.reports.members.export')) ?>?'+$.param(data);
    });

    $('form.datatable-filters').on('submit', function(ev){
        ev.preventDefault();
    });
});
</script>
