<?php if (\Ds\Models\Pledge::count() == 0): ?>

<div class="feature-highlight">
    <img class="feature-img" src="/jpanel/assets/images/icons/thermometer.svg">
    <h2 class="feature-title">Track Donor Pledges</h2>
    <p>Allow your donors to create a pledge and let Givecloud automatically track their progress.</p>
    <div class="feature-actions">
        <?php $campaign_count = \Ds\Models\PledgeCampaign::count(); ?>
        <?php if($campaign_count == 0): ?>
            <?php if (user()->can('pledgecampaigns')): ?>
                <a href="/jpanel/pledges/campaigns/new/modal" data-toggle="ajax-modal" class="btn btn-lg btn-success btn-pill"><i class="fa fa-plus"></i> Create a Pledge Campaign</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="/jpanel/pledges/new/modal" data-toggle="ajax-modal" class="btn btn-lg btn-success btn-pill"><i class="fa fa-plus"></i> Add a Pledge</a>
        <?php endif; ?>

        <a href="https://help.givecloud.com/en/articles/2841187-pledges-promises-to-give" target="_blank" class="btn btn-lg btn-outline btn-primary btn-pill" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>

        <?php if (user()->can('pledgecampaigns') && $campaign_count > 0): ?>
            <div class="text-muted top-gutter" style="font-size:14px;"><a href="/jpanel/pledges/campaigns"><i class="fa fa-gear"></i> Manage Pledge Campaigns</a></div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?= e($pageTitle) ?>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a href="/jpanel/pledges/new/modal" data-toggle="ajax-modal" class="btn btn-success"><i class="fa fa-plus"></i> Add</a>

                <a href="https://help.givecloud.com/en/articles/2841187-pledges-promises-to-give" target="_blank" class="btn btn-default" rel="noreferrer"><i class="fa fa-book fa-fw"></i> Learn More</a>
                <?php if (user()->can('pledgecampaigns')): ?>
                    <a href="/jpanel/pledges/campaigns" class="btn btn-default"><i class="fa fa-gear fa-fw"></i> Campaigns</a>
                <?php endif; ?>
                <a href="#" class="btn btn-default datatable-export"><i class="fa fa-download fa-fw"></i> Export</a>
            </div>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<div class="row">
    <form class="datatable-filters">

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
                    <option value="">Any Status</option>
                    <option value="unfunded">Unfunded</option>
                    <option value="underfunded">Underfunded</option>
                    <option value="funded">Funded</option>
                    <option value="overfunded">Overfunded</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="created_a" value="" placeholder="Date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="created_b" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Campaigns</label>
                <select class="form-control selectize" name="campaigns" id="campaigns" size="1" multiple placeholder="Any Campaign" data-allow-empty="true">
                    <option></option>
                    <?php foreach($campaigns as $campaign): ?>
                        <option value="<?= e($campaign->id) ?>" <?= e(volt_selected($campaign->id, request('campaigns'))); ?>><?= e($campaign->name) ?></option>
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
            <table id="pledges-list" class="table table-v2 table-striped table-hover responsive">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Campaign</th>
                        <th>Supporter</th>
                        <th width="90">Status</th>
                        <th width="100">Payments</th>
                        <th>Funded</th>
                        <th>Total</th>
                        <th width="120">Date</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    #pledges-list tr td .title .label { margin-top:5px; margin-right:3px; }
    #pledges-list tr td .title .label.label-outline { margin-top:4px; }
</style>
<script>
spaContentReady(function() {
    var pledges_list = $('#pledges-list').DataTable({
        "dom": 'rtpi',
        "sErrMode":'throw',
        "iDisplayLength" : 50,
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "order": [[ 7, 'desc' ]],
        "columnDefs": [
            { "orderable": false, "targets": 0, "class" : "text-center" },
            { "orderable": true, "targets": 1, "class" : "text-left" },
            { "orderable": true, "targets": 2, "class" : "text-left" },
            { "orderable": true, "targets": 3, "class" : "text-left" },
            { "orderable": true, "targets": 4, "class" : "text-center" },
            { "orderable": true, "targets": 5, "class" : "text-right" },
            { "orderable": true, "targets": 6, "class" : "text-right" },
            { "orderable": true, "targets": 7, "class" : "text-left" }
        ],
        "ajax": {
            "url": "/jpanel/pledges.json",
            "type": "POST",
            "data": function (d) {
                d.search = $('input[name=search]').val();
                d.status = $('select[name=status]').val();
                d.created_a = $('input[name=created_a]').val();
                d.created_b = $('input[name=created_b]').val();
                d.campaigns = $('select[name=campaigns]').val();
            }
        },
        "stateSave": false,

        "drawCallback" : function(){
            j.ui.datatable.formatRows($('#pledges-list'));
            $.ajaxModals();
            return true;
        },

        "initComplete" : function(){
            j.ui.datatable.formatTable($('#pledges-list'));
        }
    });

    j.ui.datatable.enableFilters(pledges_list);

    $('.datatable-export').on('click', function(ev){
        ev.preventDefault();

        var data = j.ui.datatable.filterValues('#pledges-list');
        window.location = '/jpanel/pledges.csv?'+$.param(data);
    });
});
</script>

<?php endif; ?>
