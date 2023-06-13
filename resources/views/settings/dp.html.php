
<form action="/jpanel/settings/dp/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo-xl inline"> DonorPerfect

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

<div class="form-horizontal">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <?php if (!dpo_is_connected()): ?>
        <div class="alert alert-danger text-center"><i class="fa fa-exclamation-triangle"></i> There is a problem with your DonorPerfect username and password.</div>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-lock"></i> Login
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-lock"></i> Login</div>
                    <div class="panel-sub-desc">Enter the DonorPerfect username and password that will be used to connect your accounts.</div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Username</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="dpo-username" name="dpo_user" value="<?= e(sys_get('dpo_user')) ?>" maxlength="" />
                        </div>
                    </div>

                    <div class="form-group has-feedback">
                        <label for="name" class="col-md-4 control-label">Password</label>
                        <div class="col-md-8">
                            <input type="password" class="form-control password" id="dpo-password" name="dpo_pass" value="<?= e(sys_get('dpo_pass')) ?>" maxlength="" />
                            <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                        </div>
                    </div>

                    <div class="form-group has-feedback">
                        <label for="name" class="col-md-4 control-label">API Key</label>
                        <div class="col-md-8">
                            <input type="password" class="form-control password" id="dpo-apikey" name="dpo_api_key" value="<?= e(sys_get('dpo_api_key')) ?>" maxlength="" />
                            <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                            <p class="help-block">
                                <i class="fa fa-question-circle"></i> Optional: You can either enter a Username/Password or an API Key.
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <button type="button" class="btn btn-info btn-sm dpo-test" data-username="dpo-username" data-password="dpo-password" data-apikey="dpo-apikey">Test Connection</button> <?php /*<span><i class="fa fa-spin fa-spinner"></i> Success!</span>*/ ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (dpo_is_connected()): ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-wrench"></i> Helpers
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-wrench"></i> Helpers</div>
                    <div class="panel-sub-desc"></div>
                </div>

                <div class="col-sm-6 col-lg-6 col-lg-offset-2">

                    <?php $account_count = \Ds\Models\Member::whereNotNull('donor_id')->count(); ?>
                    <strong><a href="#" data-target="#modal-update-accounts-from-dp" data-toggle="modal">Update Accounts from DonorPerfect</a></strong><br>
                    <div class="text-muted">You have <strong><?= e($account_count) ?></strong> accounts in Givecloud with Donor ID's. Use this function to update all <?= e($account_count) ?> accounts from DonorPerfect. You can select what data is updated from DonorPerfect.</div>

                </div>

            </div>
        </div>
    </div>

    <?php if (is_super_user()): ?>
        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-terminal"></i> Logging <span class="badge">SUPPORT</span>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-terminal"></i> Logging <span class="badge">SUPPORT</span></div>
                        <div class="panel-sub-desc">Enable logging and see all DP communication in the site's log file.</div>
                    </div>

                    <div class="col-sm-6 col-md-8">

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Enable Logging</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="switch" value="1" name="dp_logging" <?= e((sys_get('dp_logging') == 1) ? 'checked' : '') ?>>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-exchange"></i> Sync
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-exchange"></i> Auto-Sync</div>
                    <div class="panel-sub-desc">Automatically sync all your incoming contributions and recurring transactions to DonorPerfect.<br><br>If you choose not to auto-sync all your contributions and transactions, you can choose to manually sync individual contributions and transactions on a case by case basis.</div>

                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Auto-Sync Contributions</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="dp_auto_sync_orders" <?= e((sys_get('dp_auto_sync_orders') == 1) ? 'checked' : '') ?>>
                            <br><small class="text-muted">This will affect all <em>future contributions.</em></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Auto-Sync Contribution Refunds</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="dp_push_order_refunds" <?= e((sys_get('dp_push_order_refunds') == 1) ? 'checked' : '') ?>>
                            <br><small class="text-muted">This will affect all <em>future contribution refunds.</em></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Auto-Sync Transactions</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="dp_auto_sync_txns" <?= e((sys_get('dp_auto_sync_txns') == 1) ? 'checked' : '') ?>>
                            <br><small class="text-muted">This will affect all <em>future transactions.</em></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Auto-Sync Transactions Refunds</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="dp_push_txn_refunds" <?= e((sys_get('dp_push_txn_refunds') == 1) ? 'checked' : '') ?>>
                            <br><small class="text-muted">This will affect all <em>future transaction refunds.</em></small>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default hide">
        <div class="panel-heading visible-xs">
            <i class="fa fa-refresh"></i> Recurring Transactions
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-refresh"></i> Recurring Transactions</div>
                    <div class="panel-sub-desc">Choose how you want to handle your recurrinng transactions.</div>
                </div>

                <div class="col-sm-6 col-md-4 col-md-offset-2 col-lg-5 col-lg-offset-3">
                    <div class="radio">
                        <label>
                            <input name="rpp_donorperfect" type="radio" value="1" <?= e((sys_get('rpp_donorperfect') == 1) ? 'checked' : '') ?> >
                            <strong>Use DonorPerfect</strong>
                            <div class="text-muted">
                                Push all recurring transactions into DonorPerfect as pledges for manual processing.
                                <br /><br>
                                <i class="fa fa-check"></i> Donors are created in DonorPerfect.<br>
                                <i class="fa fa-check"></i> Pledges are created in DonorPerfect.<br>
                                <span class="text-warning"><i class="fa fa-exclamation-circle"></i> You must manually process every EFT in DonorPerfect.</span><br>
                                <span class="text-warning"><i class="fa fa-exclamation-circle"></i> Your users will not be able to edit their recurring payments.</span><br>
                            </div>
                        </label>
                    </div>

                    <br />

                    <div class="radio">
                        <label>
                            <input name="rpp_donorperfect" type="radio" value="0" <?= e((sys_get('rpp_donorperfect') == 0) ? 'checked' : '') ?> >
                            <strong>Use Automated Recurring Transactions</strong>
                            <div class="text-muted">
                                Automatically store and process all your recurring transactions.
                                <br /><br>
                                <i class="fa fa-check"></i> Donors are created in DonorPerfect.<br>
                                <span class="text-warning"><i class="fa fa-exclamation-circle"></i> Pledges are NOT created in DonorPerfect.<br></span>
                                <i class="fa fa-check"></i> Auto-charges your recurring EFTs.<br />
                                <i class="fa fa-check"></i> Gifts are created each time GC charges a supporter.<br />
                                <i class="fa fa-check"></i> Your users can change their recurring payments.<br />
                            </div>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-list"></i> Split Gifts
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-list"></i> Split Gifts</div>
                    <div class="panel-sub-desc">
                        Push Givecloud contributions as a master gift with split gifts.
                        <br /><br />
                        <span class="text-yellow-500"><i class="fa fa-exclamation-circle"></i> <strong>Warning:</strong> Before enabling, we highly recommend you <a href="https://help.givecloud.com/en/articles/6026087-split-gifts-vs-multiple-individual-gifts" target="_blank">read our help article on split gifts</a>.</span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4 col-md-offset-2 col-lg-5 col-lg-offset-3">
                    <div class="radio">
                        <label>
                            <input name="dp_enable_split_gifts" type="radio" value="1" <?= e((sys_get('dp_enable_split_gifts') == 1) ? 'checked' : '') ?> >
                            <strong>Use Split Gifts</strong>
                            <div class="text-muted">
                                One master gift is created representing the total payment. Split gifts are created representing each individual line in the contribution.
                            </div>
                        </label>
                    </div>

                    <br />

                    <div class="radio">
                        <label>
                            <input name="dp_enable_split_gifts" type="radio" value="0" <?= e((sys_get('dp_enable_split_gifts') == 0) ? 'checked' : '') ?> >
                            <strong>Use Multiple Individual Gifts</strong>
                            <div class="text-muted">
                                Every line item in the contribution is pushed into DonorPerfect as a separate gift.
                            </div>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-sticky-note"></i> GC Meta Data
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-sticky-note"></i> GC Meta Data</div>
                    <div class="panel-sub-desc">
                        We can push additional meta data about each financial transaction into DonorPerfect, if you want it.

                        <br><br><span class="text-info"><i class="fa fa-question-circle"></i> <strong>I don't see the field I want.</strong><br>In DonorPerfect, use the screen designer to ensure you have added the field to the gift screen.</span>

                        <br><br><span class="text-info"><i class="fa fa-question-circle"></i> <strong>The data isn't going into DP.</strong><br>Be sure the values being pushed from Givecloud exist as options in DonorPerfect. Givecloud will try to match both codes and descriptions. For example: <ul><li>A payment method of 'Visa' in Givecloud will match GIFT_TYPE code 'VI' if the code's description is set to 'Visa'.</li><li>A referral source of 'Facebook' will match a SOLICIT_CODE code 'FB' if the code's description is set to 'Facebook'.</li></ul></span>

                        <p>
                            <a href="#" class="dpo-codes-refresh btn btn-info btn-xs"><i class="fa fa-refresh fa-fw"></i> Refresh DonorPerfect Codes</a>
                        </p>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Payment Method<br><small class="text-info">(Visa / MasterCard / American Express / PayPal / GoCardless / Cash / Check / Other)</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_payment_method">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_payment_method')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted" style="display:inline-block;line-height:1.2;margin-bottom:8px;">The payment method used to process the payment.</small>
                            <input type="text" class="form-control" name="dp_meta_payment_method_default" value="<?= e(sys_get('dp_meta_payment_method_default')) ?>" maxlength="30" placeholder="Default value, ex: FREE">
                            <small class="text-muted" style="display:inline-block;line-height:1.2;margin-top:2px;">The value to use when there is no payment method associated with the payment.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Contribution Number<br><small class="text-info">Contribution "#XXXXXXXXX"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_order_number">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_order_number')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The unique contribution number associated with this payment in Givecloud.</small>
                        </div>
                    </div>

                    <?php $order_sources = array_unique(array_merge(['Import','Web'], explode(',', sys_get('pos_sources')))) ?>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Contribution Source<br><small class="text-info">(<?= e(implode(' / ', $order_sources)) ?>)</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_order_source">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_order_source')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The source of the payment.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Special Notes</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_special_notes">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_special_notes')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The special notes / comments provided by the supporter.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Item Description<br><small class="text-info">Ex: "Lung Health Brochure - Digital Copy (LNGHELTH-D)"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_item_description">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_item_description')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The name and code of the item represented in the contribution item.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Item Name<br><small class="text-info">Ex: "Lung Health Brochure"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_item_name">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_item_name')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The name the item represented in the contribution item.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Item Variant Name<br><small class="text-info">Ex: "Digital Copy"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_item_variant_name">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_item_variant_name')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The varaint name of the item represented in the contribution item.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Item Code<br><small class="text-info">Ex: "LNGHELTH-D"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_item_code">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_item_code')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The code of the item represented in the contribution item.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Item Fair Market Value<br><small class="text-info">Ex: "12.00"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_item_fmv">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_item_fmv')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The fair market value of the item ordered.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Item Qty<br><small class="text-info">Ex: "3"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_item_qty">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_item_qty')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The qty of the item ordered.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Was a Recurring Payment<br><small class="text-info">(Y / N)</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_is_rpp">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_is_rpp')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">Is the payment an recurring payment transaction (EFT)?</small>
                        </div>
                    </div>

                    <div class="form-group <?= e((sys_get('referral_sources_isactive') != 1) ? 'hide' : '') ?>">
                        <label for="name" class="col-md-4 control-label">Referral Source<br><small class="text-info"><?= e(implode(' / ', explode(',', sys_get('referral_sources_options')))) ?></small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_referral_source">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_referral_source')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">"How'd you hear about us?"</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Tracking Source</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tracking_source">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tracking_source')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Tracking Medium</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tracking_medium">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tracking_medium')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Tracking Campaign</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tracking_campaign">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tracking_campaign')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Tracking Term</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tracking_term">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tracking_term')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Tracking Content</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tracking_content">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tracking_content')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group top-gutter">
                        <div class="col-md-8 col-md-offset-4">
                            <hr>
                            <div class="row">
                                <div class="col-xs-7">Item settings override these codes.
                                    <br><small class="text-muted">When DP codes are set at the item level, use those instead of the codes above.</small>
                                </div>
                                <div class="col-xs-5 text-right">
                                    <input type="checkbox" class="switch" value="1" name="dp_product_codes_override" <?= e((sys_get('dp_product_codes_override') == 1) ? 'checked' : '') ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-sticky-note"></i> Receipt Settings
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-sticky-note"></i> Receipt Settings</div>
                    <div class="panel-sub-desc">
                        What receipt settings do you want to use when pushing <u>all</u> gifts to DP?
                        <br /><br />
                        <span class="text-yellow-500"><i class="fa fa-exclamation-circle"></i> <strong>Warning:</strong> These settings will be overridden anytime Givecloud issues a tax receipt on your behalf. Givecloud specifies Individual Receipting and Email Delivery Preference.</span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Receipt Type</label>
                        <div class="col-md-8">
                            <select name="dp_default_rcpt_type" class="form-control">
                                <option value="C" <?= e((sys_get('dp_default_rcpt_type') == 'C') ? 'selected' : '') ?> >(C) Consolidated Receipting</option>
                                <option value="I" <?= e((sys_get('dp_default_rcpt_type') == 'I') ? 'selected' : '') ?> >(I) Individual Receipting</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Delivery Preference</label>
                        <div class="col-md-8">
                            <select name="dp_default_rcpt_pref" class="form-control">
                                <option value="0" <?= e((sys_get('dp_default_rcpt_pref') == '0') ? 'selected' : '') ?>>Do Not Set</option>
                                <option value="L" <?= e((sys_get('dp_default_rcpt_pref') == 'L') ? 'selected' : '') ?>>(L) Letter</option>
                                <option value="E" <?= e((sys_get('dp_default_rcpt_pref') == 'E') ? 'selected' : '') ?>>(E) Email</option>
                                <option value="B" <?= e((sys_get('dp_default_rcpt_pref') == 'B') ? 'selected' : '') ?>>(B) Letter &amp; Email</option>
                                <option value="N" <?= e((sys_get('dp_default_rcpt_pref') == 'N') ? 'selected' : '') ?>>(N) Do Not Acknowledge</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-user"></i> Spouse Matching
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-user"></i> Donor Matching</div>
                        <div class="panel-sub-desc"></div>
                    </div>

                    <div class="col-sm-6 col-md-4 col-md-offset-2 col-lg-5 col-lg-offset-3">
                        <div class="radio">
                            <label>
                                <input name="dp_match_donor_spouse" type="radio" value="1" <?= e((sys_get('dp_match_donor_spouse') == 1) ? 'checked' : '') ?> >
                                <strong>Yes</strong>
                                <div class="text-muted">Include Spouse name in donor matching.<br><small>Requires dpudf.spouse field.</small></div>
                            </label>
                        </div>

                        <br />

                        <div class="radio">
                            <label>
                                <input name="dp_match_donor_spouse" type="radio" value="0" <?= e((sys_get('dp_match_donor_spouse') == 0) ? 'checked' : '') ?> >
                                <strong>No</strong>
                                <div class="text-muted">Do not include Spouse name in donor matching.</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-gift"></i> Contribution Data
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-gift"></i> Contribution Data</div>
                        <div class="panel-sub-desc"></div>
                    </div>
                    <div class="col-sm-6 col-md-8">

                        <div class="form-group top-gutter">
                            <div class="col-md-8 col-md-offset-4">
                                <div class="row">
                                    <div class="col-xs-7">
                                        Push special notes to gift narrative.
                                    </div>
                                    <div class="col-xs-5 text-right">
                                        <input type="checkbox" class="switch" value="1" name="dp_order_comments_to_narrative" <?= e((sys_get('dp_order_comments_to_narrative') == 1) ? 'checked' : '') ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group top-gutter">
                            <div class="col-md-8 col-md-offset-4">
                                <div class="row">
                                    <div class="col-xs-7">
                                        Set <strong>Thank You Date</strong> when the contribution received email is sent.
                                    </div>
                                    <div class="col-xs-5 text-right">
                                        <input type="checkbox" class="switch" value="1" name="dp_enable_ty_date" <?= e((sys_get('dp_enable_ty_date') == 1) ? 'checked' : '') ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-gift"></i> Tribute Data
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-gift"></i> Tribute Data</div>
                        <div class="panel-sub-desc">In order to help you track your tributes as clear as possible, we allow you to push details about the tribute into the <strong>Gift Narrative</strong>.</div>

                    </div>

                    <div class="col-sm-6 col-md-8">

                        <div class="form-group top-gutter">
                            <div class="col-md-8 col-md-offset-4">
                                <div class="row">
                                    <div class="col-xs-7">Push tribute message to gift narrative.
                                        <!--<br><small class="text-muted">Push updates that a donor makes in their online profile to DonorPerfect.</small>-->
                                    </div>
                                    <div class="col-xs-5 text-right">
                                        <input type="checkbox" class="switch" value="1" name="dp_tribute_message_to_narrative" <?= e((sys_get('dp_tribute_message_to_narrative') == 1) ? 'checked' : '') ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group top-gutter">
                            <div class="col-md-8 col-md-offset-4">
                                <div class="row">
                                    <div class="col-xs-7">Push tribute <strong>name</strong> and <strong>notification details</strong> to gift narrative.
                                        <!--<br><small class="text-muted">Push admin created accounts to DonorPerfect.</small>-->
                                    </div>

                                    <div class="col-xs-5 text-right">
                                        <input type="checkbox" class="switch" value="1" name="dp_tribute_details_to_narrative" <?= e((sys_get('dp_tribute_details_to_narrative') == 1) ? 'checked' : '') ?>>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Donors Name<br><small class="text-info">Ex: "Bob Hope"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_donor_name">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_donor_name')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">Name of Donor</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Name On Tribute<br><small class="text-info">Ex: "Uncle Frank"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tribute_name">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tribute_name')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">In memory or in honor of name</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Tribute Type<br><small class="text-info">Ex: "In Memory Of"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tribute_type">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tribute_type')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The type of tribute</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Notifier Name On Tribute<br><small class="text-info">Ex: "Aunt Sue"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tribute_notify_name">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tribute_notify_name')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The person who will be notified of the tribute</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Notifier Email On Tribute<br><small class="text-info">Ex: "bob@test.com"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tribute_notify_email">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tribute_notify_email')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The email address of the person who will be notified of the tribute</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Notifier Address On Tribute<br><small class="text-info">Ex: "291 No Show Lane, Newark, New Jersey, 07101, US"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tribute_notify_address">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tribute_notify_address')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The mailing address of the person who will be notified of the tribute</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Notify Type Of Tribute<br><small class="text-info">Ex: "Letter"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tribute_notify_type">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tribute_notify_type')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The type of notification for the tribute. Letter or Email.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Personal Message<br><small class="text-info">Ex: "In rememberance of Uncle Frank"</small></label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                <select class="form-control" name="dp_meta_tribute_personal_message">
                                    <option></option>
                                    <?php foreach($dp_field_options as $field): ?>
                                        <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_meta_tribute_personal_message')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="text-muted">The personal message to be sent with the tribute</small>
                        </div>
                    </div>

                    </div>

                </div>
            </div>
        </div>

        <?php if (feature('fundraising_pages') && (sys_get('fundraising_pages_enabled'))): ?>
            <div class="panel panel-default">
                <div class="panel-heading visible-xs">
                    <i class="fa fa-users"></i> Fundraising Pages
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6 col-md-4 hidden-xs">
                            <div class="panel-sub-title"><i class="fa fa-users"></i> Fundraising Page Settings</div>
                        </div>

                        <div class="col-sm-6 col-md-8">
                            <div class="form-group">
                                <label for="name" class="col-md-4 control-label">Soft Credits</label>
                                <div class="col-md-8">
                                    <input type="checkbox" class="switch" value="1" name="dp_p2p_soft_credits" <?= e((sys_get('dp_p2p_soft_credits') == 1) ? 'checked' : '') ?>>
                                    <p><small class="text-muted">Givecloud will create a soft credit that is linked to a gift towards a fundrasing page on the person's record who created the fundraiser.</small></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="name" class="col-md-4 control-label">Page Identifier</label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <div class="input-group-addon">Gift &nbsp;<i class="fa fa-arrow-right"></i></div>
                                        <select class="form-control" name="dp_p2p_url_field">
                                            <option></option>
                                            <?php foreach($dp_field_options as $field): ?>
                                                <option value="<?= e($field) ?>" <?= e((strtolower(sys_get('dp_p2p_url_field')) == $field) ? 'selected' : '') ?>><?= e(strtoupper($field)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <p><small class="text-muted">Givecloud will push the fundraising page's unique identifier (used in the URL) into this field into DP. If the code doesn't exist, one will be created.</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Sync Donor Data
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-user"></i> Sync Donor Data</div>
                    <div class="panel-sub-desc">Do you want to allow donor data to be pushed to DonorPerfect?</div>

                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group top-gutter">
                        <div class="col-md-8 col-md-offset-4">
                            <div class="row">
                                <div class="col-xs-7">When an admin user creates a supporter
                                    <br><small class="text-muted">Push admin created supporters to DonorPerfect.</small>
                                </div>

                                <div class="col-xs-5 text-right">
                                    <input type="checkbox" class="switch" value="1" name="admin_created_accounts_pushed_to_dpo" <?= e((sys_get('admin_created_accounts_pushed_to_dpo') == 1) ? 'checked' : '') ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group top-gutter">
                        <div class="col-md-8 col-md-offset-4">
                            <div class="row">
                                <div class="col-xs-7">When a donor logs in or updates their profile
                                    <br><small class="text-muted">Pull data from DonorPerfect when a donor logs in to ensure their profile in Givecloud matches their DonorPerfect profile. If the donor makes a change to their online profile, push the updates back to DonorPerfect.</small>
                                </div>
                                <div class="col-xs-5 text-right">
                                    <input type="checkbox" class="switch" value="1" name="allow_account_users_to_update_donor" <?= e((sys_get('allow_account_users_to_update_donor') == 1) ? 'checked' : '') ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group top-gutter">
                        <div class="col-md-8 col-md-offset-4">
                            <div class="row">
                                <div class="col-xs-7">Include email opt-in when syncing donor</div>
                                <div class="col-xs-5 text-right">
                                    <input type="checkbox" class="switch" value="1" name="dp_sync_noemail" <?= e((sys_get('dp_sync_noemail') == 1) ? 'checked' : '') ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group top-gutter">
                        <div class="col-md-8 col-md-offset-4">
                            <div class="row">
                                <div class="col-xs-7">Include salutation when syncing donor</div>
                                <div class="col-xs-5 text-right">
                                    <input type="checkbox" class="switch" value="1" name="dp_sync_salutation" <?= e(sys_get('dp_sync_salutation') == 1 ? 'checked' : '') ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group top-gutter">
                        <div class="col-md-8 col-md-offset-4">
                            <div class="row">
                                <div class="col-xs-7">Which field in DonorPerfect do you want to map phone numbers to?</div>
                                <div class="col-xs-5 text-right" style="margin-top:3px;">
                                    <select name="dp_phone_mapping" id="dp_phone_mapping" class="form-control">
                                        <option value="home_phone" <?= e(volt_selected(sys_get('dp_phone_mapping'), 'home_phone')); ?> >Home phone</option>
                                        <option value="business_phone" <?= e(volt_selected(sys_get('dp_phone_mapping'), 'business_phone')); ?> >Business phone</option>
                                        <option value="fax_phone" <?= e(volt_selected(sys_get('dp_phone_mapping'), 'fax_phone')); ?> >Fax phone</option>
                                        <option value="mobile_phone" <?= e(volt_selected(sys_get('dp_phone_mapping'), 'mobile_phone')); ?> >Mobile phone</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group top-gutter">
                        <div class="col-md-8 col-md-offset-4">
                            <div class="row">
                                <div class="col-xs-7">Include enroll/start date when syncing membership</div>
                                <div class="col-xs-5 text-right">
                                    <input type="checkbox" class="switch" value="1" name="dp_push_mcat_enroll_date" <?= e((sys_get('dp_push_mcat_enroll_date') == 1) ? 'checked' : '') ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-heart"></i> Sync Supporter Memberships
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-heart"></i> Sync Supporter Memberships</div>
                    <div class="panel-sub-desc">
                        Do you want to retrieve each supporter's Membership Type from DonorPerfect each time a supporter logs in?
                    </div>
                </div>

                <div class="col-sm-6 col-md-4 col-md-offset-2 col-lg-5 col-lg-offset-3">
                    <div class="radio">
                        <label>
                            <input name="keep_memberships_synced_with_dpo" type="radio" value="1" <?= e((sys_get('keep_memberships_synced_with_dpo') == 1) ? 'checked' : '') ?> >
                            <strong>Yes</strong>
                            <div class="text-muted">When supporter holders login, retrieve their Membership Type from DonorPerfect.</div>
                        </label>
                    </div>

                    <br />

                    <div class="radio">
                        <label>
                            <input name="keep_memberships_synced_with_dpo" type="radio" value="0" <?= e((sys_get('keep_memberships_synced_with_dpo') == 0) ? 'checked' : '') ?> >
                            <strong>No</strong>
                            <div class="text-muted">Manage memberships separately from DonorPerfect's Membership Types.</div>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user-times"></i> Anonymous Donors
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-user-times"></i> Anonymous Donors</div>
                    <div class="panel-sub-desc">
                        When using the POS, it's possible to enter transactions without entering any name or address information. This results in an anonymous purchase/donation. In this case, the financial data will still be pushed to DonorPerfect. What Donor ID do you want to use when there is no name or address to use?

                        <br /><br />
                        <span class="text-info">
                            <i class="fa fa-exclamation-circle"></i> <strong>Hint:</strong> Make a Donor in DonorPerfect called, "Anonymous Donor" and use their Donor ID here.
                        </span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Donor ID</label>
                        <div class="col-md-8">
                            <input type="text" autocomplete="off" class="form-control" placeholder="00000" name="dp_anonymous_donor_id" value="<?= e(sys_get('dp_anonymous_donor_id')) ?>" />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-truck"></i> Shipping Charges
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> Shipping Charges</div>
                    <div class="panel-sub-desc">Push shipping charges into DonorPerfect as a separate gift and its own coding.<br><br>One additional gift will be pushed to DonorPerfect per transaction representing the total amount of the shipping charged.</div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="shipping-charge-coding">

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">General Ledger</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GL_CODE" name="dp_shipping_gl" value="<?= e(sys_get('dp_shipping_gl')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta2" class="col-md-4 control-label">Campaign</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="CAMPAIGN" name="dp_shipping_campaign" value="<?= e(sys_get('dp_shipping_campaign')) ?>" />
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="meta3" class="col-md-4 control-label">Solicitation</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SOLICIT_CODE" name="dp_shipping_solicit" value="<?= e(sys_get('dp_shipping_solicit')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta4" class="col-md-4 control-label">Sub Solicitation</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SUB_SOLICIT_CODE" name="dp_shipping_subsolicit" value="<?= e(sys_get('dp_shipping_subsolicit')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta5" class="col-md-4 control-label">Gift Type</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GIFT_TYPE" name="dp_shipping_gift_type" value="<?= e(sys_get('dp_shipping_gift_type')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta7" class="col-md-4 control-label">TY Letter Code</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="TY_LETTER_NO" name="dp_shipping_ty_letter_code" value="<?= e(sys_get('dp_shipping_ty_letter_code')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta6" class="col-md-4 control-label">Fair Mkt. Value</label>
                            <div class="col-md-8">
                                <select name="dp_shipping_fair_mkt_val" class="form-control">
                                    <option value="0">Do Not Use</option>
                                    <option <?= dangerouslyUseHTML((sys_get('dp_shipping_fair_mkt_val')) ? 'selected="selected"' : '') ?> value="1" >Populate with Purchase Value</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta8" class="col-md-4 control-label">Gift Memo</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="dp_shipping_gift_memo" value="<?= e(sys_get('dp_shipping_gift_memo')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dp_shipping_no_calc" class="col-md-4 control-label">NoCalc</label>
                            <div class="col-md-8">
                                <select name="dp_shipping_no_calc" id="dp_shipping_no_calc" class="form-control">
                                    <option value=""> </option>
                                    <option value="Y" <?= e((sys_get('dp_shipping_no_calc') == 'Y') ? 'selected' : '') ?> >Y</option>
                                    <option value="N" <?= e((sys_get('dp_shipping_no_calc') == 'N') ? 'selected' : '') ?> >N</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dp_shipping_acknowledgepref" class="col-md-4 control-label">Acknowledge Preference</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="ACKNOWLEDGEPREF" name="dp_shipping_acknowledgepref" id="dp_shipping_acknowledgepref" value="<?= e(sys_get('dp_shipping_acknowledgepref')) ?>" maxlength="200" />
                            </div>
                        </div>

                        <?php foreach(array('meta9','meta10','meta11','meta12','meta13','meta14','meta15','meta16','meta17','meta18','meta19','meta20','meta21','meta22') as $field): ?>
                            <?php if (sys_get('dp_'.$field.'_field') !== null && sys_get('dp_'.$field.'_field') !== '') { ?>
                                <div class="form-group">
                                    <label for="dp_shipping_<?= e($field) ?>_value" class="col-md-4 control-label"><?= e(sys_get('dp_'.$field.'_label')) ?></label>
                                    <div class="col-md-8">
                                        <input type="text" <?php if(sys_get('dp_'.$field.'_autocomplete') == 1): ?>class="form-control dpo-codes" data-code="<?= e(sys_get('dp_'.$field.'_field')) ?>"<?php else: ?>class="form-control"<?php endif; ?> name="dp_shipping_<?= e($field) ?>_value" id="dp_shipping_<?= e($field) ?>_value" value="<?= e(sys_get('dp_shipping_'.$field.'_value')) ?>" maxlength="200" />
                                    </div>
                                </div>
                            <?php } ?>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-leaf"></i> "Donor Covers Costs" Charges
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-lg-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-leaf"></i> Donor Covers Costs (DCC)</div>
                    <div class="panel-sub-desc">DCC charges can be pushed into DonorPerfect as a separate gift.<br><br>If you choose to log it separately, one additional gift will be pushed to DonorPerfect per transaction representing the total amount of the administration fees charged.</div>
                </div>

                <div class="col-sm-6 col-lg-8">

                <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Log DCC as Separate Gift</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="dp_dcc_is_separate_gift" <?= e((sys_get('dp_dcc_is_separate_gift') == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('#dcc-charge-coding').removeClass('hide'); else $('#dcc-charge-coding').addClass('hide');">
                            <br><small class="text-muted">This will affect all <em>future contributions.</em></small>
                        </div>
                    </div>

                    <div id="dcc-charge-coding" class="<?= e((sys_get('dp_dcc_is_separate_gift') == 0) ? 'hide' : '') ?>">

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">General Ledger</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GL_CODE" name="dp_dcc_gl" value="<?= e(sys_get('dp_dcc_gl')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta2" class="col-md-4 control-label">Campaign</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="CAMPAIGN" name="dp_dcc_campaign" value="<?= e(sys_get('dp_dcc_campaign')) ?>" />
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="meta3" class="col-md-4 control-label">Solicitation</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SOLICIT_CODE" name="dp_dcc_solicit" value="<?= e(sys_get('dp_dcc_solicit')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta4" class="col-md-4 control-label">Sub Solicitation</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SUB_SOLICIT_CODE" name="dp_dcc_subsolicit" value="<?= e(sys_get('dp_dcc_subsolicit')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta5" class="col-md-4 control-label">Gift Type</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GIFT_TYPE" name="dp_dcc_gift_type" value="<?= e(sys_get('dp_dcc_gift_type')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta7" class="col-md-4 control-label">TY Letter Code</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="TY_LETTER_NO" name="dp_dcc_ty_letter_code" value="<?= e(sys_get('dp_dcc_ty_letter_code')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta6" class="col-md-4 control-label">Fair Mkt. Value</label>
                            <div class="col-md-8">
                                <select name="dp_dcc_fair_mkt_val" class="form-control">
                                    <option value="0">Do Not Use</option>
                                    <option <?= dangerouslyUseHTML((sys_get('dp_dcc_fair_mkt_val')) ? 'selected="selected"' : '') ?> value="1" >Populate with Purchase Value</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta8" class="col-md-4 control-label">Gift Memo</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="dp_dcc_gift_memo" value="<?= e(sys_get('dp_dcc_gift_memo')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dp_dcc_no_calc" class="col-md-4 control-label">NoCalc</label>
                            <div class="col-md-8">
                                <select name="dp_dcc_no_calc" id="dp_dcc_no_calc" class="form-control">
                                    <option value=""> </option>
                                    <option value="Y" <?= e((sys_get('dp_dcc_no_calc') == 'Y') ? 'selected' : '') ?> >Y</option>
                                    <option value="N" <?= e((sys_get('dp_dcc_no_calc') == 'N') ? 'selected' : '') ?> >N</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dp_dcc_acknowledgepref" class="col-md-4 control-label">Acknowledge Preference</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="ACKNOWLEDGEPREF" name="dp_dcc_acknowledgepref" id="dp_dcc_acknowledgepref" value="<?= e(sys_get('dp_dcc_acknowledgepref')) ?>" maxlength="200" />
                            </div>
                        </div>

                        <?php foreach(array('meta9','meta10','meta11','meta12','meta13','meta14','meta15','meta16','meta17','meta18','meta19','meta20','meta21','meta22') as $field): ?>
                            <?php if (sys_get('dp_'.$field.'_field') !== null && sys_get('dp_'.$field.'_field') !== '') { ?>
                                <div class="form-group">
                                    <label for="dp_dcc_<?= e($field) ?>_value" class="col-md-4 control-label"><?= e(sys_get('dp_'.$field.'_label')) ?></label>
                                    <div class="col-md-8">
                                        <input type="text" <?php if(sys_get('dp_'.$field.'_autocomplete') == 1): ?>class="form-control dpo-codes" data-code="<?= e(sys_get('dp_'.$field.'_field')) ?>"<?php else: ?>class="form-control"<?php endif; ?> name="dp_dcc_<?= e($field) ?>_value" id="dp_dcc_<?= e($field) ?>_value" value="<?= e(sys_get('dp_dcc_'.$field.'_value')) ?>" maxlength="200" />
                                    </div>
                                </div>
                            <?php } ?>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-university"></i> Taxes
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-university"></i> Tax Charges</div>
                    <div class="panel-sub-desc">Push taxes into DonorPerfect as a separate gift and its own coding.<br><br>One additional gift will be pushed to DonorPerfect per transaction representing the total amount of the taxes charged.</div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="tax-charge-coding">

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">General Ledger</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GL_CODE" name="dp_tax_gl" value="<?= e(sys_get('dp_tax_gl')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta2" class="col-md-4 control-label">Campaign</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="CAMPAIGN" name="dp_tax_campaign" value="<?= e(sys_get('dp_tax_campaign')) ?>" />
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="meta3" class="col-md-4 control-label">Solicitation</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SOLICIT_CODE" name="dp_tax_solicit" value="<?= e(sys_get('dp_tax_solicit')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta4" class="col-md-4 control-label">Sub Solicitation</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="SUB_SOLICIT_CODE" name="dp_tax_subsolicit" value="<?= e(sys_get('dp_tax_subsolicit')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta5" class="col-md-4 control-label">Gift Type</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="GIFT_TYPE" name="dp_tax_gift_type" value="<?= e(sys_get('dp_tax_gift_type')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta7" class="col-md-4 control-label">TY Letter Code</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control dpo-codes" data-code="TY_LETTER_NO" name="dp_tax_ty_letter_code" value="<?= e(sys_get('dp_tax_ty_letter_code')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta6" class="col-md-4 control-label">Fair Mkt. Value</label>
                            <div class="col-md-8">
                                <select name="dp_tax_fair_mkt_val" class="form-control">
                                    <option value="0">Do Not Use</option>
                                    <option <?= dangerouslyUseHTML((sys_get('dp_tax_fair_mkt_val')) ? 'selected="selected"' : '') ?> value="1" >Populate with Purchase Value</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta8" class="col-md-4 control-label">Gift Memo</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="dp_tax_gift_memo" value="<?= e(sys_get('dp_tax_gift_memo')) ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dp_tax_no_calc" class="col-md-4 control-label">NoCalc</label>
                            <div class="col-md-8">
                                <select name="dp_tax_no_calc" id="dp_tax_no_calc" class="form-control">
                                    <option value=""> </option>
                                    <option value="Y" <?= e((sys_get('dp_tax_no_calc') == 'Y') ? 'selected' : '') ?> >Y</option>
                                    <option value="N" <?= e((sys_get('dp_tax_no_calc') == 'N') ? 'selected' : '') ?> >N</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dp_tax_acknowledgepref" class="col-md-4 control-label">Acknowledge Preference</label>
                            <div class="col-md-8">
                                <input type="text" autocomplete="off" class="form-control <?= e(dpo_is_enabled() ? 'dpo-codes' : '') ?>" data-code="ACKNOWLEDGEPREF" name="dp_tax_acknowledgepref" id="dp_tax_acknowledgepref" value="<?= e(sys_get('dp_tax_acknowledgepref')) ?>" maxlength="200" />
                            </div>
                        </div>

                        <?php foreach(array('meta9','meta10','meta11','meta12','meta13','meta14','meta15','meta16','meta17','meta18','meta19','meta20','meta21','meta22') as $field): ?>
                            <?php if (sys_get('dp_'.$field.'_field') !== null && sys_get('dp_'.$field.'_field') !== '') { ?>
                                <div class="form-group">
                                    <label for="dp_tax_<?= e($field) ?>_value" class="col-md-4 control-label"><?= e(sys_get('dp_'.$field.'_label')) ?></label>
                                    <div class="col-md-8">
                                        <input type="text" <?php if(sys_get('dp_'.$field.'_autocomplete') == 1): ?>class="form-control dpo-codes" data-code="<?= e(sys_get('dp_'.$field.'_field')) ?>"<?php else: ?>class="form-control"<?php endif; ?> name="dp_tax_<?= e($field) ?>_value" id="dp_tax_<?= e($field) ?>_value" value="<?= e(sys_get('dp_tax_'.$field.'_value')) ?>" maxlength="200" />
                                    </div>
                                </div>
                            <?php } ?>
                        <?php endforeach; ?>
                    </div>


                </div>
            </div>

        </div>
    </div>

</div> <!-- /.form-horizontal -->

    <?php if (is_super_user()): ?>
        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-pencil"></i> Custom Gift Fields <span class="badge">SUPPORT</span>
            </div>
            <div class="panel-body">
                <div class="row">

                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-pencil"></i> Custom Integration Fields <span class="badge">SUPPORT</span></div>
                        <div class="panel-sub-desc">Use your DonorPerfect Gift UDF fields. You can have a maximum of 14 custom gift UDF fields. Need more? Let us know.</div>
                    </div>

                </div>

                <br>

                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Field Code</label>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Field Label</label>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Default Value</label>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Show Code Hints</label>
                        </div>
                    </div>
                </div>

                <?php foreach([9,10,11,12,13,14,15,16,17,18,19,20,21,22] as $ix): ?>
                    <div class="row">
                        <input type="hidden" class="form-control" name="dp_meta<?= e($ix) ?>_type" value="varchar">

                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Field Code <?= e($ix-8) ?>" name="dp_meta<?= e($ix) ?>_field" value="<?= e(sys_get('dp_meta'.$ix.'_field')) ?>">
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <input type="text" class="form-control"placeholder="Field Label <?= e($ix-8) ?>" name="dp_meta<?= e($ix) ?>_label" value="<?= e(sys_get('dp_meta'.$ix.'_label')) ?>">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" class="form-control"placeholder="Field Default <?= e($ix-8) ?>" name="dp_meta<?= e($ix) ?>_default" value="<?= e(sys_get('dp_meta'.$ix.'_default')) ?>">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <select class="form-control" name="dp_meta<?= e($ix) ?>_autocomplete">
                                    <option value="1" <?= e((sys_get('dp_meta'.$ix.'_autocomplete') == 1) ? 'selected' : '') ?> >Yes</option>
                                    <option value="0" <?= e((sys_get('dp_meta'.$ix.'_autocomplete') == 0) ? 'selected' : '') ?> >No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    <?php endif; ?>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-calculator"></i> Calculated Fields
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-calculator"></i> Calculated Fields</div>
                        <div class="panel-sub-desc">
                            Process custom calculations on gift sync
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4 col-md-offset-2 col-lg-5 col-lg-offset-3">
                        <div class="radio">
                            <label>
                                <input name="dp_trigger_calculated_fields" type="radio" value="1" <?= e((sys_get('dp_trigger_calculated_fields') == 1) ? 'checked' : '') ?> >
                                <strong>Yes</strong>
                                <div class="text-muted">Process calculated fields on gift sync.</small></div>
                            </label>
                        </div>

                        <br />

                        <div class="radio">
                            <label>
                                <input name="dp_trigger_calculated_fields" type="radio" value="0" <?= e((sys_get('dp_trigger_calculated_fields') == 0) ? 'checked' : '') ?> >
                                <strong>No</strong>
                                <div class="text-muted">Do not process calculated fields on gift sync.</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Created &amp; Modified By Name
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-user"></i> Created &amp; Updated By Name</div>
                    <div class="panel-sub-desc">When Givecloud creates or modifies records in DP, what name do you want to appear in the Created / Modified fields in DP?</div>
                </div>

                <div class="col-sm-6 col-md-8 form-horizontal">
                    <div class="form-group">
                        <label for="dpo-dpo_user_alias" class="col-md-4 control-label">Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="dpo-dpo_user_alias" name="dpo_user_alias" value="<?= e(sys_get('dpo_user_alias')) ?>" maxlength="32" required />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-list"></i> Multi-Client Configuration
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-list"></i> Multi-Client Configuration</div>
                    <div class="panel-sub-desc">If you are using one DonorPerfect system for multiple organizations.</div>
                </div>

                <div class="col-sm-6 col-md-8 form-horizontal">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="dp_use_link_scope" <?= e((sys_get('dp_use_link_scope') == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('#multi-client-config').removeClass('hide'); else $('#multi-client-config').addClass('hide');">
                            <br><small class="text-muted">This will affect all <em>future contributions.</em></small>
                        </div>
                    </div>
                    <div id="multi-client-config" class="<?= e((sys_get('dp_use_link_scope') == 0) ? 'hide' : '') ?>">
                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Link Code</label>
                            <div class="col-md-8">
                                <input type="text" name="dp_link_code" value="<?= e(sys_get('dp_link_code')) ?>" class="form-control">
                                <div class="text-muted">
                                    Example: CLIENT_ID
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Client Identifier</label>
                            <div class="col-md-8">
                                <input type="text" name="dp_link_donor_id2" value="<?= e(sys_get('dp_link_donor_id2')) ?>" class="form-control">
                                <div class="text-muted">
                                    Example: 1123
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php endif; ?>
</form>

</div></div>

<?php if (dpo_is_connected()): ?>

<div class="modal fade modal-info" tabindex="-1" id="dp-import-donor-modal" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-fw fa-download"></i> Import All DP Donors</h4>
            </div>
            <div class="modal-body">

                <p>This function will import all <strong><?= e(number_format(dpo_donorCount())) ?></strong> donors from your DonorPerfect system into GC.</p>

                <p><strong>Options:</strong></p>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="create_login" value="1" checked> Create logins and temporary passwords for those who have an email address
                    </label>
                </div>

                <!-- THIS CAN"T HAPPEN - we don't have a way of importing GL/SOLICIT/ETC codes <div class="checkbox">
                    <label>
                        <input type="checkbox" name="is_private" value="1"> Import Pledges as Recurring Txns
                    </label>
                </div>-->

                <p><small class="text-warning"><i class="fa fa-exclamation-circle"></i> This process could take approx <?= e(number_format(dpo_donorCount()*0.3/60)) ?> minutes (based on <?= e(number_format(dpo_donorCount())) ?> donors).  Please be patient as the import runs.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" onclick="j.importDonors();" class="btn btn-info"><i class="fa fa-download"></i> Import Now</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-info" tabindex="-1" id="modal-update-accounts-from-dp" role="dialog">
    <div class="modal-dialog modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-fw fa-download"></i> Update Givecloud Accounts</h4>
            </div>
            <form action="/jpanel/settings/dp/pull_data/donor" method="post">
                <?= dangerouslyUseHTML(csrf_field()) ?>

                <div class="modal-body">

                    <p>This function will update all <strong><?= e(number_format($account_count)) ?></strong> accounts in Givecloud that are linked to Donors in DP. <u>You will receive an automated email when the process is complete</u> as it could take a couple minutes.</p>

                    <p class="mt-3"><strong>Only update:</strong></p>

                    <?php if (sys_get('donor_title') != 'hidden'): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="options[]" value="donor_title" checked> Title <small>(Mr / Mrs / Ms)</small>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="options[]" value="email" checked> Email Address
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="options[]" value="address" checked> Address <small>(e.x. 123 Anywhere St, Beverly Hills, CA, 90210, US)</small>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="options[]" value="organization_name" checked> Organization Name <small>(Donor Last Name)</small><br>
                            <small class='text-muted'>Only when 'Is Organization'.</small>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <?php if (count($verified_donor_types) > 0): ?>
                                <input type="checkbox" name="options[]" value="donor_type" checked> Donor Type <small>(<?= e(implode('/',$verified_donor_types)) ?>)</small>
                                <br><small class="text-muted">Givecloud can pull in <?= e(count($verified_donor_types)) ?> different types. To add more, review your <a href="<?= e(route('backend.settings.supporters')) ?>">supporter types</a>.</small>
                            <?php else: ?>
                                <input type="checkbox" name="options[]" value="donor_type" disabled> <span class="text-muted">Donor Type</span>
                                <br><small class="text-danger">You must setup your <a href="<?= e(route('backend.settings.supporters')) ?>">supporter types</a> and make sure they map to DP donor types.</small>
                            <?php endif; ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-download"></i> Update Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>
