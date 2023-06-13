
<form action="/jpanel/settings/infusionsoft/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Infusionsoft

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save</button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

<div class="form-horizontal">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <?php if (!sys_get('infusionsoft_token')): ?>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-exchange"></i> Connect Infusionsoft
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div class="panel-sub-title"><i class="fa fa-exchange"></i> Connect Infusionsoft</div>
                        <div class="panel-sub-desc">Link an existing Infusionsoft account.</div>
                    </div>
                    <div class="col-sm-6 col-md-8">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4" style="margin-top:20px">
                                <a class="btn btn-primary btn-lg" href="<?= e($authorization_link) ?>">
                                    Connect Infusionsoft
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-exchange"></i> Connect Infusionsoft
            </div>
            <div class="panel-body">

                <div class="row">

                    <div class="col-sm-6 col-md-4">
                        <div class="panel-sub-title"><i class="fa fa-exchange"></i> Connect Infusionsoft</div>
                        <div class="panel-sub-desc">
                            <p>
                                By granting us third party access permissions, we're able to perform
                                Infusionsoft API operations on your behalf.
                            </p>
                            <p>
                                <a class="btn btn-danger btn-sm" href="/jpanel/settings/infusionsoft/disconnect"><i class="fa fa-times"></i> Disconnect</a>
                                <button type="button" class="btn btn-sm btn-default infusionsoft-test"><i class="fa fa-check-square-o"></i> Test Connection</button>&nbsp;
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-8">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4" style="margin-top:20px">
                                <span class="text-lg text-success">
                                    <i class="fa fa-check"></i> Connected
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading visible-xs">
                <i class="fa fa-users"></i> Syncing Contacts
            </div>
            <div class="panel-body">

                <div class="row">

                    <div class="col-sm-6 col-md-4">
                        <div class="panel-sub-title"><i class="fa fa-users"></i> Syncing Contacts</div>
                        <div class="panel-sub-desc">
                            <p>
                                Settings related to how Givecloud creates contact data in InfusionSoft.
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-8">

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Duplicate Check</label>
                            <div class="col-md-8">
                                <select class="form-control" disabled><option>Email</option></select>
                                <small class="text-info">Which fields do you want InfusionSoft to check when try to reduce duplicate records?</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Default Opt-In Reason</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="infusionsoft_default_optin_reason" value="<?= e(sys_get('infusionsoft_default_optin_reason')) ?>" placeholder="Example: Signed up from website">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta1" class="col-md-4 control-label">Opt-In Tag</label>
                            <div class="col-md-8">
                                <select class="form-control selectize" name="infusionsoft_optin_tag">
                                    <?php foreach($infusion_tags_by_categories as $category => $tags): ?>
                                        <optgroup label="<?= e($category) ?>">
                                            <?php foreach($tags as $tag): ?>
                                                <option
                                                    value="<?= e($tag->id) ?>"
                                                    <?= e(volt_selected($tag->id, sys_get('infusionsoft_optin_tag'))); ?>>
                                                    <?= e($tag->name) ?>
                                                </option>
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

    <?php endif ?>

</div> <!-- /.form-horizontal -->

</div></div>

</form>
