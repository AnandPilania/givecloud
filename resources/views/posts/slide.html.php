
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this posting?');
        if (f) {
            document.posting.action = '/jpanel/feeds/posts/destroy';
            document.posting.submit();
        }
    }
</script>

<form name="posting" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                Edit Banner

                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    <?php if($post->userCan('edit') && $post->deleted_at == ''): ?>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                        <button type="button" onclick="onDelete();" class="btn btn-danger <?= e(($isNew == 1) ? 'hidden' : '') ?>"><i class="fa fa-times"></i><span class="hidden-xs hidden-sm"> Delete</span></button>
                    <?php endif; ?>
                </div>
            </h1>
        </div>
    </div>

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <input type="hidden" id="type" value="slide" />

    <input type="hidden" name="id" value="<?= e($post->id) ?>" />
    <input type="hidden" name="type_id" value="<?= e($post->type); ?>" />

    <div class="form-horizontal">

        <div class="panel panel-default">
            <div class="panel-heading">
                General
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="col-sm-3 control-label">Image</label>
                    <div class="col-sm-9">
                        <div class="form-control-static" style="max-width:300px">
                            <input type="hidden" id="inputEnclosure" class="form-control" value="<?= e($post->enclosure->id ?? '') ?>" name="media_id" />
                            <div id="inputEnclosure-preview" class="media-picker" style="<?php if($post->enclosure): ?>background-image:url('<?= e($post->enclosure->thumbnail_url) ?>');<?php endif; ?>">
                                <div class="media-picker-btns">
                                    <a href="#" data-preview="#inputEnclosure-preview" data-input="#inputEnclosure" class="image-browser"><i class="fa fa-camera"></i></a>&nbsp;&nbsp;&nbsp;
                                    <a href="<?= e($post->enclosure->public_url ?? '') ?>" target="_blank"><i class="fa fa-external-link"></i></a>&nbsp;&nbsp;&nbsp;
                                    <a href="#" class="image-browser-clear"><i class="fa fa-trash-o"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="wrp_url">
                    <label for="url" class="col-sm-3 control-label">Link</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control ds-urls" name="url" id="url" value="<?= e($post->url) ?>" maxlength="500" />
                        <small class="text-muted">This is the link that the user will go when when they click on the image.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Headline</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" id="name" value="<?= e($post->name) ?>" maxlength="500" />
                        <small class="text-muted">Optional - Used in some advanced slider themes.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="col-sm-3 control-label">Summary</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="height:70px;" name="description" id="description"><?= e($post->description) ?></textarea>
                        <small class="text-muted">Optional - Used in some advanced slider themes.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="col-sm-3 control-label">Button Label</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="misc1" id="misc1" value="<?= e($post->misc1) ?>" maxlength="500" />
                        <small class="text-muted">Optional - Label for a button that appears on your slider.</small>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                Visibility
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label for="isenabled" class="col-sm-3 control-label">Status</label>
                    <div class="col-sm-9">
                        <input type="checkbox" class="switch" value="1" name="isenabled" <?= e(($post->isenabled) ? 'checked' : '') ?> ><br>
                        <small class="text-muted">The status determines whether or not this banner displays on your website.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="postdatetime" class="col-sm-3 control-label">Publish Date</label>
                    <div class="col-sm-3 col-lg-2">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            <input type="text" class="form-control date" name="postdatetime" id="postdatetime" value="<?php if ($post->postdatetime != null) echo toLocalFormat($post->postdatetime, 'Y-m-d'); ?>" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="expirydatetime" class="col-sm-3 control-label">End/Expiry Date</label>
                    <div class="col-sm-3 col-lg-2">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            <input type="text" class="form-control date" name="expirydatetime" id="expirydatetime" value="<?php if ($post->expirydatetime != null) echo toLocalFormat($post->expirydatetime, 'Y-m-d'); ?>" />
                        </div>
                    </div>
                </div>

                <div class="form-group" id="wrp_sequence" style="display:none;">
                    <label for="sequence" class="col-sm-3 control-label">Sequence</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" name="sequence" id="sequence" value="<?= e($post->sequence); ?>" maxlength="2" />
                    </div>
                </div>

            </div>
        </div>

    </div>
</form>
