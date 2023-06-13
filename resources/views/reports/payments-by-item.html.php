<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Payments by Item

            <div class="pull-right">
                <a href="#" class="datatable-export btn btn-default"><i class="fa fa-fw fa-download"></i> Export</a>
            </div>
        </h1>
    </div>
</div>

<?php if(\Carbon\Carbon::today()->isBefore(\Carbon\Carbon::parse('2021-12-15'))): ?>
<div class="row">
    <div class="col-lg-12">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4">
            <div class="flex">
                <div class="shrink-0">
                    <!-- Heroicon name: solid/exclamation -->
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        This report will be removed on December 15th, 2021.
                        <a href="<?= e(route('backend.reports.contribution-line-items.index')) ?>" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                            Please use the Contribution Line Items report.
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <form class="datatable-filters">
        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Items</label>
                <select id="itemfilter" class="form-control selectize" size="1" placeholder="Choose Item(s)..." name="i[]" multiple="multiple">
                    <?php foreach($items as $item): ?>
                        <option value="<?= e($item['id']) ?>"><?= e($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Categories</label>
                <select id="categoryfilter" class="form-control selectize" size="1" placeholder="Choose Category(s)..." name="c[]" multiple="multiple">
                    <?php foreach($categories as $cat1): ?>
                        <option value="<?= e($cat1->id) ?>" ><?= e($cat1->name) ?></option>
                        <?php if($cat1->childCategories): foreach($cat1->childCategories as $cat2): ?>
                            <option value="<?= e($cat2->id) ?>" ><?= e($cat1->name) ?> &gt; <?= e($cat2->name) ?></option>
                            <?php if($cat2->childCategories): foreach($cat2->childCategories as $cat3): ?>
                                <option value="<?= e($cat3->id) ?>" ><?= e($cat1->name) ?> &gt; <?= e($cat2->name) ?> &gt; <?= e($cat3->name) ?></option>
                                <?php if($cat3->childCategories): foreach($cat3->childCategories as $cat4): ?>
                                    <option value="<?= e($cat4->id) ?>" ><?= e($cat1->name) ?> &gt; <?= e($cat2->name) ?> &gt; <?= e($cat3->name) ?> &gt; <?= e($cat4->name) ?></option>
                                    <?php if($cat4->childCategories): foreach($cat4->childCategories as $cat5): ?>
                                        <option value="<?= e($cat5->id) ?>" ><?= e($cat1->name) ?> &gt; <?= e($cat2->name) ?> &gt; <?= e($cat3->name) ?> &gt; <?= e($cat4->name) ?> &gt; <?= e($cat5->name) ?></option>
                                    <?php endforeach; endif; ?>
                                <?php endforeach; endif; ?>
                            <?php endforeach; endif; ?>
                        <?php endforeach; endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Capture date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="fd1" value="" placeholder="Capture date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="fd2" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Attempt date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="fc1" value="" placeholder="Attempt date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="fc2" value="" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Status</label>
                <select class="form-control" name="s">
                    <option value="">Any Status</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
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
                <label class="form-label">IP Country</label>
                <select class="form-control" name="foi">
                    <option value="">Any IP Country</option>
                    <?php foreach($countries as $country): ?>
                        <option value="<?= e($country['code']) ?>"><?= e($country['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Gateway</label>
                <select class="form-control" name="fg">
                    <option value="">Any Gateway</option>
                    <?php foreach($gateways as $g): ?>
                        <option value="<?= e($g) ?>" <?= e((request('fg') == $g) ? 'selected' : '') ?>><?= e(ucfirst($g)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Supporter Type</label>
                <select class="selectize form-control" name="fat" placeholder="Any Supporter Type...">
                    <option></option>
                    <?php foreach ($account_types as $account_type): ?>
                        <option value="<?= e($account_type->id) ?>" <?= e(volt_selected($account_type->id, request('fat'))); ?>><?= e($account_type->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

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

            <?php if (sys_get('gift_aid') == 1) { ?>
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Gift Aid Status</label>
                    <select class="form-control" name="fga">
                        <option value="">Any Gift Aid Status</option>
                        <option value="1">Gift Aid eligible</option>
                        <option value="0">Gift Aid ineligible</option>
                    </select>
                </div>
            <?php } ?>

           <div class="form-group pt-1 px-2">
               <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
           </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="payments-by-item-list" class="table table-v2 table-striped table-hover responsive">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>GL</th>
                        <th>Name</th>
                        <th>Contribution Amount</th>
                        <th>Type</th>
                        <th>Transaction</th>
                        <th>Time</th>
                        <th>Capture Date</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
