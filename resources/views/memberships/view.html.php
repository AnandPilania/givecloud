
<script>
    function onDelete () {
        var f = confirm(<?= dangerouslyUseHTML(json_encode('Are you sure you want to delete this ' . strtolower(sys_get('syn_group')) . '?')) ?>);
        if (f) {
            document.membership.action = '/jpanel/memberships/destroy';
            document.membership.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <span class="page-header-text"><?= e($pageTitle) ?></span>

            <div class="pull-right">
                <?php if(!$membership->is_trashed): ?>
                    <a onclick="$('#membership-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e((!$membership->exists) ? 'hidden' : '') ?>"><i class="fa fa-trash fa-fw"></i></a>
                <?php endif; ?>
                <?php if($membership->exists): ?>
                    <a href="<?= e(route('backend.reports.members.index', ['group' => $membership->id])) ?>" class="btn btn-info"><i class="fa fa-users fa-fw"></i><span class="hidden-xs hidden-sm"> Members </span><span class="badge"><?= e($membership->member_count) ?></span></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<form name="membership" id="membership-form" method="post" action="/jpanel/memberships/save">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($membership->id) ?>" />


<div class="row">
    <div class="col-sm-4 col-md-3">

        <ul class="list-group" role="tablist" data-tabs="tabs">
            <a href="#general" role="tab" data-target="#general" data-toggle="tab" class="list-group-item active">General</a>
            <a href="#members" role="tab" data-target="#members" data-toggle="tab" class="list-group-item">Login</a>
            <a href="#benefits" role="tab" data-target="#benefits" data-toggle="tab" class="list-group-item">Discounts</a>
            <a href="#secure-content" role="tab" data-target="#secure-content" data-toggle="tab" class="list-group-item">Secure Content</a>
            <a href="#renewal" role="tab" data-target="#renewal" data-toggle="tab" class="list-group-item">Renewal</a>
        </ul>

        <?php if((user()->can('admin.dpo') && dpo_is_enabled()) || sys_get('infusionsoft_token')): ?>
            <ul class="list-group" role="tablist" data-tabs="tabs">
                <?php if(dpo_is_enabled()): ?><a href="#dpo-integration" role="tab" data-target="#dpo-integration" data-toggle="tab" class="list-group-item">DPO Integration</a><?php endif; ?>
                <?php if(sys_get('infusionsoft_token')): ?><a href="#infusionsoft-integration" role="tab" data-target="#infusionsoft-integration" data-toggle="tab" class="list-group-item">Infusionsoft</a><?php endif; ?>
            </ul>
        <?php endif; ?>

        <div>
            <a href="https://help.givecloud.com/en/articles/2822810-groups-memberships" target="_blank" rel="noreferrer"><i class="fa fa-book"></i> Learn More</a>
        </div>

        <?php if ($membership->exists): ?>
        <small class="text-muted hide">
            Created by <?= e($membership->createdBy->full_name) ?> on <?= e($membership->created_at) ?> EST.<br />
            Last modified by <?= e($membership->updatedBy->full_name) ?> on <?= e($membership->updated_at) ?> EST.
        </small>
        <?php endif; ?>
    </div>

    <div class="col-sm-8 col-md-9">

        <div class="tab-content">

            <div role="tabpanel" class="tab-pane active in" id="general">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        General
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">

                            <div class="form-group">
                                <label for="name" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                    Name<br>
                                    <small class="text-muted">The name of the <?= e(strtolower(sys_get('syn_group'))) ?>. Your <?= e(strtolower(sys_get('syn_group_members'))) ?> will see this.</small>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="name" id="name" value="<?= e($membership->name) ?>" maxlength="250" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="col-sm-6 control-label col-md-3 col-md-offset-1">Description
                                    <br>
                                    <small class="text-muted">Briefly describe this <?= e(strtolower(sys_get('syn_group'))) ?> and it's benefits. <strong>Internal use only.</strong></small></label>
                                <div class="col-sm-6 col-md-7">
                                    <textarea class="form-control" style="height:70px;" name="description" id="description" ><?= e($membership->description) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="starts_at" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                    Start Date<br>
                                    <small class="text-muted">Optionally choose the date on which this <?= e(strtolower(sys_get('syn_group'))) ?> is valid. If supplied, this will be the date the <?= e(strtolower(sys_get('syn_group'))) ?> will be effective, regardless of when it was purchased.</small>
                                </label>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-calendar fa-fw"></i></div>
                                        <input type="text" class="form-control datePretty" name="starts_at" placeholder="(Date of Purchase)" id="starts_at" value="<?= e(($membership->starts_at) ? $membership->starts_at->format('M j, Y') : '') ?>" />
                                    </div>

                                </div>
                            </div>

                            <div class="form-group">
                                <label for="days_to_expire" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                    Duration (Days)<br>
                                    <small class="text-muted">Choose the number of days this <?= e(strtolower(sys_get('syn_group'))) ?> is effective for, based on the purchase date or (if provided) the Start Date above.</small>
                                </label>
                                <div class="col-sm-6 col-md-7">
                                    <input type="text" class="form-control" name="days_to_expire" style="width:70px;" id="days_to_expire" value="<?= e($membership->days_to_expire) ?>" maxlength="4" />
                                </div>
                            </div>

                            <?php if($membership->exists): ?>
                                <div class="form-group">
                                    <label for="description" class="col-sm-6 control-label col-md-3 col-md-offset-1">ID
                                        <br>
                                        <small class="text-muted">Unique system ID you can use in signup shortcodes.</small>
                                    </label>
                                    <div class="col-sm-6 col-md-7">
                                        <div class="form-control-static"><?= e($membership->id) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                    Show Display Badge<br>
                                    <small class="text-muted">Should this <?= e(strtolower(sys_get('syn_group'))) ?> show beside the <?= e(strtolower(sys_get('syn_group_member'))) ?>'s user name in the top bar when they are logged into the site? If they belong to multiple <?= e(strtolower(sys_get('syn_groups'))) ?> with this setting turned on, only the latest <?= e(strtolower(sys_get('syn_group'))) ?> they've joined will show.</small>
                                </label>
                                <div class="col-sm-6 col-md-7">
                                    <input type="checkbox" class="form-control switch" value="1" name="should_display_badge" id="should_display_badge" <?= e(($membership->should_display_badge) ? 'checked' : '') ?>>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="panel panel-default hide">
                    <div class="panel-heading">
                        Other
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">

                            <div class="form-group">
                                <label for="sequence" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                    Sequence
                                    <br>
                                    <small class="text-muted">When this <?= e(strtolower(sys_get('syn_group'))) ?> displays in a list, this is the sequence in which the <?= e(strtolower(sys_get('syn_group'))) ?> will be displayed.</small>
                                </label>
                                <div class="col-sm-2 col-lg-1">
                                    <input type="text" class="form-control" name="sequence" id="sequence" value="<?= e($membership->sequence) ?>" maxlength="3" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div role="tabpanel" class="tab-pane" id="members">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-lock fa-fw"></i> Login
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">
                            <div class="form-group">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">Welcome Page<br><small class="text-muted">Select the welcome page that should display when a <?= e(strtolower(sys_get('syn_group_member'))) ?> from this <?= e(strtolower(sys_get('syn_group'))) ?> logs in.</small></label>
                                <div class="col-sm-6 col-md-7">
                                    <input type="text" class="form-control ds-urls" name="default_url" id="default_url" value="<?= e($membership->default_url) ?>" maxlength="250" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-lock fa-fw"></i> 'My Profile' Options
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">

                            <div class="form-group">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                    Show in 'My Profile'
                                </label>
                                <div class="col-sm-6 col-md-7">
                                    <input type="checkbox" class="form-control switch" value="1" name="show_in_profile" id="show_in_profile" <?= e(($membership->show_in_profile) ? 'checked' : '') ?>>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">Display Name<br><small class="text-muted">The display name for the membership.</small></label>
                                <div class="col-sm-6 col-md-7">
                                    <input type="text" name="public_name" value="<?= e($membership->public_name) ?>" placeholder="<?= e($membership->name) ?>" class="form-control" maxlength="250">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">Description<br><small class="text-muted">Briefly describe this membership.</small></label>
                                <div class="col-sm-6 col-md-7">
                                    <textarea name="public_description" class="form-control" maxlength="250"><?= e($membership->public_description) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group hidden">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">Allow Opt-Ins<br><small class="text-muted">Can non-members of this group opt-in to this group from their profile screen?</small></label>
                                <div class="col-sm-6 col-md-7">
                                    <select class="form-control" name="members_can_manage_optin">
                                        <option value="1">Display as an opt-in preference.</option>
                                        <option value="0" <?= e(($membership->members_can_manage_optout) ? '' : 'selected') ?> >DO-NOT display as an opt-in preference.</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group hidden">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">Allow Opt-Outs<br><small class="text-muted">Can members of this group opt-out of this group from their profile screen?</small></label>
                                <div class="col-sm-6 col-md-7">
                                    <select class="form-control" name="members_can_manage_optout">
                                        <option value="1">Display as an opt-out preference.</option>
                                        <option value="0" <?= e(($membership->members_can_manage_optout) ? '' : 'selected') ?> >DO-NOT display as an opt-out preference.</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane" id="benefits">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-lock fa-fw"></i> Discounts
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">

                            <div class="form-group">
                                <label for="default_promo_code" class="col-sm-6 control-label col-md-3 col-md-offset-1">Apply Promo(s)<br><small class="text-muted">Want to give <?= e(strtolower(sys_get('syn_group_members'))) ?> access to special pricing? You can select a secure promo code to automatically apply to a <?= e(strtolower(sys_get('syn_group_member'))) ?> once they login.</small></label>
                                <div class="col-sm-6 col-md-7">
                                    <select id="default_promo_code" name="default_promo_code[]" class="form-control selectize" multiple placeholder="Choose promo(s)...">
                                        <option value="">[ None ]</option>
                                        <?php foreach ($promos as $promo): ?>
                                            <option value="<?= e($promo->code) ?>" <?= dangerouslyUseHTML(($membership->promoCodes && in_array($promo->code, $membership->promoCodes->pluck('code')->toArray())) ?'selected="selected"':'') ?>><?= e($promo->code) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane" id="secure-content">

                <div class="alert alert-info alert-lg">
                    <strong><i class="fa fa-exclamation-circle"></i> Note:</strong> Each page, category and products you select to secure to this <?= e(strtolower(sys_get('syn_group'))) ?> will <u>no longer be accessible to the public</u>. The pages, categories and products you select here will only be accessible to <u>those who are logged in and have this <?= e(strtolower(sys_get('syn_group'))) ?></u>.<br><br>
                    Pages, categories and products can be locked to multiple <?= e(strtolower(sys_get('syn_groups'))) ?>.
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-lock fa-fw"></i> Secure Content
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">

                            <div class="form-group">
                                <label for="default_url" class="col-sm-3 control-label">
                                    Secure Pages
                                    <p>
                                        <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('input.node_ids_option').attr({checked:'checked'});">All</a>
                                        <a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="$('input.node_ids_option').removeAttr('checked');">None</a>
                                    </p>
                                </label>
                                <div class="col-sm-9">
                                    <div class="scolling_checkbox_select form-control">
                                        <div class="scolling_checkbox_select-inr">
                                            <?php $renderPageList = function ($pages) use (&$renderPageList, $membership_access) { ?>
                                                <ul>
                                                    <?php foreach ($pages as $page): ?>
                                                        <li>
                                                            <input type="checkbox" class="checkbox node_ids_option" name="node_ids[]" id="node_ids_<?= e($page->id) ?>" value="<?= e($page->id) ?>" <?= e(is_numeric(array_search($page->id, $membership_access->node_ids)) ? 'checked="checked"' : '') ?> />
                                                            <label style="font-size:11px;" for="node_ids_<?= e($page->id) ?>"><?= e($page->title) ?></label>
                                                            <?php if (count($page->pages)) $renderPageList($page->pages); ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php }; ?>

                                            <?php $renderPageList($pages); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="default_url" class="col-sm-3 control-label">
                                    Secure Categories
                                    <p>
                                        <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('input.category_ids_option').attr({checked:'checked'});">All</a>
                                        <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('input.category_ids_option').removeAttr('checked');">None</a>
                                    </p>
                                </label>
                                <div class="col-sm-9">
                                    <div class="scolling_checkbox_select form-control">
                                        <div class="scolling_checkbox_select-inr">
                                            <?php $renderCategoryList = function ($categories) use (&$renderCategoryList, $membership_access) { ?>
                                                <ul>
                                                    <?php foreach ($categories as $category): ?>
                                                        <li>
                                                            <input type="checkbox" class="checkbox category_ids_option" name="category_ids[]" id="category_ids_<?= e($category->id) ?>" value="<?= e($category->id) ?>" <?= e(is_numeric(array_search($category->id, $membership_access->category_ids)) ? 'checked="checked"' : '') ?> />
                                                            <label style="font-size:11px;" for="category_ids_<?= e($category->id) ?>"><?= e($category->name) ?></label>
                                                            <?php if (count($category->categories)) $renderCategoryList($category->categories); ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php }; ?>

                                            <?php $renderCategoryList($categories); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="default_url" class="col-sm-3 control-label">
                                    Secure Products
                                    <p>
                                        <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('input.product_ids_option').attr({checked:'checked'});">All</a>
                                        <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('input.product_ids_option').removeAttr('checked');">None</a>
                                    </p>
                                </label>
                                <div class="col-sm-9">
                                    <div class="scolling_checkbox_select form-control">
                                        <div class="scolling_checkbox_select-inr">
                                            <ul>
                                                <?php foreach ($products as $product): ?>
                                                    <li>
                                                        <input type="checkbox" class="checkbox product_ids_option" name="product_ids[]" id="product_ids_<?= e($product->id) ?>" value="<?= e($product->id) ?>" <?= dangerouslyUseHTML(is_numeric(array_search($product->id, $membership_access->product_ids)) ? 'checked="checked"' : '') ?> />
                                                        <label style="font-size:11px;" for="product_ids_<?= e($product->id) ?>"><?= e($product->name); ?> <span style="color:#999"><?= e($product->code); ?></span></label>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane" id="renewal">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-refresh fa-fw"></i> Renewal
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">
                            <div class="form-group">
                                <label for="renewal_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">Renewal URL<br><small class="text-muted">Select the link <?= e(strtolower(sys_get('syn_group_members'))) ?> must go to for renewing their <?= e(strtolower(sys_get('syn_group'))) ?>.</small></label>
                                <div class="col-sm-6 col-md-7">
                                    <input type="text" class="form-control ds-urls" name="renewal_url" id="renewal_url" value="<?= e($membership->renewal_url) ?>" maxlength="250" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(dpo_is_enabled()): ?>
                <div role="tabpanel" class="tab-pane" id="dpo-integration">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> DonorPerfect Integration

                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-gear fa-fw"></i> <i class="fa fa-caret-down"></i>
                                </button>
                                <ul class="dropdown-menu slidedown">
                                    <li><a href="#" class="dpo-codes-refresh"><i class="fa fa-refresh fa-fw"></i> Refresh DonorPerfect Codes</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="form-horizontal">

                                <?php if(user()->can('admin.dpo')): ?>
                                    <div class="form-group">
                                    <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1"><?= e(sys_get('syn_group')) ?> Type</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control dpo-codes" data-code="MCAT" name="dp_id" id="dp_id" value="<?= e($membership->dp_id) ?>" maxlength="250" />
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Check Your Permissions</p>
                                <?php endif; ?>

                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(sys_get('infusionsoft_token')): ?>
                <div role="tabpanel" class="tab-pane" id="infusionsoft-integration">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Infusionsoft Integration
                        </div>
                        <div class="panel-body">

                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">Tag(s)
                                        <br><small class="text-muted">Automatically sign tag(s) to each account that is a part of this group.</small></label>
                                    <div class="col-sm-6 col-md-8">
                                        <select class="form-control selectize" name="metadata[infusionsoft_tags][]" multiple>
                                            <?php foreach(app('Ds\Services\InfusionsoftService')->getTagsByCategory() as $category => $tags): ?>
                                                <optgroup label="<?= e($category) ?>">
                                                    <?php foreach($tags as $tag): ?>
                                                        <option value="<?= e($tag->id) ?>" <?= e((is_array($membership->metadata->infusionsoft_tags) && in_array($tag->id, $membership->metadata->infusionsoft_tags)) ? 'selected' : '') ?>><?= e($tag->name) ?></option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</form>
