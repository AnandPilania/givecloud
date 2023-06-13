
<form class="form-horizontal" action="/jpanel/settings/pos/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            POS Settings

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
            <i class="fa fa-pie-chart"></i> Sources
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-pie-chart"></i> Sources</div>
                    <div class="panel-sub-desc">
                        Track the source of each of your contributions.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Sources</label>
                        <div class="col-md-8">
                            <select name="pos_sources[]" multiple class="form-control selectize-info selectize-tags">
                                <?php foreach(explode(',',sys_get('pos_sources')) as $source): ?>
                                    <option value="<?= e($source) ?>" selected><?= e($source) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <!--<small class="text-muted">The source of any given sponsor. Website must always be an option.</small>-->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-bank"></i> Default Tax Region
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-bank"></i> Default Tax Region</div>
                    <div class="panel-sub-desc">
                        Set the default tax region for all POS contributions. This can be changed on each contribution.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-8 col-md-offset-4">

                    <div class="row">
                        <div class="col-sm-12 bottom-gutter-sm">
                            <select name="pos_tax_country" class="form-control">
                                <option></option>
                                <option value="CA" <?= e((sys_get('pos_tax_country') == 'CA') ? 'selected' : '') ?> >Canada</option>
                                <option value="US" <?= e((sys_get('pos_tax_country') == 'US') ? 'selected' : '') ?> >United States of America</option>
                            </select>
                        </div>

                        <div class="col-sm-12 bottom-gutter-sm">
                            <input type="text" class="form-control" name="pos_tax_address1" placeholder="Address Line 1" value="<?= e(sys_get('pos_tax_address1')) ?>">
                        </div>

                        <div class="col-sm-12 bottom-gutter-sm">
                            <input type="text" class="form-control" name="pos_tax_address2" placeholder="Address Line 2" value="<?= e(sys_get('pos_tax_address2')) ?>">
                        </div>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="pos_tax_city" placeholder="City" value="<?= e(sys_get('pos_tax_city')) ?>">
                        </div>

                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="pos_tax_state" placeholder="State" value="<?= e(sys_get('pos_tax_state')) ?>">
                        </div>

                        <div class="col-sm-4">
                            <input type="tel" class="form-control" placeholder="ZIP" name="pos_tax_zip" value="<?= e(sys_get('pos_tax_zip')) ?>" maxlength="" />
                        </div>
                    </div>

                    <hr>

                    <div class="form-group top-gutter">
                        <div class="col-sm-12">
                            <div class="checkbox">
                                <input type="checkbox" class="yes-no-switch" value="1" <?= e((sys_get('pos_use_default_tax_region') == 1) ? 'checked' : '') ?> name="pos_use_default_tax_region">
                                &nbsp; Use the customer's tax region, when available
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
            <i class="fa fa-eye"></i> Show Inactive Products
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-eye"></i> Show Inactive Products</div>
                    <div class="panel-sub-desc">
                        Products can have an active start and end date. This setting allows you to show products in POS even if the product's active start &amp; end dates have expired.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-4">

                    <div class="radio">
                        <label><input name="pos_allow_expired_products" type="radio" value="1" <?= e((sys_get('pos_allow_expired_products') == 1) ? 'checked' : '') ?> > <i class="fa fa-eye fa-fw"></i> Show inactive products</label><br>
                        <small class="text-muted">This will allow inactive products to display in your POS.</small>
                    </div>

                    <br>
                    <div class="radio">
                        <label><input name="pos_allow_expired_products" type="radio" value="0" <?= e((sys_get('pos_allow_expired_products') == 0) ? 'checked' : '') ?> > <i class="fa fa-eye-slash fa-fw"></i> Hide inactive records</label><br>
                        <small class="text-muted">This will keep inactive products from displaying in your POS.</small>
                    </div>

                </div>
                </div>

            </div>
        </div>
    </div>


</div></div>

</form>
