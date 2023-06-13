
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this feed?');
        if (f) {
            document.posting.action = '/jpanel/feeds/destroy';
            document.posting.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <span class="page-header-text"><?= e($pageTitle) ?></span>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if($feed->deleted_at == ''): ?>
                    <?php if($feed->url_slug): ?>
                        <a href="<?= e(route('backend.post.index', ['i' => $feed->id])) ?>" class="btn btn-info"> Posts</a>
                    <?php endif; ?>
                    <a onclick="$('#feed-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e((! $feed->exists) ? 'hidden' : '') ?>"><i class="fa fa-times fa-fw"></i><span class="hidden-xs hidden-sm"> Delete</span></a>
                <?php endif; ?>
            </div>

            <?php if ($feed->deleted_at == ''): ?>
                <div class="text-secondary">
                    <a href="https://<?= e(sys_get('clientDomain')) ?>/<?= e($feed->url_slug) ?>" target="_blank">https://<?= e(sys_get('clientDomain')) ?>/<?= e($feed->url_slug) ?> <i class="fa fa-external-link"></i></a>
                </div>
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<form name="posting" method="post" id="feed-form" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($feed->id) ?>" />

    <div class="panel panel-default">
        <div class="panel-heading">
            General
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Name</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="name" id="name" value="<?= e($feed->name) ?>" maxlength="100" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="sysname" class="col-sm-3 control-label">Type</label>
                    <div class="col-sm-9">
                        <div class="clearfix" style="margin-left:-30px;">

                            <div class="checkbox">
                                <label>
                                    <input type="radio" name="sysname" <?= e(($feed->sysname == 'blog') ? 'checked' : '') ?> value="blog">&nbsp;&nbsp;<i class="fa fa-rss"></i>&nbsp;&nbsp;<strong>Blog</strong>
                                    &nbsp;&nbsp;<small class="text-muted">Publish articles to a blog or news feed.</small>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="radio" name="sysname" <?= e(($feed->sysname == 'slide') ? 'checked' : '') ?> value="slide">&nbsp;&nbsp;<i class="fa fa-image"></i>&nbsp;&nbsp;<strong>Carousel Images</strong>
                                    &nbsp;&nbsp;<small class="text-muted">An image carousel that scrolls images.</small>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="radio" name="sysname" <?= e(($feed->sysname == 'snippet') ? 'checked' : '') ?> value="snippet">&nbsp;&nbsp;<i class="fa fa-list"></i>&nbsp;&nbsp;<strong>Snippets</strong>
                                    &nbsp;&nbsp;<small class="text-muted">Content snippets published via shortcodes.</small>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="template_suffix" class="col-sm-3 control-label">Feed Template</label>
                    <div class="col-sm-7">
                        <select name="template_suffix" id="template-suffix" class="form-control" style="width:180px;">
                            <option value=""  <?php if ($feed->template_suffix == '') echo 'selected'; ?>>Default</option>

                            <?php foreach (\Ds\Models\PostType::getTemplateSuffixes() as $template_suffix): ?>
                                <option value="<?= e($template_suffix) ?>"  <?php if ($feed->template_suffix == $template_suffix) echo 'selected'; ?>><?= e(ucwords(strtr($template_suffix,'-',' '))) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="text-muted">Select the template you want this product to use.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="default_template_suffix" class="col-sm-3 control-label">Post Template</label>
                    <div class="col-sm-7">
                        <select name="default_template_suffix" id="template-suffix" class="form-control" style="width:180px;">
                            <option value=""  <?php if ($feed->default_template_suffix == '') echo 'selected'; ?>>Default</option>

                            <?php foreach (\Ds\Models\Post::getTemplateSuffixes() as $template_suffix): ?>
                                <option value="<?= e($template_suffix) ?>"  <?php if ($feed->default_template_suffix == $template_suffix) echo 'selected'; ?>><?= e(ucwords(strtr($template_suffix,'-',' '))) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="text-muted">Select the template you want this product to use.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="url_slug" class="col-sm-3 control-label">Web Address<br><small class="text-muted">This is the URL that people will enter to view your blog. All your individual post URL's will start with this URL.</small></label>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <div class="input-group-addon"><?= e(sys_get('clientDomain')) ?>/</div>
                            <input type="text" class="form-control" name="url_slug" id="url_slug" value="<?= e($feed->url_slug) ?>" maxlength="150" placeholder="my-feed" />
                        </div>
                        <small class="text-muted">For example:<br><?= e(sys_get('clientDomain')) ?>/my-feed<br><?= e(sys_get('clientDomain')) ?>/my-feed/a-post-that-i-just-published</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="featured_image" class="col-sm-3 control-label">Featured Image</label>
                    <div class="col-sm-7">
                        <input type="hidden" id="media_id" name="media_id" value="<?= e($feed->media_id) ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" readonly id="featured_image" value="<?= e($feed->photo->filename ?? ''); ?>">
                            <div class="input-group-btn">
                                <a href="#" class="btn btn-primary image-browser" data-input="#media_id" data-filename="#featured_image"><i class="fa fa-photo"></i> Choose</a>
                                <a href="#" class="btn btn-info clear-field" data-target="#media_id,#featured_image"><i class="fa fa-times"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputCategories" class="col-sm-3 control-label">Categories</label>
                    <div class="col-sm-7">
                        <select class="form-control selectize-info auto-height" multiple name="categories[]" id="inputCategories">
                            <?php foreach($feed->categories()->orderBy('sequence')->get() as $category): ?>
                                <option value="category_id:<?= e($category->id) ?>" <?= e(count($category->childCategories) ? 'data-has-children' : null) ?> selected><?= e($category->fullName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            To create sub categories, use <b>></b> <em>(greater than sign)</em> to separate them.<br>
                            For example: category<b>></b>sub category<b>></b>sub sub category
                        </small>
                    </div>
                </div>
                <div class="form-group">
                    <label for="show_social_share_links" class="col-sm-3 control-label">Show social links after posts</label>
                    <div class="col-sm-7">
                        <input id="show_social_share_links" type="checkbox" class="switch" value="1" name="show_social_share_links" <?= e(($feed->show_social_share_links == 1) ? 'checked' : '') ?>>
                        <br><small class="text-muted">If enabled, the social share buttons will be visible after each blog post.</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-rss-square fa-fw"></i> RSS Settings
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group hide">
                    <label for="name" class="col-sm-3 control-label">Feed Url</label>
                    <div class="col-sm-9">
                        <div class="form-control-static">
                            <a href="<?= e(secure_site_url('feed.php?i=' . e(request('i')))) ?>" target="_blank"><?= e(secure_site_url('feed.php?i=' . e(request('i')))) ?></a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Description</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="height:70px;" name="rss_description"><?= e($feed->rss_description); ?></textarea>
                    </div>
                </div>

                <div class="form-group hide">
                    <label for="rss_link" class="col-sm-3 control-label">Link</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control ds-urls" value="<?= e($feed->rss_link) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="rss_copyright" class="col-sm-3 control-label">Copyright</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <div class="input-group-addon">&copy;</div>
                            <input type="text" class="form-control" style="width:400px;" name="rss_copyright" id="rss_copyright" value="<?= e($feed->rss_copyright); ?>" />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-apple"></i> iTunes Settings
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Is a Podcast</label>
                    <div class="col-sm-2">
                        <select name="isitunes" id="isitunes" class="form-control" onchange="j.posttype.onPodcastChange();">
                            <option value="0" <?php if ($feed->isitunes == 0) echo 'selected="selected"'; ?>>No</option>
                            <option value="1" <?php if ($feed->isitunes == 1) echo 'selected="selected"'; ?>>Yes</option>
                        </select>
                    </div>
                </div>

                <div id="podcast_wrp" class="hidden">

                    <div class="form-group">
                        <label for="imagepath" class="col-sm-3 control-label">Thumbnail</label>
                        <div class="col-sm-9">
                            <div class="form-control-static">
                                <?php if($feed->photo): ?><img src="<?= e(media_thumbnail($feed)) ?>" width="80" class="pull-left" style="margin-right:10px;" /><?php endif; ?>
                                <table>
                                    <tr>
                                        <td>Currently: </td>
                                        <td><?php if ($feed->photo) echo '<a href="'.$feed->photo->public_url.'" target="_blank">'.$feed->photo->filename.'</a>'; else echo 'No file uploaded'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Upload: </td>
                                        <td><input type="file" name="imagepath" style="width:200px;" /></td>
                                    </tr>
                                </table>
                                <small>Note: Only JPEG, GIF and PNG upload is supported.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="itunes_subtitle" class="col-sm-3 control-label">Subtitle</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="itunes_subtitle" id="itunes_subtitle" value="<?= e($feed->itunes_subtitle) ?>" />
                            <small>&lt;itunes:subtitle&gt;</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="itunes_author" class="col-sm-3 control-label">Author</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="itunes_author" id="itunes_author" value="<?= e($feed->itunes_author) ?>" />
                            <small>&lt;itunes:author&gt;</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="itunes_owner_name" class="col-sm-3 control-label">Owner Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="itunes_owner_name" id="itunes_owner_name" value="<?= e($feed->itunes_owner_name) ?>" />
                            <small>&lt;itunes:owner&gt;</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="itunes_owner_email" class="col-sm-3 control-label">Owner Email</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="itunes_owner_email" id="itunes_owner_email" value="<?= e($feed->itunes_owner_email) ?>" />
                            <small>&lt;itunes:owner&gt;</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="itunes_category" class="col-sm-3 control-label">Category</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="itunes_category" id="itunes_category" value="<?= e($feed->itunes_category) ?>" />
                            <small>&lt;itunes:category&gt;</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($schemas as $template): ?>
        <?php foreach ($template->schema as $data): ?>
            <?php gc_metadata_template_suffixes($feed, $template, $data) ?>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <script>
    spaContentReady(function() {
        $('#template-suffix').bind('change', function() {
            $('.template-suffix').addClass('hide').attr('disabled', true);
            $('.template-suffix.template-suffix--' + $(this).val()).removeClass('hide').removeAttr('disabled');
        }).change();

        $('#inputCategories').selectize({
            plugins: ['drag_drop', 'remove_button'],
            delimiter: ',',
            persist: false,
            create: function (input) {
                return { value: input, text: input }
            },
            onDelete: function (values) {
                var itemsToDelete = Array.from(
                    this.revertSettings.$children.filter(function (i, option) {
                        return values.indexOf(option.value) > -1
                            && option
                            && typeof option.dataset.hasChildren !== 'undefined';
                    })
                );

                for (i = 0; i < itemsToDelete.length; i++) {
                    if (confirm('Are you sure you want to delete category "' + itemsToDelete[i].text + '"? Note: this category has sub-categories that will also be deleted.') === false) {
                        return false;
                    }
                }

                return true;
            },
        });
    });
    </script>

</form>
