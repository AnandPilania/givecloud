
<script>
    function onShippingSelection () {
        value = $('input[name=shipping_handler]:checked').val();
        if (value === 'courier') {
            $('.courier-only').removeClass('hide');
            onShipperSelection();
        } else {
            $('.courier-only').addClass('hide');
            $('.ups-only, .capost-only, .fedex-only, .usps-only').addClass('hide');
        }


    }

    function onShipperSelection () {
        if ($('input[name=shipping_ups_enabled]').prop('checked')) {
            $('.ups-only').removeClass('hide');
        } else {
            $('.ups-only').addClass('hide');
        }

        if ($('input[name=shipping_canadapost_enabled]').prop('checked')) {
            $('.capost-only').removeClass('hide');
        } else {
            $('.capost-only').addClass('hide');
        }

        if ($('input[name=shipping_fedex_enabled]').prop('checked')) {
            $('.fedex-only').removeClass('hide');
        } else {
            $('.fedex-only').addClass('hide');
        }

        if ($('input[name=shipping_usps_enabled]').prop('checked')) {
            $('.usps-only').removeClass('hide');
        } else {
            $('.usps-only').addClass('hide');
        }
    }
</script>

<form class="form-horizontal" action="/jpanel/settings/shipping/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Shipping

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-gear"></i> Shipping Setup
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-gear"></i> Shipping Setup</div>
                    <div class="panel-sub-desc">
                        <p>Choose how you'd like to charge your customers/supporters shipping.</p>

                        <p class="text-info">
                        <strong><i class="fa fa-question-circle"></i> Want to use a shipping integration?</strong>Make sure you select <strong>Rated Shipping</strong> as your shipping setup.</p>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">

                    <div class="radio">
                        <label>
                            <input name="shipping_handler" type="radio" value="tiered" <?= e((sys_get('shipping_handler') === 'tiered') ? "checked" : "") ?> onchange="onShippingSelection();" >
                            <strong>Flat Rate Shipping</strong> (Default)
                            <div class="text-muted">
                                Charge one price for shipping based on the total value of the items in the cart and their shipping location.<br />
                                <a href="/jpanel/shipping" style="margin-top:7px;" class="btn btn-default btn-sm"><i class="fa fa-gear"></i> Change Rates</a>
                            </div>
                        </label>
                    </div>

                    <br>

                    <div class="radio">
                        <label>
                            <input name="shipping_handler" type="radio" value="courier" <?= e((sys_get('shipping_handler') === 'courier') ? "checked" : "") ?> onchange="onShippingSelection();" >
                            <strong>Rated Shipping</strong> (Shipping Integrations)
                            <div class="text-muted">
                                Use estimates from your preferred shipper to charge shipping costs. You'll be required to assign every product a weight. We will communicate with the shipper of your choice to detemine the estimated shipping cost and will add the estimated cost to the contribution as the shipping cost.
                            </div>
                        </label>
                    </div>

                </div>

            </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default courier-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-truck"></i> Shippers
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> Shippers</div>
                    <div class="panel-sub-desc">
                        <p>Choose which shipper estimates you want to display during checkout.</p>

                        <?php if (user()->can_live_chat): ?>
                            <p class="text-info">
                                <strong><i class="fa fa-question-circle"></i> Do you have your own shipper account with negotiated shipping rates?</strong><a href="javascript:Intercom('showNewMessage','Can you help me setup negotiated rates with my shipper?');"> Contact us</a> with your account settings for your shipper and we'll do the rest.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">

                    <div class="checkbox">
                        <label>
                            <input name="shipping_ups_enabled" type="checkbox" value="1" onclick="onShipperSelection();" <?= e((sys_get('shipping_ups_enabled') === '1') ? 'checked' : '') ?> >
                            <strong>UPS</strong>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input name="shipping_fedex_enabled" type="checkbox" value="1" <?= e((sys_get('shipping_fedex_enabled') === '1') ? 'checked' : 'disabled') ?> >
                            <strong>FedEx</strong> <?php if (user()->can_live_chat): ?> (Contact <a href="javascript:Intercom('showNewMessage','Can you help me setup FedEx?');">Support</a>) <?php endif; ?>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input name="shipping_usps_enabled" type="checkbox" onclick="onShipperSelection();" value="1" <?= e((sys_get('shipping_usps_enabled') === '1') ? 'checked' : '') ?> >
                            <strong>United States Postal Service</strong>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input name="shipping_canadapost_enabled" type="checkbox" onclick="onShipperSelection();" value="1" <?= e((sys_get('shipping_canadapost_enabled') === '1') ? 'checked' : '') ?> >
                            <strong>Canada Post</strong>
                        </label>
                    </div>

                </div>
                </div>

            </div>
        </div>
    </div>

<!--
    <div class="panel panel-default hide courier-only canadapost-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-truck"></i> CanadaPost Settings
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> CanadaPost Settings</div>
                    <div class="panel-sub-desc">You can optionally use your own CanadaPost settings to customize your CanadaPost rates.</div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="" class="col-md-4 control-label">Settings</label>
                        <div class="col-md-8">
                            <select name="shipping_canadapost_custom_settings" class="form-control" onchange="onShipperSelection();">
                                <option value="0" <?= e((sys_get('shipping_canadapost_custom_settings') === '1') ? 'checked' : '') ?> >Use default setings</option>
                                <option value="1" <?= e((sys_get('shipping_canadapost_custom_settings') === '0') ? 'checked' : '') ?> >Use custom settings</option>
                            </select>
                        </div>
                    </div>

                    <div class="shipping_canadapost_custom_settings-wrap hide">
                        <div class="form-group">
                            <label for="" class="col-md-4 control-label">Customer Number</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="shipping_canadapost_customer_number" value="<?= e(sys_get('shipping_canadapost_customer_number')) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-md-4 control-label">Username</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="shipping_canadapost_user" value="<?= e(sys_get('shipping_canadapost_user')) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-md-4 control-label">Password</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="shipping_canadapost_pass" value="<?= e(sys_get('shipping_canadapost_pass')) ?>">
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>-->

    <div class="panel panel-default hide ups-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-truck"></i> UPS Settings
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> UPS Settings</div>
                    <div class="panel-sub-desc">
                        Configure your UPS integration.
                    </div>
                </div>
                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Account Number</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="shipping_ups_account" value="<?= e(sys_get('shipping_ups_account')) ?>">
                            <small class="text-muted">Required when using negotiated rates.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Options</label>
                        <div class="col-md-8">
                            <select class="selectize form-control auto-height" multiple="multiple" name="shipping_ups_servicecodes[]">
                                <?php foreach(\Ds\Domain\Commerce\Shipping\Carriers\UPS::getServices() as $id => $label): ?>
                                    <option value="<?= e($id) ?>" <?= e((in_array((string)$id, explode(',',sys_get('shipping_ups_servicecodes')), true)) ? 'selected' : '') ?> ><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select the shipping options you want UPS to display for shipments.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Rate Type</label>
                        <div class="col-md-8">
                            <select class="form-control" name="shipping_ups_negotiated_rates">
                                <option value="0" <?= e(volt_selected(sys_get('shipping_ups_negotiated_rates'), '0')); ?> >Standard Rates</option>
                                <option value="1" <?= e(volt_selected(sys_get('shipping_ups_negotiated_rates'), '1')); ?> >Negotiated Rates</option>
                            </select>
                            <small class="text-muted">Choose between negotiated and standard rates.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default hide fedex-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-truck"></i> FedEx Settings
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> FedEx Settings</div>
                    <div class="panel-sub-desc">
                        Configure your FedEx integration.
                    </div>
                </div>
                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Options</label>
                        <div class="col-md-8">
                            <select class="selectize form-control auto-height" multiple="multiple" name="shipping_fedex_servicecodes[]">
                                <?php foreach(\Ds\Domain\Commerce\Shipping\Carriers\FedEx::getServices() as $id => $label): ?>
                                    <option value="<?= e($id) ?>" <?= e((in_array((string)$id, explode(',',sys_get('shipping_fedex_servicecodes')), true)) ? 'selected' : '') ?> ><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select the shipping options you want FedEx to display for shipments.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default hide usps-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-truck"></i> USPS Settings
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> USPS Settings</div>
                    <div class="panel-sub-desc">
                        Configure your USPS integration.

                        <br><br>
                        <p class="text-info">
                        <strong><i class="fa fa-exclamation-circle"></i> Note: </strong>
                        More information on USPS shipping methods can be found on the <a href="https://www.usps.com/ship/mail-shipping-services.htm" target="_blank" rel="noopener noreferrer">USPS website</a>.
                        </p>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Domestic Options</label>
                        <div class="col-md-8">
                            <select class="selectize form-control auto-height" multiple="multiple" name="shipping_usps_classids[]">
                                <?php foreach(\Ds\Domain\Commerce\Shipping\Carriers\USPS::getClassIds() as $id => $label): ?>
                                    <option value="<?= e($id) ?>" <?= e((in_array((string)$id, explode(',',sys_get('shipping_usps_classids')), true)) ? 'selected' : '') ?> ><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select the shipping options you want USPS to display for domestic shipments.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">International Options</label>
                        <div class="col-md-8">
                            <select class="selectize form-control auto-height" multiple="multiple" name="shipping_usps_interids[]">
                                <?php foreach(\Ds\Domain\Commerce\Shipping\Carriers\USPS::getInternationalClassIds() as $id => $label): ?>
                                    <option value="<?= e($id) ?>" <?= e((in_array((string)$id, explode(',',sys_get('shipping_usps_interids')), true)) ? 'selected' : '') ?> ><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select the shipping options you want USPS to display for international shipments.</small>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default courier-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-map-marker"></i> Shipping From...
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-map-marker"></i> Shipping From...</div>
                    <div class="panel-sub-desc">When using a third-party shipper, it's important to know from where you are shipping your contributions so that shipping estimates are accurate.</div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="" class="col-md-4 control-label">State/Province</label>
                        <div class="col-md-8">
                            <select name="shipping_from_state" class="form-control">
                                <option value="">Choose One</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= e($region->code) ?>" <?= e((sys_get('shipping_from_state') == $region->code) ? 'selected' : '') ?> ><?= e($region->name) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="col-md-4 control-label">Postal Code</label>
                        <div class="col-md-8">
                            <input type="text" name="shipping_from_zip" class="form-control" value="<?= e(sys_get('shipping_from_zip')) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="col-md-4 control-label">Country</label>
                        <div class="col-md-8">
                            <select name="shipping_from_country" class="form-control">
                                <option value="">Choose One</option>
                                <option value="CA" <?= e((sys_get('shipping_from_country') == 'CA') ? 'selected' : '') ?> >Canada</option>
                                <option value="US" <?= e((sys_get('shipping_from_country') == 'US') ? 'selected' : '') ?> >United States</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-envelope"></i> Packing Slip
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-envelope"></i> Packing Slip</div>
                    <div class="panel-sub-desc">Customize the packing slip header to match your organization's branding.</div>
                </div>

            </div>

            <br>
            <textarea name="packing_slip_corporate_header" id="packing_slip_corporate_header" style="height:300px;" class="form-control html-doc"><?= e(sys_get('packing_slip_corporate_header')) ?></textarea>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-university"></i> Taxes on Shipping
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-university"></i> Taxes on Shipping</div>
                    <div class="panel-sub-desc">
                        <p>Decide whether or not to apply tax on your shipping charges or not.<p>
                        <p class="text-info">
                        <strong><i class="fa fa-question-circle"></i> Why would I NOT charge tax?</strong><br>
                        For example, Third-Party Shipper's will include tax in their rate estimate. In that case, it would be odd to charge tax on top of the estimate from the shipper.
                        </p>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Apply Tax</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" name="shipping_taxes_apply" value="1" <?= e((sys_get('shipping_taxes_apply') == 1) ? 'checked' : '') ?> >
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-gear"></i> Shipping Expectations
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-gear"></i> Shipping Expectations</div>
                    <div class="panel-sub-desc">Default settings for product stock related shipping expectation notices.</div>
                </div>
                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="" class="col-md-4 control-label">Stock Threshold</label>
                        <div class="col-md-8">
                            <input class="form-control" type="number" name="shipping_expectation_threshold" value="<?= e(sys_get('shipping_expectation_threshold')) ?>">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="" class="col-md-4 control-label">Over Threshold Notice</label>
                        <div class="col-md-8">
                            <input class="form-control" type="text" name="shipping_expectation_over" value="<?= e(sys_get('shipping_expectation_over')) ?>" maxlength="255">
                            <small class="help-block">
                                Displayed when available stock is <strong>over</strong> the stock threshold.
                            </small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-md-4 control-label">Under Threshold Notice</label>
                        <div class="col-md-8">
                            <input class="form-control" type="text" name="shipping_expectation_under" value="<?= e(sys_get('shipping_expectation_under')) ?>" maxlength="255">
                            <small class="help-block">
                                Displayed when available stock is <strong>at or under</strong> the stock threshold.
                            </small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
</div>

<script>
spaContentReady(function() {
    onShippingSelection();
});
</script>

</form>
