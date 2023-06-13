
<form class="form-horizontal" action="<?= e(route('backend.settings.supporters_save')) ?>" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Supporters

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
            <i class="fa fa-user"></i> Supporter Types
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-user"></i> Supporter Types</div>
                    <div class="panel-sub-desc">Track different types of supporters in your system. Organizations vs individuals.
                        <br><br>
                        <a href="<?= e(route('backend.supporter_types.add')) ?>" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add a Supporter Type</a>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Supporter Types</label>
                        <div class="col-md-8">

                            <table class="table table-striped">
                                <tbody>
                                    <?php foreach (\Ds\Models\AccountType::all() as $type): ?>
                                        <tr>
                                            <td style="width:16px;"><a href="<?= e(route('backend.supporter_types.edit', $type->getKey())) ?>"><i class="fa fa-search"></i></a></td>
                                            <td><?= e($type->name) ?></td>
                                            <td>
                                                <?php if($type->is_organization): ?><span class="badge">Organization</span><?php endif; ?>
                                                <?php if(!$type->on_web): ?><span class="badge">Hide</span><?php endif; ?>
                                                <?php if($type->is_default): ?><span class="badge"><i class="fa fa-check"></i> Default</span><?php endif; ?>
                                            </td>
                                            <td style="width:16px;"><?php if(!$type->is_protected): ?><a href="<?= e(route('backend.supporter_types.destroy', $type->getKey())) ?>" class="text-danger"><i class="fa fa-times"></i></a><?php endif; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Show on Web</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="yes-no-switch" value="1" <?= e((sys_get('allow_account_types_on_web') == 1) ? 'checked' : '') ?> name="allow_account_types_on_web">
                                <br><small class="text-muted">If enabled, donors will be able to select their supporter type during donations, during checkout, during sign-up and in their profile screen.</small>
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Referral Sources
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-question-circle"></i> Referral Sources</div>
                    <div class="panel-sub-desc"><span class="text-info">"How'd you hear about us?"</span><br><br>Keep track of how people have heard about your organization.</div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="yes-no-switch" value="1" <?= e((sys_get('referral_sources_isactive') == 1) ? 'checked' : '') ?> name="referral_sources_isactive" onchange="if ($(this).prop('checked')) { $('#referral-sources-options').removeClass('hide'); } else { $('#referral-sources-options').addClass('hide'); }">
                            <br><small class="text-muted">If enabled, a dropdown menu with a list of specified options will be displayed on your checkout pages.</small>
                        </div>
                    </div>

                    <div id="referral-sources-options" class="<?= e((sys_get('referral_sources_isactive') == '0') ? 'hide' : '') ?>">

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Options</label>
                            <div class="col-md-8">
                                <select name="referral_sources_options[]" multiple class="form-control selectize-info selectize-tags auto-height">
                                    <?php foreach(explode(',',sys_get('referral_sources_options')) as $source): ?>
                                        <option value="<?= e($source) ?>" selected><?= e($source) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="referral_sources_other" value="1" <?= e((sys_get('referral_sources_other') == 1) ? 'checked' : '') ?>> Allow 'Other' option
                                    </label>
                                </div>
                                <small class="text-muted">Adds the option 'Other' to the list of options. If 'Other' is selected, a text field will display that will allow a user to enter their own referral source.</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-globe"></i> Countries
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-globe"></i> Countries</div>
                    <div class="panel-sub-desc">Customize how donors select their country when paying or registering.</div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Default Country</label>
                        <div class="col-md-8">
                            <select name="default_country" class="form-control selectize">
                                <?php foreach(cart_countries() as $iso_code => $name): ?>
                                    <option value="<?= e($iso_code) ?>" <?= e(($iso_code == sys_get('default_country')) ? 'selected' : '') ?>><?= e($name) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="hide_other_countries" value="1" <?= e((sys_get('force_country') == sys_get('default_country')) ? 'checked' : '') ?> > Always force this country. <small class="text-muted">(Pinned countries will be ignored)</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Pinned Countries</label>
                        <div class="col-md-8">
                            <select name="pinned_countries[]" class="form-control selectize auto-height" multiple="multiple">
                                <?php foreach(cart_countries() as $iso_code => $name): ?>
                                    <option value="<?= e($iso_code) ?>" <?= e((in_array($iso_code,sys_get('list:pinned_countries'))) ? 'selected' : '') ?>><?= e($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Pin a handful of popular countries to the top of the country selector to make it easier for your donors to find their country.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Net Promoter Score
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-heart"></i> Net Promoter Score</div>
                    <div class="panel-sub-desc"><span class="text-info">"How likely are you to recommend us?"</span><br><br>Keep track of how likely your donors are to promote your organization.<br /><br /><a href="https://help.givecloud.com/en/articles/3083151-donor-loyalty-using-nps" target="_blank" rel="noreferrer">Learn More</a></div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="yes-no-switch" value="1" <?= e((sys_get('nps_enabled') == 1) ? 'checked' : '') ?> name="nps_enabled">
                            <br><small class="text-muted">If enabled, your donors will be asked to provide feedback at touch-points you configure.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Supporter Titles
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-user"></i> Supporter Titles</div>
                    <div class="panel-sub-desc"><span class="text-info">Mr. / Mrs. / Ms. / Dr. / Prof.</span><br><br>Track the optional prefix or title supporters can choose from.</div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="yes-no-switch" value="1" <?= e((sys_get('donor_title') != 'hidden') ? 'checked' : '') ?> name="donor_title-is_enabled" onchange="if ($(this).prop('checked')) { $('#donor-title-options').removeClass('hide'); } else { $('#donor-title-options').addClass('hide'); }">
                        </div>
                    </div>

                    <div id="donor-title-options" class="<?= e((sys_get('donor_title') == 'hidden') ? 'hide' : '') ?>">

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="radio">
                                            <label>
                                                <input name="donor_title-is_required" type="radio" value="0" <?= e((sys_get('donor_title') != 'required') ? 'checked' : '') ?> >
                                                <strong>Optional</strong>
                                                <div class="text-muted">
                                                    <small>Do not force donors to provide their title during a donation or purchase.</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="radio">
                                            <label>
                                                <input name="donor_title-is_required" type="radio" value="1" <?= e((sys_get('donor_title') == 'required') ? 'checked' : '') ?> >
                                                <strong>Required</strong>
                                                <div class="text-muted">
                                                    <small>Force all donors to provide their title during any donation or purchase.</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!--<select name="donor_title" class="form-control">
                                    <option value="hidden" <?= e((sys_get('donor_title') == 'hidden') ? 'selected' : '') ?> >Hidden</option>
                                    <option value="required" <?= e((sys_get('donor_title') == 'required') ? 'selected' : '') ?> >Required</option>
                                    <option value="optional" <?= e((sys_get('donor_title') == 'optional') ? 'selected' : '') ?> >Optional</option>
                                </select>
                                <small class="text-muted">Specifies whether the donor title field should be hidden, required or optional.</small>-->
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Fixed Options<br><small class="text-muted">(Optional)</small></label>
                            <div class="col-md-8">
                                <select name="donor_title_options[]" multiple class="form-control selectize-info selectize-tags">
                                    <?php foreach(explode(',',sys_get('donor_title_options')) as $source): ?>
                                        <option value="<?= e($source) ?>" selected><?= e($source) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Ex: Mr, Mrs, Dr. If no options are defined, a text box will be displayed instead of a dropdown menu.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-envelope"></i> Marketing Opt-Out Options
        </div>

        <div class="panel-body clearfix">
            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title">
                        <i class="fa fa-envelope"></i> Marketing Opt-Out Options
                    </div>
                    <div class="panel-sub-desc">
                        <span class="text-info">Manage reasons supporters can select when they opt-out of your mailing list.</span>
                        <br><br>
                        Keep your supporters in the loop.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Options</label>
                        <div class="col-md-8">
                            <select
                                name="marketing_optout_options[]"
                                class="form-control selectize-info selectize-tags auto-height"
                                multiple>
                                <?php foreach(explode(',', sys_get('marketing_optout_options')) as $source): ?>
                                    <option value="<?= e($source) ?>" selected><?= e($source) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="checkbox">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="referral_sources_other"
                                        value="1"
                                        <?= e(volt_checked(sys_get('marketing_optout_other'), 1)); ?>>
                                        Allow 'Other' option
                                </label>
                            </div>
                            <small class="text-muted">
                                Adds the option 'Other' to the list of options.
                                If 'Other' is selected, a text field will display that will allow a user to enter their own reason.
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Required</label>
                        <div class="col-md-8">
                            <input
                                type="checkbox"
                                name="marketing_optout_reason_required"
                                class="yes-no-switch"
                                value="1"
                                <?= e(volt_checked(sys_get('marketing_optout_reason_required'), 1)); ?>>
                            <br>
                            <small class="text-muted">
                                If enabled, supporters must provide a reason when opting out.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user-plus"></i> Social Login
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-user-plus"></i> Social Login</div>
                    <div class="panel-sub-desc"><span class="text-info">
                        Allow your supporters to login to your donor portal using a social login.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="yes-no-switch" value="1" <?= e(feature('social_login') ? 'checked' : '') ?> name="feature_social_login">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-lock"></i> Donor Portal Features
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-lock"></i> Donor Portal Features</div>
                    <div class="panel-sub-desc"></div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <?php
                        $all_account_features = [
                            'Profile & Addresses'           => 'heading',
                            'view-profile'                  => 'View Profile (Name & Email)',
                            'view-billing'                  => 'View Billing Address',
                            'view-shipping'                 => 'View Shipping Address',
                            'edit-profile'                  => 'Change Profile (Name & Email)',
                            'edit-billing'                  => 'Change Billing Address',
                            'edit-shipping'                 => 'Change Shipping Address',
                            'view-purchased-media'          => 'View Purchased Media',

                            'Payments & Receipts'           => 'heading',
                            'view-orders'                   => 'View Past Donations/Contributions',
                            'view-receipts'                 => 'View Tax Receipts',
                            'view-giving-impact'            => 'View Giving Impact',

                            'Pledges'                       => 'heading',
                            'view-pledges'                  => 'View Pledges',

                            'Payment Methods'               => 'heading',
                            'view-payment-methods'          => 'View Payment Methods',
                            'edit-payment-methods'          => 'Change Payment Methods',
                            'delete-default-payment-method' => 'Delete Default Payment Method',

                            'Sponsorships'                  => 'heading',
                            'view-sponsorships'             => 'View Sponsorships',
                            'end-sponsorships'              => 'End Sponsorships',

                            sys_get('syn_groups')           => 'heading',
                            'view-memberships'              => 'View ' . sys_get('syn_groups'),
                            /*'edit-sponsorship-amount'       => 'Change Sponsorship Amount',
                            'edit-sponsorship-date'         => 'Change Sponsorship Date',
                            'edit-sponsorship-frequency'    => 'Change Sponsorship Frequency',*/

                            'Fundraisers'                   => 'heading',
                            'view-fundraisers'              => 'View Fundraisers',
                            'edit-fundraisers'              => 'Edit Fundraisers',

                            'Recurring Payments'            => 'heading',
                            'view-subscriptions'            => 'View Recurring Payments',
                            'end-subscriptions'             => 'End Recurring Payments',
                            'edit-subscription-amount'      => 'Change Recurring Payment Amount',
                            'edit-subscription-date'        => 'Change Recurring Payment Date',
                            'edit-subscription-frequency'   => 'Change Recurring Payment Frequency'
                        ];
                    ?>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Enabled Features</label>
                        <div class="col-md-8">
                            <div class="row" style="margin-top:-8px;">
                                <?php foreach ($all_account_features as $feature => $description): ?>
                                    <?php if ($description == 'heading'): ?>
                                        <div class="col-xs-12" style="margin-top:12px;">
                                            <span style="color:#999; font-size:10px; font-weight:bold; text-transform:uppercase;"><?= e($feature) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="account_login_features[]" value="<?= e($feature) ?>" <?= e(volt_checked($feature, explode(',',sys_get('account_login_features')))); ?> > <?= e($description) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div></div>

</form>
