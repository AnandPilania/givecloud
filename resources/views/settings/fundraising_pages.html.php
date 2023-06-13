
<script>
__checkForm = function(){
    if ($('#product-ids').is(':visible') && $('#product-ids').val().length == 0) {
        $.alert('You must select atleast one fundraising option.');
        return false;
    }
    return true;
}
</script>

<form class="form-horizontal" action="/jpanel/settings/fundraising-pages/save" method="post" onsubmit="return __checkForm();">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Fundraising Pages

            <div class="pull-right">
                <a href="https://help.givecloud.com/en/articles/2213441-peer-to-peer-fundraising-pages" target="_blank" class="btn btn-default btn-outline"><i class="fa fa-book"></i> Getting Started</a>
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-users"></i> Fundraising Pages
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-users"></i> Fundraising Pages</div>
                    <div class="panel-sub-desc">
                        Allow your donors to create their own fundraising pages.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="fundraising_pages_enabled" <?= e((sys_get('fundraising_pages_enabled') == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('.fundraising-only').removeClass('hide'); else $('.fundraising-only').addClass('hide');">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="fundraising-only <?= e((sys_get('fundraising_pages_enabled') == 0) ? 'hide' : '') ?>">

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-cogs"></i> Fundraising Verification Options
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-cogs"></i> Fundraising Verification Options</div>
                        <div class="panel-sub-desc">
                            Control how fundraisers are verified
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-8">

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Require all fundraisers to be verified</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="yes-no-switch"
                                       value="1" <?= e(volt_checked(sys_get('fundraising_pages_requires_verify'), 1)); ?>
                                       name="fundraising_pages_requires_verify"
                                       onchange="if ($(this).prop('checked')) {
                                           $('.fundraising_pages_requires_verify').removeClass('hide'); } else { $('.fundraising_pages_requires_verify').addClass('hide'); }
                                        ">
                            </div>
                        </div>

                        <div class="form-group fundraising_pages_requires_verify <?= e((sys_get('fundraising_pages_requires_verify') != '1') ? 'hide' : '') ?>">
                            <label for="name" class="col-md-4 control-label">Auto-verify fundraisers</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="yes-no-switch"
                                       value="1"
                                        <?= e(sys_get('fundraising_pages_requires_verify') == 0 ? 'checked' : volt_checked(sys_get('fundraising_pages_auto_verifies'), 1)) ?>
                                       name="fundraising_pages_auto_verifies">
                                <br>
                                <small class="text-muted">
                                    If this is turned on, when a page is created, the fundraiser will be automatically verified if they donated in the past. (Recommended, saves time)
                                </small>
                            </div>
                        </div>

                        <?php if (! sys_get('fundraising_pages_did_verify_former_pages')): ?>
                        <div class="form-group fundraising_pages_requires_verify <?= e((sys_get('fundraising_pages_requires_verify') != '1') ? 'hide' : '') ?>">
                            <label for="name" class="col-md-4 control-label">Verify current fundraisers</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="yes-no-switch"
                                       value="1"
                                       <?= e(sys_get('fundraising_pages_requires_verify') == 0 ? 'checked' : volt_checked(sys_get('fundraising_pages_did_verify_former_pages'), 1)) ?>
                                       name="fundraising_pages_verify_former_pages">
                                <br>
                                <small class="text-muted">
                                    <span class="text-extra-bold">One-time use. </span>
                                    When this is activated, all supporters with active fundraising pages will be automatically marked as verified.
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group fundraising_pages_requires_verify <?= e((sys_get('fundraising_pages_requires_verify') != '1') ? 'hide' : '') ?>">
                            <label for="name" class="col-md-4 control-label">Pending fundraiser message</label>
                            <div class="col-md-8">
                                <textarea name="fundraising_page_pending_message" id="fundraising_page_pending_message" class="form-control input-html"><?= e(sys_get('fundraising_page_pending_message')) ?></textarea>
                                <br>
                            </div>
                        </div>

                        <div class="form-group fundraising_pages_requires_verify <?= e((sys_get('fundraising_pages_requires_verify') != '1') ? 'hide' : '') ?>">
                            <label for="name" class="col-md-4 control-label">Denied fundraiser message</label>
                            <div class="col-md-8">
                                <textarea name="fundraising_page_denied_message" id="fundraising_page_denied_message" class="form-control input-html"><?= e(sys_get('fundraising_page_denied_message')) ?></textarea>
                                <br>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-exclamation-triangle"></i> Fundraising Options
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-tags"></i> Fundraising Options</div>
                        <div class="panel-sub-desc">
                            Select which fundraising items your donors can fundraise for.
                            <br><br>

                            <span class="text-info"><i class="fa fa-exclamation-circle"></i> Every donation that is made will be counted as a sale against this item and inherit this item's finacial coding. For example, if all Fundraising Pages should just go to your general fund, select your General Fund donation item. If you want all the donations to be directed a special event or tournament, create a fundraising item for that tournament and select it here.</span>
                            <br><br>

                            <span class="text-info"><i class="fa fa-exclamation-circle"></i> If you select multiple donation items, your fundraisers will be able to choose the type of page they will create.</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-8">
                        <div class="form-group">
                            <label for="product-ids" class="col-md-4 control-label">Fundraising For</label>
                            <div class="col-md-8">
                                <select id="product-ids" name="fundraising_product_ids[]" multiple size="1" class="form-control ds-products auto-height">
                                    <?php foreach (\Ds\Models\Product::whereAllowFundraisingPages(1)->get() as $product): ?>
                                        <option value="<?= e($product->id) ?>" selected><?= e($product->name) ?></option>
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
                <i class="fa fa-exclamation-triangle"></i> Categories
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-tags"></i> Categories</div>
                        <div class="panel-sub-desc">
                            Organize your fundraising pages by categories.
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-8">

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Options</label>
                            <div class="col-md-8">
                                <select name="fundraising_pages_categories[]" multiple size="1" class="form-control selectize-info selectize-tags auto-height">
                                    <?php foreach (explode(',', sys_get('fundraising_pages_categories')) as $category): ?>
                                        <option value="<?= e($category) ?>" selected><?= e($category) ?></option>
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
                <i class="fa fa-cogs"></i> Fundraising Guidelines
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-cogs"></i> Fundraising Guidelines</div>
                        <div class="panel-sub-desc">
                            Require fundraisers to agree to these guidelines before they can fundraise.
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-8">

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Enable</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="yes-no-switch"
                                       value="1" <?= e(volt_checked(sys_get('fundraising_pages_require_guideline_acceptance'), 1)); ?>
                                       name="fundraising_pages_require_guideline_acceptance"
                                       onchange="$('.fundraising_pages_require_guideline_acceptance').toggleClass('hide', !$(this).prop('checked'))">
                            </div>
                        </div>

                        <div class="form-group fundraising_pages_require_guideline_acceptance <?= e(sys_get('fundraising_pages_require_guideline_acceptance') != '1' ? 'hide' : ''); ?>">
                            <label for="name" class="col-md-4 control-label">Guidelines</label>
                            <div class="col-md-8">
                                <textarea name="fundraising_pages_guidelines" id="fundraising_pages_guidelines" rows="5" class="form-control input-html"><?= e(sys_get('fundraising_pages_guidelines')) ?></textarea>
                                <small class="text-muted">
                                    When this is activated, fundraisers will see these guidelines before they can create a fundraising page and must agree to follow them.
                                </small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-comment-exclamation"></i> Profanity filter
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-comment-exclamation"></i> Profanity filter</div>
                        <div class="panel-sub-desc">
                            Enables the profanity filter on fundraising pages.
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-8">

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Enable</label>
                            <div class="col-md-8">
                                <input type="checkbox" class="yes-no-switch"
                                       value="1"
                                        <?= e(volt_checked(sys_get('fundraising_pages_profanity_filter'), 1)) ?>
                                       name="fundraising_pages_profanity_filter">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-exclamation-triangle"></i> Reporting Abuse
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-exclamation-triangle"></i> Reporting Abuse</div>
                        <div class="panel-sub-desc">
                            Visitors can report a page for being inappropriate.
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-8">

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Reasons</label>
                            <div class="col-md-8">
                                <select name="fundraising_pages_report_reasons[]" multiple size="1" class="form-control selectize-info selectize-tags auto-height">
                                    <?php foreach (explode(',', sys_get('fundraising_pages_report_reasons')) as $reason): ?>
                                        <option value="<?= e($reason) ?>" selected><?= e($reason) ?></option>
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
                <i class="fa fa-envelope-o"></i> Staff Notifications
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-envelope-o"></i> Staff Notifications</div>
                        <div class="panel-sub-desc">
                            Choose who in your organization should be notified when specific actions take place on fundraising pages.
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-8">

                        <?php $users = \Ds\Models\User::orderBy('firstname')->orderBy('lastname')->get(); ?>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Page Activated</label>
                            <div class="col-md-8">
                                <select name="notify_fundraising_page_activated[]" multiple size="1" class="form-control selectize-info selectize-tags" placeholder="Select user(s)...">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= e($user->id) ?>" <?= e(($user->notify_fundraising_page_activated) ? 'selected' : '') ?>><?= e($user->full_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Page Edited</label>
                            <div class="col-md-8">
                                <select name="notify_fundraising_page_edited[]" multiple size="1" class="form-control selectize-info selectize-tags" placeholder="Select user(s)...">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= e($user->id) ?>" <?= e(($user->notify_fundraising_page_edited) ? 'selected' : '') ?>><?= e($user->full_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group hide">
                            <label for="meta1" class="col-md-4 control-label">Page Closed</label>
                            <div class="col-md-8">
                                <select name="notify_fundraising_page_closed[]" multiple size="1" class="form-control selectize-info selectize-tags" placeholder="Select user(s)...">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= e($user->id) ?>" <?= e(($user->notify_fundraising_page_closed) ? 'selected' : '') ?>><?= e($user->full_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Abuse Reported</label>
                            <div class="col-md-8">
                                <select name="notify_fundraising_page_abuse[]" multiple size="1" class="form-control selectize-info selectize-tags" placeholder="Select user(s)...">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= e($user->id) ?>" <?= e(($user->notify_fundraising_page_abuse) ? 'selected' : '') ?>><?= e($user->full_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

</div></div>

</form>
