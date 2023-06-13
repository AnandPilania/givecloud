
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this posting?');
        if (f) {
            document.posting.action = '/jpanel/feeds/posts/destroy';
            document.posting.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?= e($pageTitle) ?>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if($post->userCan('edit') && $post->deleted_at == ''): ?>
                    <a onclick="$('#post-form').submit();" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e(($isNew == 1) ? 'hidden' : '') ?>"><i class="fa fa-times"></i><span class="hidden-xs hidden-sm"> Delete</span></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<form name="posting" method="post" id="post-form" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($post->id) ?>" />
    <input type="hidden" name="type_id" value="<?= e($post->type); ?>" />


    <div class="form-horizontal">

        <div class="form-group">
            <label for="isenabled" class="col-sm-3 control-label">Status</label>
            <div class="col-sm-3">
                <select id="isenabled" name="isenabled" class="form-control">
                    <option value="1" <?php if ($post->isenabled == 1) echo 'selected'; ?>>Online</option>
                    <option value="0" <?php if ($post->isenabled == 0) echo 'selected'; ?>>Offline</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="sequence" class="col-sm-3 control-label">Feed</label>
            <div class="col-sm-5">
                <select id="type" class="form-control" name="type" onchange="j.post.onTypeChange();" <?php if (!$isNew) echo 'disabled="disabled"'; ?>>
                    <option value="">Choose One</option>
                    <?php
                        $query = 'SELECT * FROM posttype ORDER BY name';
                        $qryCourse = db_query($query);
                        while ($m = db_fetch_assoc($qryCourse)) {
                            if ($m['id'] == request('p'))
                                echo '<option value="'.$m['sysname'].'" selected>'.stripslashes($m['name']).'</option>';
                            else
                                echo '<option value="'.$m['sysname'].'">'.stripslashes($m['name']).'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="postdatetime" class="col-sm-3 control-label">Date</label>
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

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">Headline</label>
            <div class="col-sm-7">
                <input type="text" class="form-control" name="name" id="name" value="<?= e($post->name) ?>" maxlength="500" />
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="col-sm-3 control-label">Summary</label>
            <div class="col-sm-7">
                <textarea class="form-control" style="height:70px;" name="description" id="description"><?= e($post->description) ?></textarea>
            </div>
        </div>

        <div class="form-group" id="wrp_author" style="display:none;">
            <label for="author" class="col-sm-3 control-label">Author</label>
            <div class="col-sm-5">
                <input type="text" class="form-control" name="author" id="author" value="<?= e($post->author); ?>" maxlength="500" />
            </div>
        </div>

        <div class="form-group" id="wrp_location" style="display:none;">
            <label for="location" class="col-sm-3 control-label">Location</label>
            <div class="col-sm-5">
                <input type="text" class="form-control" name="location" id="location" value="<?= e($post->location); ?>" maxlength="500" />
            </div>
        </div>

        <div class="form-group" id="wrp_embedcode" style="display:none;">
            <label for="location" class="col-sm-3 control-label">Embed Code</label>
            <div class="col-sm-5">
                <textarea class="form-control" style="height:200px;" name="embedcode" id="embedcode"><?= e($post->embedcode); ?></textarea>
            </div>
        </div>

        <div class="form-group" id="wrp_sequence" style="display:none;">
            <label for="sequence" class="col-sm-3 control-label">Sequence</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="sequence" id="sequence" value="<?= e($post->sequence); ?>" maxlength="2" />
            </div>
        </div>

        <div class="form-group" id="wrp_filepath" style="display:none;">
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

        <div class="form-group" id="wrp_url" style="display:none;">
            <label for="url" class="col-sm-3 control-label">Link</label>
            <div class="col-sm-7">
                <input type="text" class="form-control ds-urls" name="url" id="url" value="<?= e($post->url); ?>" maxlength="500" />
                <small>This is the link that the user will go when when they click on the image.</small>
            </div>
        </div>

        <div class="form-group" id="wrp_fineprint" style="display:none;">
            <label for="url" class="col-sm-3 control-label">Conditions</label>
            <div class="col-sm-7">
                <textarea class="text" style="width:400px; height:50px;" name="fineprint" id="fineprint"><?= e($post->fineprint); ?></textarea>
                <small>Example: Not valid with any other offer.</small>
            </div>
        </div>

        <div class="form-group" id="wrp_fineprint" style="display:none;">
            <label for="url" class="col-sm-3 control-label">Conditions</label>
            <div class="col-sm-9">
                <textarea class="text" style="width:400px; height:50px;" name="fineprint" id="fineprint"><?= e($post->fineprint); ?></textarea>
                <small>Example: Not valid with any other offer.</small>
            </div>
        </div>

        <div class="form-group" id="wrp_body" style="display:none;">
            <label for="url" class="col-sm-3 control-label">Body</label>
            <div class="col-sm-9">
                <textarea class="html form-control" style="height:300px;" name="body"><?= e($post->body); ?></textarea>
            </div>
        </div>

        <div class="form-group" id="wrp_misc1" style="display:none;">
            <label for="misc1" class="col-sm-3 control-label"><?php if ($t['sysname'] == 'event') echo 'Speaker(s):'; else echo 'misc1'; ?></label>
            <div class="col-sm-7">
                <input type="text" class="form-control" name="misc1" id="misc1" value="<?= e($post->misc1); ?>" maxlength="500" />
            </div>
        </div>

        <div class="form-group">
            <label for="tags" class="col-sm-3 control-label">Tags</label>
            <div class="col-sm-7">
                <input type="text" class="form-control" name="tags" id="tags" value="<?= e($post->tags); ?>" maxlength="500" />
                <small>Example: 'Conference', or 'Conference, Retreat, Speaking'</small>
            </div>
        </div>

        <div class="form-group" id="wrp_length_milliseconds" style="display:none;">
            <label for="length_milliseconds" class="col-sm-3 control-label">Milliseconds</label>
            <div class="col-sm-7">
                <input type="text" class="form-control" name="length_milliseconds" id="length_milliseconds" value="<?= e($post->length_milliseconds); ?>" maxlength="500" />
            </div>
        </div>

        <div class="form-group" id="wrp_length_formatted" style="display:none;">
            <label for="length_formatted" class="col-sm-3 control-label">Length Formatted</label>
            <div class="col-sm-7">
                <input type="text" class="form-control" name="length_formatted" id="length_formatted" value="<?= e($post->length_formatted); ?>" maxlength="500" />
                <small>Example: hh:ss (03:48 is 3hrs 48min)</small>
            </div>
        </div>

    </div>
</form>
