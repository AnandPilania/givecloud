
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this category?');
        if (f) {
            document.posting.action = '/jpanel/products/categories/destroy';
            document.posting.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <span class="page-header-text"><?= e($pageTitle) ?></span>

            <div class="pull-right">
                <?php if($cat->userCan('edit')): ?>
                    <div class="btn-group">
                        <a onclick="$('#product-category-form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            <li><a onclick="$('#product-category-form').submit();"><i class="fa fa-check fa-fw"></i> Save</a></li>
                            <?php if(user()->can('node.add')): ?><li><a onclick="$('#category-update-menu').val(1); $('#product-category-form').submit();"><i class="fa fa-sitemap fa-fw"></i> Save &amp; Update Menu</a></li><?php endif; ?>
                        </ul>
                    </div>
                    <a onclick="onDelete();" class="btn btn-danger <?= e((! $cat->exists) ? 'hidden' : '') ?>"><i class="fa fa-times fa-fw"></i><span class="hidden-xs hidden-sm"> Delete</span></a>
                <?php endif; ?>
            </div>

            <div class="text-secondary">
                <?php if($cat->exists): ?><a href="<?= e($cat->abs_url) ?>" target="_blank"><?= e($cat->abs_url) ?></a> <a href="#" data-toggle="modal" data-target="#modal-cat-url"><i class="fa fa-pencil-square"></i></a><?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<?php if(count($membership_ids_required) > 0): ?>
    <div class="alert alert-warning">
        <i class="fa fa-lock fa-fw"></i> The following membership levels restrict access to this category:
        <ul class="mt-1">
            <?php foreach ($membership_list as $membership): ?>
                <li><a href="<?= e(route('backend.memberships.edit', ['i' => $membership->id])) ?>"><?= e($membership->name) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form name="posting" id="product-category-form" method="post" action="/jpanel/products/categories/save" enctype="multipart/form-data" onsubmit="if ($('#category-id').val() == $('#parent_id').val() && $('#category-id').val() !== '') { alert('A category cannot belong to itself. Please choose a different parent category.'); return false; }">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" id="category-id" name="id" value="<?= e($cat->id) ?>" />
    <input type="hidden" id="category-update-menu" name="_update_menu" value="0" />

    <div class="form-horizontal">

        <div class="form-group">
            <label for="sequence" class="col-sm-3 control-label">Sequence</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" name="sequence" id="sequence" value="<?= e($cat->sequence) ?>" maxlength="2" />
            </div>
        </div>

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">Name</label>
            <div class="col-sm-7">
                <input type="text" class="form-control required" name="name" onblur="j.category.onNameChange();" id="name" value="<?= e($cat->name) ?>" maxlength="100" />
            </div>
        </div>

        <div class="form-group">
            <label for="featured_image" class="col-sm-3 control-label">Featured Image</label>
            <div class="col-sm-7">
                <input type="hidden" id="media_id" name="media_id" value="<?= e($cat->media_id) ?>">
                <div class="input-group">
                    <input type="text" class="form-control" readonly id="featured_image" value="<?= e($cat->photo->filename ?? ''); ?>">
                    <div class="input-group-btn">
                        <a href="#" class="btn btn-primary image-browser" data-input="#media_id" data-filename="#featured_image"><i class="fa fa-photo"></i> Choose</a>
                        <a href="#" class="btn btn-info clear-field" data-target="#media_id,#featured_image"><i class="fa fa-times"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">Template</label>
            <div class="col-sm-7">
                <select name="template_suffix" id="template-suffix" class="form-control">
                    <option value=""  <?php if ($cat->template_suffix == '') echo 'selected'; ?>>Default</option>

                    <?php foreach (\Ds\Models\ProductCategory::getTemplateSuffixes() as $template_suffix): ?>
                        <option value="<?= e($template_suffix) ?>"  <?php if ($cat->template_suffix == $template_suffix) echo 'selected'; ?>><?= e(ucwords(strtr($template_suffix,'-',' '))) ?></option>
                    <?php endforeach ?>
                </select>
                <small class="text-muted">Select the template you want this category to use.</small>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="col-sm-3 control-label">Parent Category</label>
            <div class="col-sm-7">
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">(top)</option>
                    <!-- level 1 -->
                    <?php $qCatLevel1 = db_query(sprintf('SELECT c.id, c.name FROM productcategory c WHERE IFNULL(c.parent_id,0) = 0 ORDER BY c.sequence')) ?>
                    <?php while($cat_level1 = db_fetch_object($qCatLevel1)): ?>
                        <option value="<?= e($cat_level1->id) ?>" <?= dangerouslyUseHTML(($cat->parent_id == $cat_level1->id)?'selected="selected"':'') ?>><?= e($cat_level1->name) ?></option>
                        <!-- level 2 -->
                        <?php $qCatLevel2 = db_query(sprintf('SELECT c.id, c.name FROM productcategory c WHERE IFNULL(c.parent_id,0) = %d ORDER BY c.sequence',$cat_level1->id)) ?>
                        <?php while($cat_level2 = db_fetch_object($qCatLevel2)): ?>
                            <option value="<?= e($cat_level2->id) ?>" <?= dangerouslyUseHTML(($cat->parent_id == $cat_level2->id)?'selected="selected"':'') ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= e($cat_level2->name) ?></option>
                            <!-- level 3 -->
                            <?php /*$qCatLevel3 = db_query(sprintf('SELECT c.id, c.name FROM productcategory c WHERE IFNULL(c.parent_id,0) = %d ORDER BY c.sequence',$cat_level2->id)) ?>
                            <?php while($cat_level3 = db_fetch_object($qCatLevel3)): ?>
                                <option value="<?= e($cat_level3->id) ?>" <?= dangerouslyUseHTML(($cat->parent_id == $cat_level3->id)?'selected="selected"':'') ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= e($cat_level3->name) ?></option>
                            <?php endwhile */?>
                        <?php endwhile ?>
                    <?php endwhile ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-7">
                <textarea id="description" name="description" class="form-control html" style="height:300px;"><?= e(stripslashes($cat->description)) ?></textarea>
            </div>
        </div>

        <?php foreach ($schemas as $template): ?>
            <?php foreach ($template->schema as $data): ?>
                <?php gc_metadata_template_suffixes($cat, $template, $data) ?>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <script>
        spaContentReady(function() {
            $('#template-suffix').bind('change', function() {
                $('.template-suffix').addClass('hide');
                $('.template-suffix.template-suffix--' + $(this).val()).removeClass('hide');
            }).change();
        });
        </script>

    </div>

    <div class="modal modal-info fade" id="modal-cat-url">
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
                            <div class="input-group-addon"><?= e(secure_site_url()) ?>/</div>
                            <input type="text" class="form-control" name="url_name" value="<?= e($cat->url_name) ?>" placeholder="category-name-goes-here">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" onclick="$('#product-category-form').submit();" class="btn btn-primary" data-dismiss="modal">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
