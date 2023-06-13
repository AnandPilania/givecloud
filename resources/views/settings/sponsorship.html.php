
<form class="form-horizontal" action="/jpanel/settings/sponsorship/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Sponsorship

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
            <i class="fa fa-pencil"></i> Customize
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-pencil"></i> Customize</div>
                    <div class="panel-sub-desc">
                        Change the language used to describe your sponsorship records to best fit your organization.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Single Record</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="syn_sponsorship_child" value="<?= e(sys_get('syn_sponsorship_child')) ?>" maxlength="" />
                            <small class="text-muted">For example: Child, Animal, Event, Person, Record, etc</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Plural Records</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="syn_sponsorship_children" value="<?= e(sys_get('syn_sponsorship_children')) ?>" maxlength="" />
                            <small class="text-muted">For example: Children, Animals, Events, People, Records, etc</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-reload"></i> Payment Behaviour
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-reload"></i> Recurring Payments</div>
                    <div class="panel-sub-desc">
                        How do you want the sponsorship module to behave when the status of recurring payments change?
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label class="col-md-4 control-label">When Suspended:<br><small class="text-muted">When a payment fails and you are waiting for a new payment.</small></label>
                        <div class="col-md-8">
                            <select class="form-control" name="sponsorship_end_on_rpp_suspend">
                                <option value="0" <?= e((sys_get('sponsorship_end_on_rpp_suspend') == '0') ? 'selected' : '') ?>>Do Nothing</option>
                                <option value="1" <?= e((sys_get('sponsorship_end_on_rpp_suspend') == '1') ? 'selected' : '') ?>>End Sponsorship</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">When Cancelled:<br><small class="text-muted">When a donor or staff member cancels their recurring commitment to pay.</small></label>
                        <div class="col-md-8">
                            <select class="form-control" name="sponsorship_end_on_rpp_cancel">
                                <option value="0" <?= e((sys_get('sponsorship_end_on_rpp_cancel') == '0') ? 'selected' : '') ?>>Do Nothing</option>
                                <option value="1" <?= e((sys_get('sponsorship_end_on_rpp_cancel') == '1') ? 'selected' : '') ?>>End Sponsorship</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default <?= e((sys_get('tax_receipt_pdfs') == 0) ? 'hide' : '') ?>">
        <div class="panel-heading visible-xs">
            <i class="fa fa-file"></i> Tax Receipts
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-file"></i> Tax Receipts</div>
                    <div class="panel-sub-desc">
                        Do you want Givecloud to issue Tax Receipts for all the Sponsorship payments you receive?
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">

                    <div class="radio">
                        <label><input name="sponsorship_tax_receipts" type="radio" value="1" <?= e((sys_get('sponsorship_tax_receipts') == 1) ? 'checked' : '') ?> > <i class="fa fa-check fa-fw"></i><strong> YES</strong>, Issue Tax Receipts</label><br>
                        <small class="text-muted">Givecloud will automatically <u>issue and send</u> a Tax Receipt for all Sponsorship Payments.</small><br>

                        <a href="/jpanel/settings/tax_receipts" class="btn btn-xs btn-info"><i class="fa fa-pencil"></i> Customize Email and Letter</a>
                    </div>

                    <br>
                    <div class="radio">
                        <label><input name="sponsorship_tax_receipts" type="radio" value="0" <?= e((sys_get('sponsorship_tax_receipts') == 0) ? 'checked' : '') ?> > <i class="fa fa-times fa-fw"></i><strong> NO</strong>, Do Not Issue Tax Receipts</label><br>
                        <small class="text-muted">Givecloud will not generate a tax receipt for any payments received in your Sponsorship program.</small>
                    </div>

                </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-eye"></i> Sponsored Records on Website
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-eye"></i> Sponsored Records on Website</div>
                    <div class="panel-sub-desc">
                        Do you want your supporters to be able to see records that have already been sponsored on your site?
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">

                    <div class="radio">
                        <label><input name="sponsorship_show_sponsored_on_web" type="radio" value="1" <?= e((sys_get('sponsorship_show_sponsored_on_web') == 1) ? 'checked' : '') ?> > <i class="fa fa-eye fa-fw"></i> Show sponsored records</label><br>
                        <small class="text-muted">This will also allow your supporters to filter by sponsored vs not-sponsored.</small>
                    </div>

                    <br>
                    <div class="radio">
                        <label><input name="sponsorship_show_sponsored_on_web" type="radio" value="0" <?= e((sys_get('sponsorship_show_sponsored_on_web') == 0) ? 'checked' : '') ?> > <i class="fa fa-eye-slash fa-fw"></i> Hide sponsored records</label><br>
                        <small class="text-muted">This will hide the sponsored vs not-sponsored filter and ensure no sponsored records display on your site.</small>
                    </div>

                </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Sponsor Options
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-user"></i> Sponsor Options</div>
                    <div class="panel-sub-desc">Do you want to allow logged in sponsors to be able to end their sponsorships?</div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Allow sponsors to end their sponsorships</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="allow_member_to_end_sponsorship" <?= e((sys_get('allow_member_to_end_sponsorship') == 1) ? 'checked' : '') ?>>
                            <br><small class="text-muted">If enabled, the 'End Sponsorship' button will appear in <i>My Sponsorships</i> when a sponsor is logged in.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Public end reasons</label>
                        <div class="col-md-8">
                            <select name="public_sponsorship_end_reasons[]" multiple class="form-control selectize-info selectize-tags auto-height">
                                <?php foreach(explode(',',sys_get('public_sponsorship_end_reasons')) as $source): ?>
                                    <option value="<?= e($source) ?>" selected><?= e($source) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-sort-alpha-asc"></i> Default Sorting on Website
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-sort-alpha-asc"></i> Default Sorting on Website</div>
                    <div class="panel-sub-desc">
                        When donors browse your database of children, you can decide the order in which your records display. By default, records are sorted in the order that they were created.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Sort By</label>
                        <div class="col-md-6">
                            <select class="form-control" name="sponsorship_default_sorting">
                                <option value="id_asc" <?= e((sys_get('sponsorship_default_sorting') == 'id_asc') ? 'selected' : '') ?> >Oldest Records First (Default)</option>
                                <option value="id_desc" <?= e((sys_get('sponsorship_default_sorting') == 'id_desc') ? 'selected' : '') ?> >Newest Records First</option>
                                <option value="ref_asc" <?= e((sys_get('sponsorship_default_sorting') == 'ref_asc') ? 'selected' : '') ?> >Reference Number (A-Z)</option>
                                <option value="ref_desc" <?= e((sys_get('sponsorship_default_sorting') == 'ref_desc') ? 'selected' : '') ?> >Reference Number (Z-A)</option>
                                <option value="fname_asc" <?= e((sys_get('sponsorship_default_sorting') == 'fname_asc') ? 'selected' : '') ?> >First Name (A-Z)</option>
                                <option value="fname_desc" <?= e((sys_get('sponsorship_default_sorting') == 'fname_desc') ? 'selected' : '') ?> >First Name (Z-A)</option>
                                <option value="lname_asc" <?= e((sys_get('sponsorship_default_sorting') == 'lname_asc') ? 'selected' : '') ?> >Last Name (A-Z)</option>
                                <option value="lname_desc" <?= e((sys_get('sponsorship_default_sorting') == 'lname_desc') ? 'selected' : '') ?> >Last Name (Z-A)</option>
                                <option value="random" <?= e((sys_get('sponsorship_default_sorting') == 'random') ? 'selected' : '') ?> >Random</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-birthday-cake"></i> Age of Maturity
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-birthday-cake"></i> Age of Maturity</div>
                    <div class="panel-sub-desc">
                        The age at which records are no longer sponsorable.

                        <br><br>
                        <div class="text-info">
                        <strong><i class="fa fa-exclamation-circle"></i> For example, </strong>when a child turns 18, you may no longer want them to be sponsored.
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Age</label>
                        <div class="col-md-4">
                            <input type="numeric" class="form-control" name="sponsorship_maturity_age" value="<?= e(sys_get('sponsorship_maturity_age')) ?>" maxlength="2" />
                            <small class="text-muted"></small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-exclamation-triangle"></i> Number of Sponsors
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-users"></i> Number of Sponsors</div>
                    <div class="panel-sub-desc"></div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Sponsors Needed to be Considered "Sponsored"</label>
                        <div class="col-md-8">
                            <input type="numeric" class="form-control" name="sponsorship_num_sponsors" value="<?= e(sys_get('sponsorship_num_sponsors')) ?>" maxlength="3" />
                            <div>
                                <small class="text-muted">Please note, updating this value will update the "sponsored" state of all children (unless they're sponsored status has been set manually).</small>
                                <br>
                                <div class="text-info">
                                    <strong><i class="fa fa-exclamation-circle"></i> For example, </strong>once a sponsorship record has 1 sponsor you may want them to be considered sponsored. Alternatively, you may only consider a sponsorship as sponsored when they have 4 sponsors.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Maximum Sponsors Allowed per Sponsorship</label>
                        <div class="col-md-8">
                            <input type="numeric" class="form-control" name="sponsorship_max_sponsors" value="<?= e(sys_get('sponsorship_max_sponsors')) ?>" maxlength="3" />
                            <div>
                                <small class="text-muted">Set this to blank to allow unlimited sponsorships.</small>
                                <br>
                                <div class="text-info">
                                    <strong><i class="fa fa-exclamation-circle"></i> For example, </strong>you may only want to allow 1 sponsor per sponsorship. Alternatively, you may not want to limit the number of sponsors. In this case, set the number to something large.
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
            <i class="fa fa-pie-chart"></i> Tracking &amp; Reporting
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-pie-chart"></i> Tracking &amp; Reporting</div>
                    <div class="panel-sub-desc">
                        Customize how to track and report your sponsorship records.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Sources</label>
                        <div class="col-md-8">
                            <select name="sponsorship_sources[]" multiple class="form-control selectize-info selectize-tags">
                                <?php foreach(explode(',',sys_get('sponsorship_sources')) as $source): ?>
                                    <option value="<?= e($source) ?>" selected><?= e($source) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">The source of any given sponsor. Website must always be an option.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">End Reasons</label>
                        <div class="col-md-8">
                            <select name="sponsorship_end_reasons[]" multiple class="form-control selectize-info selectize-tags">
                                <?php foreach(explode(',',sys_get('sponsorship_end_reasons')) as $source): ?>
                                    <option value="<?= e($source) ?>" selected><?= e($source) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">A pre-determined list of reasons a sponsorship might be terminated.</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div></div>

</form>
