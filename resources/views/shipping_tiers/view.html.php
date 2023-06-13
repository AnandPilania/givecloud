
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this shipping tier?');
        if (f) {
            document.tier.action = '/jpanel/shipping/tiers/destroy';
            document.tier.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <a onclick="$('#shipping-tier-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                <a onclick="onDelete();" class="btn btn-danger <?= e(($isNew == 1) ? 'hidden' : '') ?>"><i class="fa fa-times fa-fw"></i><span class="hidden-xs hidden-sm"> Delete</span></a>
            </div>
        </h1>
    </div>
</div>

<form name="tier" id="shipping-tier-form" method="post" action="/jpanel/shipping/tiers/save">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($tier->id) ?>" />

    <div class="form-horizontal">

        <div class="form-group">
            <label for="min_value" class="col-sm-3 control-label">Minimum Value</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-addon">$</div>
                    <input type="text" class="form-control text-right" name="min_value" id="min_value" value="<?= e(number_format($tier->min_value,2)) ?>" maxlength="14" />
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="max_value" class="col-sm-3 control-label">Maximum Value</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-addon">$</div>
                    <input type="text" class="form-control text-right" name="max_value" id="max_value" value="<?= e(number_format($tier->max_value,2)) ?>" maxlength="14" />
                </div>
            </div>
        </div>
    </div>
</form>
