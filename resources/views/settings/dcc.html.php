
<form class="form-horizontal"
      action="/jpanel/settings/dcc/save"
      method="post"
      x-data="{ dcc_is_enabled: <?= e(sys_get('dcc_enabled')) ?>,feature_is_enabled : <?= e(feature('dcc_ai_plus')) ?>, dcc_ai_calculation : <?= e(sys_get('dcc_ai_is_enabled')) ?>}">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Donor Covers Costs (DCC)

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                <a href="https://help.givecloud.com/en/articles/4553231-what-is-donor-covers-cost-dcc" target="_blank" class="btn btn-default" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-lg-8 col-lg-offset-2">

        <?= dangerouslyUseHTML(app('flash')->output()) ?>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-gear"></i> DCC Setup
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 col-lg-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-gear"></i> DCC Setup</div>
                        <div class="panel-sub-desc">
                            <p>Choose whether you'd like to allow donors to cover the transaction costs.</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-8">
                        <div class="flex items-center">
                            <label for="dcc_enabled" class="mb-0 mr-4">Enable DCC</label>
                            <button type="button" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue" :class="dcc_is_enabled ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-200 hover:bg-gray-300'" @click="dcc_is_enabled = !dcc_is_enabled" role="switch" aria-checked="false" aria-labelledby="dcc-label">
                                <span aria-hidden="true" :class="dcc_is_enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>
                        <input type="checkbox" class="hidden" name="dcc_enabled" :checked="dcc_is_enabled" x-model="dcc_is_enabled" value="1" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(feature('dcc_ai_plus')) : ?>
<div class="row" x-show="dcc_is_enabled == 1">
    <div class="col-md-12 col-lg-8 col-lg-offset-2">


        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-calculator"></i> Calculation
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 col-lg-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-gear"></i> Calculation</div>
                        <div class="panel-sub-desc">
                            <p>Choose how Givecloud calculates the DCC top-up it displays.</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-8">
                        <div class="space-y-5">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input x-model="dcc_ai_calculation" id="small" style="margin-top:0" aria-describedby="small-description" name="dcc_ai_is_enabled" type="radio" checked class="focus:ring-brand-blue h-4 w-4 text-black border-gray-300" value="1">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="small" class="mb-0 font-bold text-gray-700">Automatic
                                        <span class="inline-flex items-center ml-2 px-1.5 py-0 rounded text-xxs font-bold uppercase bg-cyan-100 text-gray-900">Recommended</span>
                                    </label>
                                    <p id="small-description" class="text-gray-500">
                                        Givecloud's AI+ displays the best top-ups for each donor to maximize the costs you can recover.
                                    </p>
                                </div>
                            </div>

                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input x-model="dcc_ai_calculation" id="medium" style="margin-top:0" aria-describedby="medium-description" name="dcc_ai_is_enabled" type="radio" class="focus:ring-brand-blue h-4 w-4 text-black border-gray-300" value="0">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="medium" class="mb-0 font-bold text-gray-700">Customize</label>
                                    <p id="medium-description" class="text-gray-500">Override the calculation and wording.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row" x-show="(dcc_ai_calculation == 0 || feature_is_enabled == 0) && dcc_is_enabled == 1">
    <div class="col-md-12 col-lg-8 col-lg-offset-2">

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-calculator"></i> DCC Calculation
            </div>
            <div class="panel-body">

                <div class="row">

                    <div class="col-sm-6 col-lg-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-calculator"></i> DCC Calculation</div>
                        <div class="panel-sub-desc">
                            Manage the manual calculation for the DCC your donors will see.
                            <br /><br />
                            <span class="text-info">
                                <a href="https://help.givecloud.com/en/articles/4553236-customize-donor-covers-cost" target="_blank" rel="noreferrer">Learn more...</a>
                            </span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-8">
                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">DCC Rate</label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control text-right" name="dcc_percentage" value="<?= e(sys_get('dcc_percentage')) ?>" maxlength="5" />
                                    <div class="input-group-addon"><i class="fa fa-percent fa-fw"></i></div>
                                </div>
                                <small class="text-muted">Traditionally, this should match your payment processing rate. (%)</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-8">
                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">DCC Cost per Payment</label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-dollar fa-fw"></i></div>
                                    <input type="text" class="form-control text-right" name="dcc_cost_per_order" value="<?= e(sys_get('dcc_cost_per_order')) ?>" maxlength="5" />
                                </div>
                                <small class="text-muted">The fixed cost for each payment. ($)</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" x-show="(dcc_ai_calculation == 0 || feature_is_enabled == 0) && dcc_is_enabled == 1">
    <div class="col-md-12 col-lg-8 col-lg-offset-2">

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-wrench"></i> Overrides
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 col-lg-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-wrench"></i> Overrides</div>
                        <div class="panel-sub-desc">
                            <p>Choose how you'd like the DCC messages to show on the site.</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-8">
                        <div class="form-group">
                            <label for="dcc_checkout_label" class="col-md-4 control-label">Checkout Heading</label>
                            <div class="col-md-8">
                                <input id="dcc_checkout_label" class="form-control" type="text" name="dcc_checkout_label" value="<?= e(sys_get('dcc_checkout_label')) ?>" maxlength="255">
                                <small class="help-block">
                                    Displayed as the heading of the area where you are asked if you'd like to cover the costs.
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dcc_checkout_description" class="col-md-4 control-label">Checkout Description</label>
                            <div class="col-md-8">
                                <input id="dcc_checkout_description" class="form-control" type="text" name="dcc_checkout_description" value="<?= e(sys_get('dcc_checkout_description')) ?>" maxlength="255">
                                <small class="help-block">
                                    Displayed as the description when asked if you'd like to cover costs.
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dcc_checkout_description_with_amount" class="col-md-4 control-label">Checkout Description with Amount</label>
                            <div class="col-md-8">
                                <input id="dcc_checkout_description_with_amount" class="form-control" type="text" name="dcc_checkout_description_with_amount" value="<?= e(sys_get('dcc_checkout_description_with_amount')) ?>" maxlength="255">
                                <small class="help-block">
                                    Displayed as the description when asked if you'd like to cover costs. Please note that when adding "[$$$]" into the description, it will be replaced dynamically with the amount of the DCC fees. Eg, "Cover the [$$$] fee to make the most of my donation." will result in "Cover the $3.20 fee to make the most of my donation."
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dcc_invoice_label" class="col-md-4 control-label">Invoice / Cart Label</label>
                            <div class="col-md-8">
                                <input id="dcc_invoice_label" class="form-control" type="text" name="dcc_invoice_label" value="<?= e(sys_get('dcc_invoice_label')) ?>" maxlength="255">
                                <small class="help-block">
                                    This will be the label used to display the fee being covered in the cart, contribution review and invoice. It will be shown next to tax and shipping.
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dcc_enabled_on_sponsorships" class="col-md-4 control-label">Enable DCC on Sponsorships</label>
                            <div class="col-md-8">
                                <input id="dcc_enabled_on_sponsorships" type="checkbox" class="switch" name="dcc_enabled_on_sponsorships" value="1" <?= e((sys_get('dcc_enabled_on_sponsorships') == 1) ? 'checked' : '') ?> >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</form>
