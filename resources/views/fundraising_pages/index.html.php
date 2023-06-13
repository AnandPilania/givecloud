
<?php if (!sys_get('fundraising_pages_enabled') || \Ds\Models\FundraisingPage::websiteType()->count() == 0): ?>

<div class="feature-highlight">
    <img class="feature-img" src="/jpanel/assets/images/icons/file-sharing.svg">
    <h2 class="feature-title">Peer-to-Peer Fundraising Pages</h2>
    <p>Allow your donors to create their own fundraising pages for your cause.</p>

    <?php if(sys_get('fundraising_pages_enabled')): ?>
        <p class="text-success"><i class="fa fa-check"></i> You're all setup!<br>We're just waiting for a donor to create the first fundraising page.</p>
    <?php endif; ?>

    <div class="feature-actions">
        <?php if(!sys_get('fundraising_pages_enabled')): ?>
            <a href="/jpanel/settings/fundraising-pages" class="btn btn-lg btn-success btn-pill"><i class="fa fa-gear"></i> Setup Fundraising Pages</a>
        <?php endif; ?>
        <a href="https://help.givecloud.com/en/articles/2213441-peer-to-peer-fundraising-pages" target="_blank" class="btn btn-lg btn-outline btn-primary btn-pill" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>
    </div>
</div>

<?php else: ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?= e($pageTitle) ?>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a href="#" class="btn btn-default datatable-export"><i class="fa fa-download fa-fw"></i> Export</a>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title">30-Day Activity</div>
                </div>

                <div class="text-muted" style="min-height:132px;">Coming soon...</div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">

            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title">Open Pages</div>
                </div>

                <div class="row">

                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm"><?= e(money($stats['open_page_goal'])->format('$0,0[.]00')) ?></div>
                        <div class="stat-label">Open Goal</diV>
                    </div>

                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm text-bold">
                            <?= e(money($stats['open_page_amount_raised'])->format('$0,0[.]00')) ?>
                            <?php if ($stats['open_page_amount_raised']): ?>
                                <small class="text-muted"><?= e(number_format($stats['open_page_progress_percent'],1)) ?>%</small>
                            <?php endif; ?>
                        </div>
                        <div class="stat-label">Open Progress</diV>
                    </div>

                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm"><?= e(number_format($stats['open_page_count'])) ?></div>
                        <div class="stat-label">Open Pages</diV>
                    </div>

                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm">
                            <?php if($stats['open_page_reported_count'] > 0): ?>
                                <span class="text-danger text-bold"><i class="fa fa-exclamation-circle"></i> <?= e(number_format($stats['open_page_reported_count'])) ?></span>
                            <?php else: ?>
                                <span class="text-success text-bold"><i class="fa fa-check"></i> 0</span>
                            <?php endif; ?>
                        </div>
                        <div class="stat-label">Reported Pages</diV>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">

            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title">Closed Pages</div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm"><?= e(money($stats['closed_pages_goal'])->format('$0,0[.]00')) ?></div>
                        <div class="stat-label">Total Goal</diV>
                    </div>

                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm">
                            <?= e(money($stats['closed_pages_amount_raised'])->format('$0,0[.]00')) ?>
                            <?php if ($stats['closed_pages_amount_raised']): ?>
                                <small class="text-muted"><?= e(number_format($stats['closed_pages_progress_percent'],1)) ?>%</small>
                            <?php endif; ?>
                        </div>
                        <div class="stat-label">Total Raised</diV>
                    </div>

                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm"><?= e(number_format($stats['closed_pages_count'])) ?></div>
                        <div class="stat-label">Total Pages</diV>
                    </div>

                    <div class="col-xs-12 col-sm-6 stat">
                        <div class="stat-value-sm">
                            <?= e(number_format($stats['closed_pages_success_count'])) ?>
                            <?php if ($stats['closed_pages_success_count']): ?>
                                <small class="text-muted"><?= e(number_format($stats['closed_pages_success_percent'],1)) ?>%</small>
                            <?php endif; ?>
                        </div>
                        <div class="stat-label">Sucessful Pages</diV>
                    </div>

                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
</div>

<div class="row">
    <form class="datatable-filters">
        <input type="hidden" name="fA" value="<?= e(request('fA')); ?>">
        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">
            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control delay-filter" name="search" value="" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter fundraising pages by:<br><i class='fa fa-check'></i> Name, Description, Url<br><i class='fa fa-check'></i> Author Name, Team Member Names" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Status</label>
                <select class="form-control" name="status">
                    <option value="" selected>Active</option>
                    <option value="active-abuse">Active w/ Abuse Reports</option>
                    <?php if(sys_get('fundraising_pages_requires_verify')) : ?>
                        <option value="denied">Denied</option>
                    <?php endif; ?>
                    <option value="draft">Draft</option>
                    <?php if(sys_get('fundraising_pages_requires_verify')) : ?>
                    <option value="pending">Pending</option>
                    <?php endif; ?>
                    <option value="cancelled">Cancelled</option>
                    <option value="suspended">Suspended</option>
                    <option value="closed">Closed</option>
                    <option value="any">Any Status</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Progress</label>
                <select class="form-control" name="progress">
                    <option value="">Any Progress</option>
                    <option value="goal-short">Goal Short</option>
                    <option value="goal-reached">Goal Reached</option>
                    <option value="goal-exceeded">Goal Exceeded</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Category</label>
                <select class="form-control" name="category" placeholder="Any Category">
                    <option value="">Any Category</option>
                    <?php foreach(explode(',',sys_get('fundraising_pages_categories')) as $category): ?>
                        <option value="<?= e($category) ?>" <?= dangerouslyUseHTML((request('category','') == $category)?'selected="selected"':'') ?>><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Page Type</label>
                <select class="form-control" name="product_id" placeholder="Page Type">
                    <option value="">Any Page Type</option>
                    <?php foreach($page_types as $page_type): ?>
                        <option value="<?= e($page_type->id) ?>" <?= dangerouslyUseHTML((request('product_id','') == $page_type->id)?'selected="selected"':'') ?>><?= e($page_type->fundraising_page_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Created</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="created_start" value="" placeholder="Created..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="created_end" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Activated</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="activated_start" value="" placeholder="Activated..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="activated_end" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Deadline</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="deadline_start" value="" placeholder="Deadline..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="deadline_end" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Fundraiser</label>
                <select class="form-control" name="fundraiser" placeholder="Fundraiser">
                    <option value="">Any Fundraiser</option>
                    <?php foreach($fundraisers as $fundraiser): ?>
                        <option value="<?= e($fundraiser->id) ?>" <?= e(volt_selected($fundraiser->id, request('fundraiser'))); ?>><?= e($fundraiser->display_name) ?></option>
                    <?php endforeach; ?>
                </select>
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
            <table id="fundraising-pages-list" class="table table-v2 table-striped table-hover responsive">
                <thead>
                    <tr>
                        <th data-orderable="false" width="16"></th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Goal</th>
                        <th>Donations</th>
                        <th>Raised</th>
                        <th>Time Elapsed</th>
                        <th>Deadline</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    #fundraising-pages-list tr td .title .label { margin-top:5px; margin-right:3px; }
    #fundraising-pages-list tr td .title .label.label-outline { margin-top:4px; }
</style>
<script>
spaContentReady(function() {
    var payments_list = $('#fundraising-pages-list').DataTable({
        "dom": 'rtpi',
        "sErrMode":'throw',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "order": [[ 1, "asc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 0, "class" : "text-left" },
            { "orderable": true, "targets": 1, "class" : "text-left" },
            { "orderable": true, "targets": 2, "class" : "text-left" },
            { "orderable": false, "targets": 3, "class" : "text-left" },
            { "orderable": true, "targets": 4, "class" : "text-right" },
            { "orderable": true, "targets": 5, "class" : "text-center" },
            { "orderable": true, "targets": 6, "class" : "text-center" },
            { "orderable": false, "targets": 7, "class" : "text-center" },
            { "orderable": true, "targets": 8, "class" : "text-left" }
        ],
        "ajax": {
            "url": "/jpanel/fundraising-pages.json",
            "type": "POST",
            "data": function (d) {
                d.search = $('input[name=search]').val();
                d.status = $('select[name=status]').val();
                d.product_id = $('select[name=product_id]').val();
                d.progress = $('select[name=progress]').val();
                d.category = $('select[name=category]').val();
                d.created_start = $('input[name=created_start]').val();
                d.created_end = $('input[name=created_end]').val();
                d.activated_start = $('input[name=activated_start]').val();
                d.activated_end = $('input[name=activated_end]').val();
                d.deadline_start = $('input[name=deadline_start]').val();
                d.deadline_end = $('input[name=deadline_end]').val();
                d.verified_status = $('select[name=verified_status]').val();
                d.fundraiser = $('select[name=fundraiser]').val();
            }
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

            j.ui.datatable.formatRows($('#payments-listing'));
            return true;
        },

        "initComplete" : function(){
            j.ui.datatable.formatTable($('#payments-listing'));
        }
    });

    j.ui.datatable.enableFilters(payments_list);

    $('.datatable-export').on('click', function(ev){
        ev.preventDefault();

        var data = j.ui.datatable.filterValues('#fundraising-pages-list');
        window.location = '/jpanel/fundraising-pages.csv?'+$.param(data);
    });
});
</script>

<?php endif; ?>
