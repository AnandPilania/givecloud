
<form class="form-horizontal" action="/jpanel/settings/taxcloud/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Tax Cloud

            <div class="pull-right">
                <a href="https://help.givecloud.com/en/articles/3081919-taxcloud" target="_blank" class="btn btn-default btn-outline"><i class="fa fa-book"></i> Getting Started</a>
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-lock"></i> Login
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-key"></i> API Keys</div>
                    <div class="panel-sub-desc">
                        Enter your Login ID and API Key.

                        <br><br>
                        <span class="text-info"><i class="fa fa-info-circle"></i> <strong>Note:</strong> Your Login ID and API Key can be found in your 'Websites' settings panel in Tax Cloud.</span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">API Login ID</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="taxcloud_api_login_id" value="<?= e(sys_get('taxcloud_api_login_id')) ?>" maxlength="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">API Key</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control text-monospace" name="taxcloud_api_key" value="<?= e(sys_get('taxcloud_api_key')) ?>" maxlength="" />
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-truck"></i> Origin Address
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-truck"></i> Origin Address</div>
                    <div class="panel-sub-desc">
                        The origin address of all your shipments.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Address</label>
                        <div class="col-md-8">

                            <div class="row">

                                <div class="col-sm-12 bottom-gutter-sm">
                                    <input type="text" class="form-control" placeholder="Address Line 1" name="taxcloud_origin_address1" value="<?= e(sys_get('taxcloud_origin_address1')) ?>" maxlength="" />
                                </div>

                                <div class="col-sm-12 bottom-gutter-sm">
                                    <input type="text" class="form-control" placeholder="Address Line 2" name="taxcloud_origin_address2" value="<?= e(sys_get('taxcloud_origin_address2')) ?>" maxlength="" />
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control" placeholder="City" name="taxcloud_origin_city" value="<?= e(sys_get('taxcloud_origin_city')) ?>" maxlength="" />
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" maxlength="2" class="form-control" placeholder="State" name="taxcloud_origin_state" value="<?= e(sys_get('taxcloud_origin_state')) ?>" maxlength="" />
                                </div>

                                <div class="col-sm-4">
                                    <input type="tel" class="form-control" placeholder="ZIP" name="taxcloud_origin_zip" value="<?= e(sys_get('taxcloud_origin_zip')) ?>" maxlength="" />
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div></div>

</form>
