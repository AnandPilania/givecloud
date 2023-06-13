<script>
    exportRecords = function (url) {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = url + '?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <?php if(sys_get('sponsorship_database_name')): ?><small><a href="#" data-placement="bottom" data-trigger="hover" data-toggle="popover" title="What are 'Local Sponsors'?" data-content="These are the list of sponsors that exist in your local sponsorship database. There could be many more sponsors in other databases, however, you cannot view them here."><i class="fa fa-question-circle"></i></a></small><?php endif; ?>

                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li><a onclick="exportRecords('/jpanel/sponsors.csv');"><i class="fa fa-fw fa-download"></i> All Sponsors</a></li>
                        <li><a onclick="exportRecords('/jpanel/sponsors_detailed.csv');"><i class="fa fa-fw fa-download"></i> All Sponsors with Children</a></li>
                    </ul>
                </div>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-child"></i> All <?= e(sys_get('syn_sponsorship_children')) ?></div>
                </div>

                <div class="row">
                    <div class="col-sm-5">
                        <div id="sponsorships_breakdown-chart" style="height:160px;"></div>
                        <script type="application/json" id="sponsorships_breakdown-chart-data"><?= dangerouslyUseHTML(json_encode($sponsorship_breakdown_stats)) ?></script>
                    </div>

                    <div class="col-xs-7 col-sm-7">
                        <div class="row">
                            <div class="col-xs-6 stat">
                                <div class="stat-value-bold"><?= e(number_format($total_sponsorship_stats->all,0)) ?></div>
                                <div class="stat-label no-wrap">All <?= e(sys_get('syn_sponsorship_children')) ?></diV>
                            </div>
                            <div class="col-xs-6 stat">
                                <div class="stat-value text-muted">
                                    <?= e(number_format($total_sponsorship_stats->not_sponsored,0)) ?>
                                </div>
                                <div class="stat-label">Unsponsored</diV>
                            </div>
                        </div>
                        <div class="row">
                            <?php if (sys_get('sponsorship_database_name')): ?>
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-bold text-success">
                                        <?= e(number_format($total_sponsorship_stats->local_sponsored,0)) ?>
                                    </div>
                                    <div class="stat-label">Locally</div>
                                </div>
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-bold text-success">
                                        <?= e(number_format($total_sponsorship_stats->sponsored-$total_sponsorship_stats->local_sponsored,0)) ?>
                                    </div>
                                    <div class="stat-label">Remotely</div>
                                </div>
                            <?php else: ?>
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-bold text-success">
                                        <?= e(number_format($total_sponsorship_stats->sponsored,0)) ?>
                                    </div>
                                    <div class="stat-label">Sponsored</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>

    <div class="col-lg-4  col-md-6 col-sm-12">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-user hidden-xs"></i> <?= e((sys_get('sponsorship_database_name')) ? 'Local ' : '') ?>Sponsorships</div>
                </div>

                <div class="row">
                    <div class="col-sm-5">
                        <div id="sponsors_breakdown-chart" style="height:160px;"></div>
                        <script type="application/json" id="sponsors_breakdown-chart-data"><?= dangerouslyUseHTML(json_encode($sponsors_breakdown_stats)) ?></script>
                    </div>

                    <div class="col-xs-7 col-sm-7">
                        <div class="row">
                            <div class="col-xs-6 stat">
                                <div class="stat-value-bold"><?= e(number_format($total_sponsors_stats->all,0)) ?></div>
                                <div class="stat-label no-wrap">All Sponsorships</diV>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="col-xs-6 stat" style="padding-left:0px">
                                    <div class="stat-value-bold text-success">
                                        <?= e(number_format($total_sponsors_stats->active,0)) ?>
                                    </div>
                                    <div class="stat-label">Active</div>
                                </div>
                                <div class="col-xs-6 stat" style="padding-left:0px">
                                    <div class="stat-value text-danger">
                                        <?= e(number_format($total_sponsors_stats->ended,0)) ?>
                                    </div>
                                    <div class="stat-label">Ended</diV>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="panel panel-default">

            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-credit-card"></i> Local Payments</div>
                </div>

                <div class="row">

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="row">
                            <div class="col-xs-12 stat">
                                <div class="stat-value-bold text-success"><?= e($recurring_payments_stats->active) ?></div>
                                <div class="stat-label no-wrap">Active Payments</diV>
                            </div>
                            <div class="col-xs-12 stat">
                                <div class="stat-value-bold text-success">
                                    <?= e(money($recurring_payments_stats->total)) ?>
                                </div>
                                <div class="stat-label no-wrap">Total Recurring Amount</diV>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="row">
                            <div class="col-xs-12 stat">
                                <div class="stat-value text-warning"><?= e($recurring_payments_stats->suspended) ?></div>
                                <div class="stat-label">Suspended</diV>
                            </div>
                            <div class="col-xs-12 stat">
                                <div class="stat-value text-danger"><?= e($recurring_payments_stats->cancelled) ?></div>
                                <div class="stat-label">Cancelled</diV>
                            </div>
                        </div>
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
                    <input type="text" class="form-control delay-filter" name="search" value="" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter sponsor records by:<br><i class='fa fa-check'></i> First &amp; Last Name<br>" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Billing period</label>

                <select class="form-control selectize" name="billing_period">
                    <option value="">Billing Period...</option>
                    <option value="Week">Weekly</option>
                    <option value="SemiMonth">Every 2 Weeks</option>
                    <option value="Month">Monthly</option>
                    <option value="Quarter">Quarterly</option>
                    <option value="SemiYear">Every 6 Months</option>
                    <option value="Year">Yearly</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Sponsor status</label>
                <select class="form-control selectize" name="sponsor_status">
                    <option value="">Sponsor Status...</option>
                    <option value="Active">Active</option>
                    <option value="Ended">Ended</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Started</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="sponsorship_start_from" value="" placeholder="Started From..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="sponsorship_start_to" value="" placeholder="Started To..." />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Ended</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="sponsorship_ended_from" value="" placeholder="Ended From..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="sponsorship_ended_to" value="" placeholder="Ended To..." />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Source</label>
                <select class="form-control selectize" name="source">
                    <option value="" selected>Source...</option>
                    <?php foreach(explode(',',sys_get('sponsorship_sources')) as $source): ?>
                        <option value="<?= e($source) ?>"><?= e($source) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Birth date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="birth_date_start" value="" placeholder="Birth Date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="birth_date_end" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Gender</label>
                <select class="form-control selectize" name="gender">
                    <option value="">Gender...</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Sponsor Count</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="sponsor_count_start" value="" placeholder="Sponsor Count..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="sponsor_count_end" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">On Web</label>
                <select class="form-control selectize" name="is_enabled">
                    <option value="">On Web...</option>
                    <option value="1">Live on Web</option>
                    <option value="0">Hidden from Web</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Sponsored</label>
                <select class="form-control selectize" name="is_sponsored">
                    <option value="">Sponsored...</option>
                    <option value="1">Sponsored</option>
                    <option value="0">Not Sponsored</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Recurring Payment Status</label>
                <select class="form-control selectize" name="recurring_payments_status" multiple>
                    <option value="">Any Status...</option>
                    <?php foreach(\Ds\Enums\RecurringPaymentProfileStatus::all() as $option): ?>
                        <option value="<?= e($option) ?>"><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Payment Option</label>
                <select class="form-control selectize" name="payment_option_group_id">
                    <option value="">Payment Option...</option>
                    <option value="0">[None]</option>
                    <?php foreach(\Ds\Domain\Sponsorship\Models\PaymentOptionGroup::all() as $option): ?>
                        <option value="<?= e($option->id) ?>"><?= e($option->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if(user()->can('sponsor.mature')): ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Maturity</label>
                    <select class="form-control selectize" name="is_mature">
                        <option value="">Maturity...</option>
                        <option value="1" <?= e((request()->filled('is_mature') && request('is_mature') == 1) ? 'selected' : '') ?> >Matured</option>
                        <option value="0" <?= e((request()->filled('is_mature') && request('is_mature') == 0) ? 'selected' : '') ?> >Not Matured</option>
                    </select>
                </div>
            <?php endif; ?>

            <?php foreach(\Ds\Domain\Sponsorship\Models\Segment::with('items')->get() as $segment): ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label"><?= e($segment->name) ?></label>
                    <?php if ($segment->type === 'multi-select' || $segment->type === 'advanced-multi-select'): ?>
                        <select name="segment_filters[<?= e($segment->id) ?>][]" class="selectize form-control" multiple="multiple" size="1" placeholder="<?= e($segment->name) ?>...">
                            <option></option>
                            <?php foreach($segment->items as $item): ?>
                                <option value="<?= e($item->id) ?>"><?= e($item->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input class="form-control delay-filter" name="segment_filters[<?= e($segment->id) ?>]" placeholder="<?= e($segment->name) ?>...">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="sponsors-list" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Name <small>Last, First</small></th>
                        <th><?= e(sys_get('syn_sponsorship_child')) ?></th>
                        <th>Started On</th>
                        <th>Source</th>
                        <th width="200">Recurring Txn</th>
                        <th>Ended On</th>
                        <th>Ended Reason</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
