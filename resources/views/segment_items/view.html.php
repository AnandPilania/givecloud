
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this option?');
        if (f) {
            document.segment_item.action = '/jpanel/sponsorship/segments/items/destroy';
            document.segment_item.submit();
        }
    }
    function onRestore () {
        var f = confirm('Are you sure you want to restore (un-delete) this option?');
        if (f) {
            document.segment_item.action = '/jpanel/sponsorship/segments/items/restore';
            document.segment_item.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <?php if (!$item->trashed()): ?>
                    <a onclick="$('#segment_item-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e((!$item->exists) ? 'hidden' : '') ?>"><i class="fa fa-trash fa-fw"></i></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?php if($item->trashed()): ?>
    <div class="alert alert-danger">
        <i class="fa fa-trash"></i> This custom field has been deleted. <a onclick="onRestore();" class="btn btn-success btn-xs"><i class="fa fa-refresh fa-fw"></i><span class="hidden-xs hidden-sm"> Restore</span></a>
    </div>
<?php endif; ?>

<form name="segment_item" id="segment_item-form" method="post" action="/jpanel/sponsorship/segments/items/save" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($item->id) ?>" />
    <input type="hidden" name="segment_id" value="<?= e($item->segment->id) ?>" />

    <div class="row">

        <div class="col-sm-8">

            <div class="panel panel-default">
                <div class="panel-heading">
                    General
                </div>
                <div class="panel-body">

                    <div class="form-horizontal">

                        <div class="form-group">
                            <label for="name" class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="name" id="name" value="<?= e($item->name) ?>" maxlength="150" />
                            </div>
                        </div>

                        <div class="<?= e(($item->segment->type === 'text') ? 'hidden' : '') ?>">

                            <div class="form-group">
                                <label for="summary" class="col-sm-3 control-label">Summary</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" style="height:70px;" name="summary" id="summary"><?= e($item->summary) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="link" class="col-sm-3 control-label">Link</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control ds-urls" name="link" id="link" value="<?= e($item->link) ?>" />
                                </div>
                                <div class="col-sm-4">
                                    <select class="form-control" name="target" id="target">
                                        <option value="">Open in Same Window/Tab</option>
                                        <option value="_blank" <?= dangerouslyUseHTML(($item->target == '_blank') ? 'checked="checked"' : '') ?> >Open in New Window/Tab</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="col-sm-4">
            <?php if($item->segment->is_geographic == 1): ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Location
                        <div class="pull-right">
                            <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$(this).toggle(); $('#google_search_form').toggle(); $('#location_search').focus(); return false;"><i class="fa fa-search"></i> Search a Location</a>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:0px;">

                        <fieldset class="gllpLatlonPicker" id="custom_id">

                            <div class="form-group" id="google_search_form" style="padding:20px; display:none;">
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                    <input type="text" id="location_search" class="form-control gllpSearchField">
                                    <div class="input-group-addon btn gllpSearchButton">Find</div>
                                </div>
                            </div>

                            <div class="gllpMap">Google Maps</div>
                            <input type="hidden" class="gllpLatitude" name="latitude" value="<?= e(($item === false) ? 38 : $item->latitude) ?>"/>
                            <input type="hidden" class="gllpLongitude" name="longitude" value="<?= e(($item === false) ? -100 : $item->longitude) ?>"/>
                            <input type="hidden" class="gllpZoom" value="2"/>
                            <div style="font-size:10px; color:#999;">Lat: <span class="gllpLatitude_html"><?= e((!$item->exists) ? 38 : $item->latitude) ?></span>; Long: <span class="gllpLongitude_html"><?= e((!$item->exists) ? -100 : $item->longitude) ?></span>;<br /></div>
                        </fieldset>

                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</form>

<?php if ($item->exists): ?>
<hr />
<small>
    Created by <?= e($item->createdBy->full_name) ?> on <?= e($item->created_at) ?> EST.<br />
    <?php if ($item->updatedBy): ?>Last modified by <?= e($item->updatedBy->full_name) ?> on <?= e($item->updated_at) ?> EST.<?php endif; ?>
</small>
<?php endif; ?>
