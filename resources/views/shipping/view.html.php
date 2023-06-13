
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this shipping option?');
        if (f) {
            document.ship.action = '/jpanel/shipping/destroy';
            document.ship.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <a onclick="$('#ship-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                <a onclick="onDelete();" class="btn btn-danger <?= e((! $method->exists) ? 'hidden' : '') ?>"><i class="fa fa-times fa-fw"></i><span class="hidden-xs hidden-sm"> Delete</span></a>
            </div>
        </h1>
    </div>
</div>


<form name="ship" method="post" id="ship-form" action="/jpanel/shipping/save">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($method->id) ?>" />

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="bottom-gutter">
                <div class="panel-sub-title"><i class="fa fa-pencil"></i> General</div>
                <div class="panel-sub-desc">
                    Some general info about this shipping method.
                </div>
            </div>

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Name</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" name="name" id="name" value="<?= e($method->name) ?>" maxlength="150" />
                        <small class="text-muted">This is the name of the shipping method that will display to each donor/customer.</small>
                    </div>
                </div>

                <div class="form-group hidden">
                    <label for="name" class="col-sm-3 control-label">Type</label>
                    <div class="col-sm-4">
                        <select name="code" id="code" class="form-control">
                            <option value="standard" <?= dangerouslyUseHTML(($method->code === 'standard')?'selected="selected"':'') ?>>Standard</option>
                            <option value="expedited" <?= dangerouslyUseHTML(($method->code === 'expedited')?'selected="selected"':'') ?>>Expedited</option>
                            <option value="next_day" <?= dangerouslyUseHTML(($method->code === 'next_day')?'selected="selected"':'') ?>>Next Day</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="col-sm-3 control-label">Description</label>
                    <div class="col-sm-7">
                        <textarea class="form-control" style="height:70px;" name="description" id="description" ><?= e($method->description) ?></textarea>
                        <small class="text-muted">This is typically an internal reference only. However, some website themes will display this description to the donor/customer.</small>
                    </div>
                </div>

                <div class="form-group hidden">
                    <label for="show_on_web" class="col-sm-3 control-label">Show on Web</label>
                    <div class="col-sm-7">
                        <select name="show_on_web" class="form-control" id="show_on_web">
                            <option value="1" <?= dangerouslyUseHTML(($method->show_on_web == 1)?'selected="selected"':'') ?>>Yes - Show as a Shipping Option</option>
                            <option value="0" <?= dangerouslyUseHTML(($method->show_on_web == 0)?'selected="selected"':'') ?>>No - Do not show as a Shipping Option</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="is_default" class="col-sm-3 control-label">Is Default</label>
                    <div class="col-sm-7">
                        <select name="is_default" id="is_default" class="form-control">
                            <option value="0" <?= dangerouslyUseHTML(($method->is_default == 0)?'selected="selected"':'') ?>>No - This is not the default Shipping Option</option>
                            <option value="1" <?= dangerouslyUseHTML(($method->is_default == 1)?'selected="selected"':'') ?>>Yes - This is the default Shipping Option</option>
                        </select>
                        <small class="text-muted">If you set this method to the <em>default</em> method, every donor/customer will have this method selected automatically when they start the checkout process.  They can select a different method if they wish.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="priority" class="col-sm-3 control-label">Sequence</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" style="width:100px;" name="priority" id="priority" value="<?= e($method->priority) ?>" maxlength="2" />
                        <small class="text-muted">The sequence helps determine which method to select by default in complex scenarios where regions are begin used.</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="bottom-gutter">
                <div class="panel-sub-title"><i class="fa fa-map-pin"></i> Geography</div>
                <div class="panel-sub-desc">
                    You can optionally limit the availability of this shipping method to specific countries or regions.
                </div>
            </div>

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Country(s)</label>
                    <div class="col-sm-7">
                        <select class="form-control selectize" multiple name="countries[]" placeholder="Any Country">
                            <?php foreach(cart_countries() as $code => $country): ?>
                                <option value="<?= e($code) ?>" <?= e((is_array($method->countries) && in_array($code, $method->countries))?'selected':'') ?> ><?= e($country) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Which countries is this shipping method available in?</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Region(s)</label>
                    <div class="col-sm-7">
                        <select class="form-control selectize" multiple name="regions[]" placeholder="Any Region">
                            <?php foreach(\Ds\Models\Region::all() as $region): ?>
                                <option value="<?= e($region->code) ?>" <?= e((is_array($method->regions) && in_array($region->code, $method->regions))?'selected':'') ?> ><?= e($region->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Which regions is this shipping method available in?</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="bottom-gutter">
                <div class="panel-sub-title"><i class="fa fa-sitemap"></i> Tier Pricing</div>
                <div class="panel-sub-desc">
                    Set the price of this shipping method at the different tiers. Each tier min/max amount represents the total amount of the contribution, not the amount of an individual item.  For example, if there were 3 x $30 items in the contribution, that would put the contribution in the $90 shipping tier.
                </div>
            </div>

            <div class="form-horizontal">

                <?php foreach($tiers as $tier): ?>

                    <div class="form-group">
                        <label for="tier_<?= e($tier->id) ?>" class="col-sm-3 control-label"><?= e(money($tier->min_value)) ?> <?= e(($tier->is_infinite)?'+':'- '.money($tier->max_value)) ?></label>
                        <div class="col-sm-4 col-lg-3">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-dollar fa-fw"></i></div>
                                <?php $_amount = (float) db_var("select amount from shipping_value where method_id = %d and tier_id = %d", [$method->id, $tier->id]) ?>
                                <input type="text" class="form-control text-right" name="tier[<?= e($tier->id) ?>]" id="tier_<?= e($tier->id) ?>" value="<?= e(number_format($_amount,2)) ?>" maxlength="14" />
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

        </div>
    </div>




</form>
