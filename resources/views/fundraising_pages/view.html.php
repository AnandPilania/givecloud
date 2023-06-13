<style>
    .page-header .label { position:relative; display:inline-block; font-size:16px; top:-6px; }
</style>


<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <span class="page-header-text"><?= e($pageTitle) ?></span>

            <?php if ($fundraising_page->status == 'suspended'): ?>
                <span class="label label-pill label-warning"><i class="fa fa-exclamation-circle"></i> Suspended</span>
            <?php elseif ($fundraising_page->status == 'closed'): ?>
                <span class="label label-pill label-outline label-default"><i class="fa fa-exclamation-circle"></i> Closed</span>
            <?php elseif ($fundraising_page->status == 'draft'): ?>
                <span class="label label-pill label-outline label-warning"><i class="fa fa-exclamation-circle"></i> Draft</span>
            <?php elseif ($fundraising_page->status != 'active'): ?>
                <span class="label label-pill label-outline label-warning"><?= e(ucwords($fundraising_page->status)) ?></span>
            <?php endif; ?>

            <div class="visible-xs-block"></div>

            <div class="pull-right">

                <?php if($fundraising_page->trashed()): ?>

                    <a href="#" target="_blank" class="btn btn-success btn-outline restore-page"><i class="fa fa-check"></i> Restore Page</a>

                <?php else: ?>

                    <?php if ($fundraising_page->status == 'suspended'): ?>
                        <a href="#" class="btn btn-success activate-page" data-toggle="tooltip" data-placement="bottom" title="Activate this page."><i class="fa fa-check fa-fw"></i> Activate</a>
                    <?php endif; ?>

                    <span data-toggle="tooltip" data-placement="bottom" title="Edit this page.">
                        <a href="#modal-edit-fundraiser" class="btn btn-info btn-outline" data-toggle="modal"><i class="fa fa-pencil fa-fw"></i> Edit</a>
                    </span>

                    <div class="btn-group" role="group" aria-label="...">
                        <?php if ($fundraising_page->status != 'suspended'): ?>
                            <a href="#" class="btn btn-warning btn-outline suspend-page" data-toggle="tooltip" data-placement="bottom" title="Suspend this page."><i class="fa fa-exclamation-circle fa-fw"></i></a>
                        <?php endif; ?>
                        <a href="#" class="btn btn-danger btn-outline delete-page" data-toggle="tooltip" data-placement="bottom" title="Delete this page."><i class="fa fa-trash fa-fw"></i></a>
                    </div>
                <?php endif; ?>

                <a onclick="exportRecords(); return false;" class="btn btn-default datatable-export"><i class="fa fa-download fa-fw"></i> Export</a>
            </div>

            <div class="text-secondary">
                Created by <a href="<?= e(route('backend.member.edit', $fundraising_page->member_organizer_id)) ?>"><i class="fa <?= e($fundraising_page->memberOrganizer->fa_icon) ?>"></i> <?= e($fundraising_page->memberOrganizer->display_name); ?></a>

                <?php if (! $fundraising_page->trashed()): ?>
                    <div class="pull-right text-right">
                        <div><a href="<?= e($fundraising_page->absolute_url) ?>" target="_blank"><?= e($fundraising_page->absolute_url) ?> <i class="fa fa-external-link"></i></a></div>
                        <?php if ($fundraising_page->guidelines_accepted_at): ?>
                            <div>
                                Guidelines first accepted <?= e(toLocalFormat($fundraising_page->guidelines_accepted_at)); ?> at <?= e(toLocalFormat($fundraising_page->guidelines_accepted_at, 'g:ia')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        </h1>
    </div>
</div>

<?php if ($fundraising_page->trashed()): ?>
    <div class="alert alert-danger">Deleted <?= e(toLocalFormat($fundraising_page->deleted_at)) ?> at <?= e(toLocalFormat($fundraising_page->deleted_at, 'g:ia')) ?>.</div>
<?php endif; ?>

<?php if(sys_get('fundraising_pages_requires_verify')): ?>
    <?php if($fundraising_page->memberOrganizer->isDenied): ?>
        <div class="alert alert-danger">This page is not live as the supporter was denied verified status for fundraising.</div>
    <?php endif ?>

    <?php if($fundraising_page->memberOrganizer->isPending || $fundraising_page->memberOrganizer->isUnverified): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
            <div class="flex">
                <div class="shrink-0">
                    <!-- Heroicon name: solid/exclamation -->
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        This page is not live as the supporter is not verified to fundraise.
                    </p>

                    <p class="text-sm text-yellow-700">
                        By verifying <strong><?= e($fundraising_page->memberOrganizer->display_name); ?></strong> you’ll also activate all of their pending Fundraising Pages (<?= e($pendingPages) ?>).
                    </p>

                    <div class="mt-4">
                        <div class="-mx-2 -my-1.5 flex">
                            <a href="<?= e(route('backend.supporter_verification.verify', $fundraising_page->memberOrganizer)) ?>" type="button" class="bg-yellow-50 px-2 py-1.5 rounded-md text-sm font-bold text-yellow-700 !hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-yellow-50 focus:ring-yellow-600">
                                Verify Supporter
                            </a>
                            <a href="<?= e(route('backend.supporter_verification.deny', $fundraising_page->memberOrganizer)) ?>" type="button" class="bg-yellow-50 px-2 py-1.5 rounded-md text-sm font-bold text-yellow-700 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-yellow-50 focus:ring-yellow-600">
                                Deny Supporter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
<?php endif; ?>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<!-- /.row -->
<div class="row">
    <div class="col-lg-8 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> 60 Day Activity
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="text-muted" style="height:257px;">Coming soon...</div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-shopping-cart fa-fw"></i> Donation Stats
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="progress progress-lg" style="margin-bottom:0px;">
                    <div class="progress-bar <?= e(($fundraising_page->progress_percent == 0) ? 'progress-bar-default' : '') ?>" role="progressbar" aria-valuenow="<?= e($fundraising_page->progress_percent * 100) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width:3em; width:<?= e($fundraising_page->progress_percent * 100) ?>%;">
                    <?= e(numeral($fundraising_page->progress_percent * 100)->format('0,0[.]0')) ?>%
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 stat">
                        <div class="stat-value-sm"><?= e(money($fundraising_page->amount_raised, $fundraising_page->currency_code)) ?></div>
                        <div class="stat-label">Raised</diV>
                    </div>

                    <div class="col-sm-6 stat text-right">
                        <div class="stat-value-sm"><strong><?= e(money($fundraising_page->goal_amount, $fundraising_page->currency_code)) ?></strong></div>
                        <div class="stat-label">Goal</diV>
                    </div>
                </div>

                <div class="row">

                    <hr style="margin-top:0px;">

                    <?php if ($fundraising_page->goal_deadline): ?>
                    <div class="col-sm-6 stat">
                        <div class="stat-value-sm"><?= e($fundraising_page->goal_deadline) ?></div>
                        <div class="stat-label">Deadline</diV>
                    </div>
                    <?php endif; ?>

                    <div class="col-sm-6 stat">
                        <div class="stat-value-sm"><?= e(number_format($fundraising_page->donation_count)) ?></div>
                        <div class="stat-label">Donations</diV>
                    </div>

                    <?php if ($fundraising_page->goal_deadline): ?>
                    <div class="col-sm-6 stat">
                        <div class="stat-value-sm"><?= e(($fundraising_page->goal_deadline->isPast()) ? 0 : $fundraising_page->goal_deadline->diffInDays()) ?></div>
                        <div class="stat-label">Days Remaining</diV>
                    </div>
                    <?php endif; ?>

                    <div class="col-sm-6 stat">
                        <div class="stat-value-sm">
                            <?php if ($fundraising_page->report_count == 0): ?>
                                <span class="text-success"><i class="fa fa-check"></i> <?= e(number_format($fundraising_page->report_count)) ?></span>
                            <?php else: ?>
                                <span class="text-danger"><i class="fa fa-exclamation-triangle"></i> <?= e(number_format($fundraising_page->report_count)) ?></span>
                                <small><a href="#abuse-reports-modal" data-toggle="modal"><i class="fa fa-external-link"></i></a></small>
                            <?php endif; ?>
                        </div>
                        <div class="stat-label">Abuse Reports</diV>
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
                <label class="form-label">Status</label>
                <select class="form-control" name="c" id="c" placeholder="Contribution Status">
                    <option value=""  <?= dangerouslyUseHTML((request('c') === '') ? 'selected="selected"' : '') ?>>Any Status</option>
                    <option value="1" <?= dangerouslyUseHTML((request('c') === '1') ? 'selected="selected"' : '') ?>>Complete Contributions</option>
                    <option value="0" <?= dangerouslyUseHTML((request('c') === '0') ? 'selected="selected"' : '') ?>>Incomplete Contributions</option>
                    <option value="2" <?= dangerouslyUseHTML((request('c') === '2') ? 'selected="selected"' : '') ?>>Refunded Contributions</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Ordered at</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                    <input type="text" class="form-control" name="ordered_at_str" value="<?= e($filters->ordered_at_str) ?>" placeholder="Ordered on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="ordered_at_end" value="<?= e($filters->ordered_at_end) ?>" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Price</label>
                <div class="input-group">
                    <div class="input-group-addon"><?= e(currency($fundraising_page->currency_code)->symbol) ?></div>
                    <input type="text" class="form-control" name="total_str" value="<?= e($filters->total_str) ?>" placeholder=">= Total" />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="total_end" value="<?= e($filters->total_end) ?>" placeholder="<= Total" />
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
            <table id="fundraising-page-orders" data-page-id="<?= e($fundraising_page->id) ?>" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th width="150">Contribution Date</th>
                        <th>Contribution#</th>
                        <th>Supporter</th>
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

<div class="modal fade" id="abuse-reports-modal" tabindex="-1" role="dialog" aria-labelledby="abuse-reports-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="abuse-reports-modal-label"><i class="fa fa-exclamation-triangle"></i> Abuse Reports</h4>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fundraising_page->reports as $report): ?>
                            <tr>
                                <td><?= e(toLocalFormat($report->reported_at)) ?> <small><?= e(toLocalFormat($report->reported_at, 'g:ia')) ?></small></td>
                                <td><?= e($report->reason) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-info" id="modal-edit-fundraiser" tabindex="-1" role="dialog" aria-labelledby="modal-edit-fundraiser-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal-edit-fundraiser-label"><i class="fa fa-pencil"></i> Edit</h4>
            </div>
            <form action="/jpanel/fundraising-pages/<?= e($fundraising_page->id) ?>/update" method="post">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <div class="modal-body">

                    <h4>Offset Progress</h4>
                    <p>Manually offset the amount raised and the number of donors to help account for donations processed in other systems.</p>

                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="" class="control-label">Amount</label>
                            <input type="number" class="form-control" id="" name="amount_raised_offset" value="<?= e(numeralFormat($fundraising_page->amount_raised_offset, '0[.]00')) ?>" placeholder="0.00" step=".01">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="control-label">Donors</label>
                            <input type="number" class="form-control" id="" name="donation_count_offset" value="<?= e($fundraising_page->donation_count_offset) ?>" placeholder="0">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

exportRecords = function () {
    var d = j.ui.datatable.filterValues('table.dataTable');
    window.location = '<?= e(route('backend.fundraising_pages/orders_csv', $fundraising_page)) ?>?' + $.param(d);
}

spaContentReady(function() {
    $('.suspend-page').click(function(ev){
        ev.preventDefault();

        $.confirm('Are you sure you want to suspend this page?', function(){
            window.location = '/jpanel/fundraising-pages/<?= e($fundraising_page->id) ?>/suspend';
        },'warning');
    });

    $('.activate-page').click(function(ev){
        ev.preventDefault();

        $.confirm('Are you sure you want to activate this page?', function(){
            window.location = '/jpanel/fundraising-pages/<?= e($fundraising_page->id) ?>/activate';
        },'warning');
    });

    $('.delete-page').click(function(ev){
        ev.preventDefault();

        $.confirm('Are you sure you want to delete this page?', function(){
            window.location = '/jpanel/fundraising-pages/<?= e($fundraising_page->id) ?>/destroy';
        },'danger');
    });

    $('.restore-page').click(function(ev){
        ev.preventDefault();

        $.confirm('Are you sure you want to restore this page?', function(){
            window.location = '/jpanel/fundraising-pages/<?= e($fundraising_page->id) ?>/restore';
        },'warning');
    });

    var ordersList = $('#fundraising-page-orders').DataTable({
        "dom": 'rtpi',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "order": [[ 1, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 0},
            { "orderable": false, "targets": 3},
            { "class": "text-left", "targets": 4},
            { "class": "text-center", "targets": 5},
            { "class": "text-right", "targets": 6},
            { "class": "text-right", "targets": 7}
        ],
        "stateSave": true,
        "ajax": {
            "url": "<?= e(route('backend.fundraising_pages/orders_json', '__ID__', false)) ?>".replace('__ID__', $('#fundraising-page-orders').data('page-id')),
            "type": "POST",
            "data": function (d) {
                fields = $('.datatable-filters').serializeArray();
                $.each(fields,function(i, field){
                    d[field.name] = field.value;
                })
            }
        },

        // colors/styles
        "fnRowCallback": function( nRow, aData ) {
            var refundAmt = aData[8];
            var iscomplete = aData[10];

            var $nRow = $(nRow); // cache the row wrapped up in jQuery

            if (iscomplete)
                $nRow.addClass('success');

            if (refundAmt > 0)
                $nRow.addClass('text-danger');

            return nRow;
        }
    });

    j.ui.datatable.enableFilters(ordersList);


    $('.datatable-filters input, .datatable-filters select').not(':hidden').each(function(i, input){
        if ($(input).data('datepicker'))
            $(input).on('changeDate', function () {
                ordersList.draw();
            });

        else
            $(input).change(function(){
                ordersList.draw();
            });
    });

    $('form.datatable-filters').on('submit', function(ev){
        ev.preventDefault();
    });
});
</script>
