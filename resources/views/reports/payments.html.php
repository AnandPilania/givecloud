
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                <?= e($pageTitle) ?>
                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    <?php if (feature('givecloud_pro')): ?>
                    <a href="/jpanel/reports/payments-old" class="btn btn-info btn-outline">View Old Report</a>
                    <?php endif; ?>

                    <a href="#" class="btn btn-default datatable-export"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
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
                        <input type="text" class="form-control" name="fk" id="k" value="<?= e(request('fk')) ?>" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter payments by:<br><i class='fa fa-check'></i> Name on card/account<br><i class='fa fa-check'></i> Last 4 Digits<br><i class='fa fa-check'></i> Reference info." />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Attempted</label>
                    <div class="input-group input-daterange-pretty">
                        <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                        <input type="text" class="form-control" name="fc1" value="<?= e(request()->has('fc1') ? request('fc1') : $firstOfYear) ?>" placeholder="Attempted on..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="fc2" value="<?= e(request()->has('fc2') ? request('fc2') : $today) ?>" />
                    </div>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">One-time & Recurring</label>
                    <select class="form-control" name="fo">
                        <option value="">One-Time &amp; Recurring</option>
                        <option value="onetime" <?= e((request('fo') == 'onetime') ? 'selected' : '') ?>>One-Time Only</option>
                        <option value="recurring" <?= e((request('fo') == 'recurring') ? 'selected' : '') ?>>Recurring Only</option>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="fp">
                        <option value="" <?= e(request()->isNotFilled('fp') ? 'selected' : '') ?>>Any Status</option>
                        <option value="paid" <?= e((request('fp') == 'paid' || request()->missing('fp')) ? 'selected' : '') ?>>Paid (inc Partially Refunded) Only</option>
                        <option value="refunded" <?= e((request('fp') == 'refunded') ? 'selected' : '') ?>>Refunded Only</option>
                        <option value="failed" <?= e((request('fp') == 'failed') ? 'selected' : '') ?>>Failed Only</option>
                        <option value="spam" <?= e((request('fp') == 'spam') ? 'selected' : '') ?>>Spam/Fraud Only</option>
                    </select>
                </div>

                <!--<div class="form-group col-md-3 col-sm-6 col-xs-6 hide more-field">
                    <input class="form-control" name="fa" placeholder="Amount" data-popover-bottom="Filter by the amount of the payment. You can filter on the exact amount or in ranges. For example: <br><strong>55</strong> (Exactly $50)<br><strong>&gt;55</strong> (More than $55)<br><strong>&lt;55</strong> (Less than $55)<br><strong>50-100</strong> (Between $50 and $100)">
                </div>-->

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Gateway</label>
                    <select class="form-control" name="fg">
                        <option value="">Any Gateway</option>
                        <?php foreach(\Ds\Models\Payment::getDistinctValuesOf('gateway_type') as $g): ?>
                            <option value="<?= e($g) ?>" <?= e((request('fg') == $g) ? 'selected' : '') ?>><?= e(ucfirst($g)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Type</label>
                    <select class="form-control" name="ft">
                        <option value="">Any Type</option>
                        <?php foreach($paymentTypes as $g => $i): ?>
                            <option value="<?= e($g) ?>" <?= e((request('ft') == $g) ? 'selected' : '') ?>><?= e($i) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Card Brand</label>
                    <select class="form-control" name="fm">
                        <option value="">Any Card Brand</option>
                        <?php foreach(\Ds\Models\Payment::getDistinctValuesOf('card_brand') as $g): ?>
                            <option value="<?= e($g) ?>" <?= e((request('fm') == $g) ? 'selected' : '') ?>><?= e(ucwords(strtolower($g))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Bank Name</label>
                    <select class="form-control" name="fb">
                        <option value="">Any Bank Name</option>
                        <option value="unknown">Unknown</option>
                        <?php foreach(\Ds\Models\Payment::getDistinctValuesOf('bank_name') as $g): ?>
                            <option value="<?= e($g) ?>" <?= e((request('fb') == $g) ? 'selected' : '') ?>><?= e(ucwords(strtolower($g))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">AVS/CVC Verification</label>
                    <select class="form-control" name="fav">
                        <option value="">Any AVS/CVC Verification</option>
                        <optgroup label="Overall Status">
                            <option value="pass" <?= e((request('fav') == 'pass') ? 'selected' : '') ?>>Pass</option>
                            <option value="fail" <?= e((request('fav') == 'fail') ? 'selected' : '') ?>>Fail</option>
                            <option value="unavailable" <?= e((request('fav') == 'unavailable') ? 'selected' : '') ?>>Not Available</option>
                        </optgroup>
                        <optgroup label="Messages">
                            <option value="bad_cvc" <?= e((request('fav') == 'bad_cvc') ? 'selected' : '') ?>>Bad CVC</option>
                            <option value="no_cvc" <?= e((request('fav') == 'no_cvc') ? 'selected' : '') ?>>No CVC</option>
                            <option value="bad_address" <?= e((request('fav') == 'bad_address') ? 'selected' : '') ?>>Bad Address</option>
                            <option value="no_address" <?= e((request('fav') == 'no_address') ? 'selected' : '') ?>>No Address</option>
                            <option value="bad_zip" <?= e((request('fav') == 'bad_zip') ? 'selected' : '') ?>>Bad ZIP</option>
                            <option value="no_zip" <?= e((request('fav') == 'no_zip') ? 'selected' : '') ?>>No ZIP</option>
                        </optgroup>
                    </select>
                </div>

                <?php /*<div class="form-group col-md-3 col-sm-6 col-xs-6 hide more-field">
                    <select class="form-control" name="fr">
                        <option value="*">Any Referral Source</option>
                    </select>
                </div>*/ ?>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Captured On</label>
                    <div class="input-group input-daterange-pretty">
                        <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                        <input type="text" class="form-control" name="fd1" value="<?= e(request('fd1')) ?>" placeholder="Captured on..." />
                        <span class="input-group-addon">to</span>
                        <input type="text" class="form-control" name="fd2" value="<?= e(request('fd2')) ?>" />
                    </div>
                </div>

                <?php if (count($currencies) > 1) { ?>
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label">Currency</label>
                        <select class="form-control selectize" name="cc" placeholder="Currency">
                            <option value=""></option>
                            <?php foreach($currencies as $currency): ?>
                            <option value="<?= e($currency->code) ?>" <?= e((request('cc') == $currency->code) ? 'selected' : '') ?>><?= e($currency->code) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Failure Reason</label>
                    <select class="form-control" name="ff">
                        <option value="">Any Failure Reason</option>
                        <option value="unknown">Unknown</option>
                        <?php foreach(\Ds\Models\Payment::getDistinctValuesOf('failure_message') as $g): ?>
                            <option value="<?= e($g) ?>" <?= e((request('ff') == $g) ? 'selected' : '') ?>><?= e(ucfirst(strtolower($g))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">IP Country</label>
                    <select class="form-control" name="foi">
                        <option value="">Any IP Country</option>
                        <?php foreach(\Ds\Models\Order::getDistinctValuesOf('ip_country') as $g): ?>
                            <option value="<?= e($g) ?>"><?= e(cart_countries()[$g]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (feature('givecloud_pro')): ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Supporter Type</label>
                    <select class="selectize form-control" name="fat" placeholder="Any Supporter Type...">
                        <option></option>
                        <?php foreach ($account_types as $account_type): ?>
                            <option value="<?= e($account_type->id) ?>" <?= e(volt_selected($account_type->id, request('fat'))); ?>><?= e($account_type->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (feature('fundraising_forms')): ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Fundraising Forms</label>
                    <select class="form-control selectize auto-height" multiple name="fundraising_forms" size="1" placeholder="Any Fundraising Form...">
                        <option></option>
                        <?php foreach ($fundraisingForms as $hashid => $name): ?>
                        <option value="<?= e($hashid) ?>" <?= e(volt_selected($hashid, explode(',', request('fundraising_forms')))) ?>> <?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (feature('membership')): ?>
                    <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                        <label class="form-label"><?= e(sys_get('syn_group')) ?></label>
                        <select class="selectize form-control" name="fmm" placeholder="Any <?= e(sys_get('syn_group')) ?>...">
                            <option></option>
                            <?php foreach ($memberships as $membership): ?>
                                <option value="<?= e($membership->id) ?>" <?= e(volt_selected($membership->id, request('membership_id'))); ?>><?= e($membership->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (feature('referral_sources_isactive')): ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Referral Source</label>
                    <select class="form-control selectize-tag" placeholder="Any Referral Source..." name="for">
                        <option></option>
                        <?php foreach(explode(',',sys_get('referral_sources_options')) as $source): ?>
                            <option value="<?= e($source) ?>" <?= dangerouslyUseHTML((request('fR','') == $source)?'selected="selected"':'') ?>><?= e($source) ?></option>
                        <?php endforeach; ?>
                        <?php if (sys_get('referral_sources_other')): ?>
                            <option value="Other" <?= dangerouslyUseHTML((request('fR','') == "Other")?'selected="selected"':'') ?>>Other</option>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Tracking Source</label>
                    <select class="form-control selectize-tag" placeholder="Any Tracking Source..." name="fots">
                        <option></option>
                        <?php foreach(\Ds\Models\Order::getDistinctValuesOf('tracking_source') as $g): ?>
                            <option value="<?= e($g) ?>"><?= e($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Tracking Medium</label>
                    <select class="form-control selectize-tag" placeholder="Any Tracking Medium..." name="fotm">
                        <option></option>
                        <?php foreach(\Ds\Models\Order::getDistinctValuesOf('tracking_medium') as $g): ?>
                            <option value="<?= e($g) ?>"><?= e($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-3 col-sm-6 col-xs-6 hide more-field">
                    <label class="form-label">Tracking Campaign</label>
                    <select class="form-control selectize-tag" placeholder="Any Tracking Campaign..." name="fotc">
                        <option></option>
                        <?php foreach(\Ds\Models\Order::getDistinctValuesOf('tracking_campaign') as $g): ?>
                            <option value="<?= e($g) ?>"><?= e($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Tracking Content</label>
                    <select class="form-control selectize-tag" placeholder="Any Tracking Content..." name="fott">
                        <option></option>
                        <?php foreach(\Ds\Models\Order::getDistinctValuesOf('tracking_content') as $g): ?>
                            <option value="<?= e($g) ?>"><?= e($g) ?></option>
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
    <div class="col-md-12">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-pie-chart"></i> Payments Breakdown</div>
                </div>

                <div id="aggregate_html">

                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover" id="payments-list-new" data-ajax-route="<?= e(route('backend.reports.payments.index_ajax')) ?>"  data-ajax-aggregate-route="<?= e(route('backend.reports.payments.aggregate_ajax')) ?>">
        <thead>
            <tr>
                <th>Attempted</th>
                <th>Captured</th>
                <th>Transaction</th>
                <th width="120">Amount</th>
                <th>Name on Card / Bank Account</th>
                <th width="120">Gateway</th>
                <th width="120">Method</th>
                <th>Reference</th>
                <th>Verification</th>
                <th>Fail Text</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
