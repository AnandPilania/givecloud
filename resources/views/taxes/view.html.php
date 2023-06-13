
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this tax?');
        if (f) {
            document.tax.action = '/jpanel/taxes/destroy';
            document.tax.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e(\Illuminate\Support\Str::limit($pageTitle, 22)) ?>

            <div class="pull-right">
                <?php if(!$tax->deleted_at): ?>
                    <a onclick="$('#tax-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-sm hidden-xs"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e((!$tax->exists) ? 'hidden' : '') ?>"><i class="fa fa-times fa-fw"></i><span class="hidden-sm hidden-xs"> Delete</span></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?php if($tax->deleted_at): ?>
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle fa-fw"></i> This record was deleted on <?= e(toLocalFormat($tax->deleted_at, 'fdatetime')) ?> by <?= e($tax->deletedBy->name) ?>.
    </div>
<?php endif; ?>

<form name="tax" id="tax-form" method="post" action="/jpanel/taxes/save">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($tax->id) ?>" />

    <div class="form-horizontal">

        <div class="form-group">
            <label for="code" class="col-sm-3 control-label">Name</label>
            <div class="col-sm-5">
                <input type="text" class="form-control" name="code" id="code" value="<?= e($tax->code) ?>" maxlength="15" />
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-7">
                <textarea class="form-control" style="height:70px;" name="description" id="description" ><?= e($tax->description); ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="code" class="col-sm-3 control-label">Rate</label>
            <div class="col-sm-3 col-lg-2">
                <div class="input-group">
                    <input type="text" class="form-control text-right" name="rate" id="rate" value="<?= e($tax->rate) ?>" maxlength="5" />
                    <div class="input-group-addon">%</div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="regionids" class="col-sm-3 control-label">
                State/Province(s)

                <p><a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('#regionids option').attr('selected','selected');">All</a>
                <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('#regionids option').removeAttr('selected');">None</a></p>
                <p><a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('#regionids option.-US').attr('selected','selected'); $('#regionids option.-CA').removeAttr('selected');">US Only</a>
                <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$('#regionids option.-CA').attr('selected','selected'); $('#regionids option.-US').removeAttr('selected');">CA Only</a></p>
            </label>
            <div class="col-sm-5">
                <select id="regionids" name="regionids[]" class="form-control" multiple="multiple" size="7">
                    <?php
                        $qG = db_query(sprintf("SELECT r.*,
                                    (CASE WHEN t.id IS NOT NULL THEN 1 ELSE 0 END) AS isselected
                                FROM region r
                                LEFT JOIN producttaxregion t ON t.regionid = r.id AND t.taxid = %d
                                ORDER BY r.country DESC, r.name ASC",
                            db_real_escape_string($tax->id)
                        ));
                        while ($g = db_fetch_assoc($qG)) {
                            echo '<option class="-'.$g['country'].'" value="'.$g['id'].'" '.(($g['isselected'] == 1)?'selected="selected"':'').'>'.$g['name'].'</option>';
                        }
                    ?>
                </select>
                <small>Hold CTRL (Command on Mac) key to select multiple options.</small>
            </div>
        </div>

        <div class="form-group">
            <label for="city" class="col-sm-3 control-label">City(s)</label>
            <div class="col-sm-5">
                <textarea name="city" class="form-control" style="height:100px;"><?= e($tax->city) ?></textarea>
                <small>Separate each city name using a carriage return.<br />This list is NOT case sensitive.</small>
            </div>
        </div>

    </div>
</form>
