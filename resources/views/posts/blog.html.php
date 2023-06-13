
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this posting?');
        if (f) {
            document.posting.action = '/jpanel/feeds/posts/destroy';
            document.posting.submit();
        }
    }
</script>

<form name="posting" id="post-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                <div class="text-ellipsis" style="display:inline-block; vertical-align:middle; max-width:50vw;"><?= e(($post->exists) ? $post->name : 'New Post') ?></div>

                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    <a href="<?= e(route('backend.post.index', ['i' => $post->postType->id])) ?>" class="btn btn-default">Back to Feed</a>
                    <?php if($post->userCan('edit') && $post->deleted_at == ''): ?>
                        <?php if($post->isenabled): ?>
                            <button type="submit" name="isenabled" value="0" class="btn btn-outline btn-warning"><i class="fa fa-reply"></i><span class="hidden-xs hidden-sm"> Unpublish</span></button>
                            <button type="submit" name="isenabled" value="1" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                        <?php else: ?>
                            <button type="submit" name="isenabled" value="0" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                        <?php endif; ?>

                        <div class="btn-group" role="group" aria-label="">
                            <a href="/jpanel/feeds/posts/<?= e($post->id) ?>/duplicate" class="btn btn-default"><i class="fa fa-copy"></i></a>
                            <?php if($post->exists()): ?><a class="btn btn-default" onclick="onDelete();"><i class="fa fa-trash text-danger"></i></a><?php endif; ?>
                            <!--<a href="#advanced-modal" class="btn btn-default" data-toggle="modal"><i class="fa fa-gear"></i></a>-->
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-secondary">
                    <?php if($post->exists): ?><a href="<?= e($post->absolute_url) ?>" target="_blank"><?= e($post->absolute_url) ?></a> <a href="#" data-toggle="modal" data-target="#modal-post-url"><i class="fa fa-pencil-square"></i></a><?php endif; ?>
                </div>
            </h1>
        </div>
    </div>

    <div class="toastify hide">
        <?= dangerouslyUseHTML(app('flash')->output()) ?>
    </div>

    <input type="hidden" name="id" value="<?= e($post->id) ?>" />
    <input type="hidden" name="type_id" value="<?= e($post->type); ?>" />

    <div class="">

        <div class="row">

            <div class="col-lg-8 col-md-7 col-sm-12">

                <div class="bottom-gutter <?= e($content_editor_classes) ?>">
                    <textarea class="givecloudeditor form-control" name="body" data-primary="true"><?= e($post->body); ?></textarea>
                </div>

                <?php foreach ($schemas as $template): ?>
                    <?php foreach ($template->schema as $data): ?>
                        <?php gc_metadata_template_suffixes($post, $template, $data) ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <script>
                spaContentReady(function() {
                    $('.template-suffix').addClass('hide').attr('disabled', true);
                    $('.template-suffix.template-suffix--<?= e($post->postType->default_template_suffix ?? '') ?>').removeClass('hide').removeAttr('disabled');
                });
                </script>

            </div>

            <div class="col-lg-4 col-md-5 col-sm-12">

                <?php if(!$post->isenabled): ?>
                    <div class="alert alert-warning">
                        <button type="submit" name="isenabled" value="1" class="pull-right btn btn-xs btn-warning">Publish Now</button>
                        This is a draft.
                    </div>
                <?php endif; ?>

                <div class="panel panel-basic">
                    <div class="panel-heading">
                        Settings
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label for="name" class="control-label">Title</label>
                            <input type="text" class="form-control" required name="name" id="name" value="<?= e($post->name) ?>" maxlength="500" />
                        </div>

                        <div class="form-group">
                            <label for="description" class="control-label">Summary</label>
                            <textarea class="form-control" style="height:80px;" name="description" id="description"><?= e($post->description) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="inputCategoryIds">Categories</label>
                            <select class="form-control selectize-primary selectize" size="1" multiple name="categories[]" id="inputCategories">
                                <?php $category_ids = $post->categories->pluck('id')->all(); ?>
                                <?php foreach($post->postType->categories()->orderBy('sequence')->get() as $category): ?>
                                    <option value="<?= e($category->id) ?>" <?= e((in_array($category->id, $category_ids)) ? 'selected' : '') ?> ><?= e($category->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <input type="text" class="form-control selectize-info selectize-tags" name="tags" id="tags" value="<?= e($post->tags) ?>" maxlength="500" />
                        </div>

                        <hr style="margin-left:-20px; margin-right:-20px;">

                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label for="tags">Featured Image</label>
                                <div>
                                    <input type="hidden" id="inputFeaturedImage" class="form-control" value="<?= e($post->featuredImage->id) ?>" name="featured_image_id" id="inputFeaturedImage" />
                                    <div id="inputFeaturedImage-preview" class="media-picker" style="<?php if($post->featuredImage): ?>background-image:url('<?= e($post->featuredImage->thumbnail_url) ?>');<?php endif; ?>">
                                        <div class="media-picker-btns">
                                            <a href="#" data-preview="#inputFeaturedImage-preview" data-input="#inputFeaturedImage" class="image-browser"><i class="fa fa-camera"></i></a>&nbsp;&nbsp;&nbsp;
                                            <a href="<?= e($post->featuredImage->public_url) ?>" target="_blank"><i class="fa fa-external-link"></i></a>&nbsp;&nbsp;&nbsp;
                                            <a href="#" class="image-browser-clear"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-xs-6">
                                <label for="tags">Alternate Image</label>
                                <div>
                                    <input type="hidden" id="inputAltImage" class="form-control" value="<?= e($post->altImage->id) ?>" name="alt_image_id" id="inputAltImage" />
                                    <div id="inputAltImage-preview" class="media-picker" style="<?php if($post->altImage): ?>background-image:url('<?= e($post->altImage->thumbnail_url) ?>');<?php endif; ?>">
                                        <div class="media-picker-btns">
                                            <a href="#" data-preview="#inputAltImage-preview" data-input="#inputAltImage" class="image-browser"><i class="fa fa-camera"></i></a>&nbsp;&nbsp;&nbsp;
                                            <a href="<?= e($post->altImage->public_url) ?>" target="_blank"><i class="fa fa-external-link"></i></a>&nbsp;&nbsp;&nbsp;
                                            <a href="#" class="image-browser-clear"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr style="margin-left:-20px; margin-right:-20px; margin-top:10px;">

                        <div class="row">
                            <div class="form-group col-xs-12">
                                <!--<a href="#advanced-modal" class="pull-right" data-toggle="modal"><i class="fa fa-gear"></i></a>-->
                                <label for="inputAuthor" class="control-label">Author</label>
                                <input type="text" class="form-control" name="author" id="inputAuthor" value="<?= e($post->author); ?>" maxlength="500" />
                            </div>

                            <div class="form-group col-xs-6">
                                <label for="postdatetime" class="control-label">Publish Date</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control datePretty" name="postdatetime" id="postdatetime" value="<?php if ($post->postdatetime != null) echo toLocalFormat($post->postdatetime, 'M j, Y'); ?>" />
                                </div>
                            </div>

                            <div class="form-group col-xs-6">
                                <label for="expirydatetime" class="control-label">Unpublish Date</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control datePretty" name="expirydatetime" id="expirydatetime" value="<?php if ($post->expirydatetime != null) echo toLocalFormat($post->expirydatetime, 'M j, Y'); ?>" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

        <div class="panel panel-default hide">
            <div class="panel-heading">
                Advanced
            </div>
            <div class="panel-body">

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

                <div class="form-group" id="wrp_misc1" style="display:none;">
                    <label for="misc1" class="col-sm-3 control-label"><?php if ($post->postType->sysname === 'event') echo 'Speaker(s):'; else echo 'misc1'; ?></label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" name="misc1" id="misc1" value="<?= e($post->misc1); ?>" maxlength="500" />
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
        </div>

    </div>

    <div class="modal modal-info fade" id="modal-post-url">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-link"></i> Change Public URL</h4>
                </div>
                <div class="modal-body">

                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> Changing this public URL will break all existing links to this article.
                    </div>

                    <div class="form-group">
                        <label class="control-label">Public Url</label>
                        <div class="input-group">
                            <div class="input-group-addon"><?= e($post->postType->absolute_url) ?>/</div>
                            <input type="text" class="form-control" name="url_slug" value="<?= e($post->url_slug) ?>" placeholder="article-title-goes-here">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>
