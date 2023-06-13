
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <span class="page-header-text block w-0 h-0 overflow-hidden"><?= e($pageTitle) ?></span>

            <?= e(\Illuminate\Support\Str::limit($pageTitle,22)) ?>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if(user()->can('node.add')): ?>
                    <a onclick="$.confirm('Are you sure you want to create a duplicate of this page?', function(){ location='/jpanel/pages/copy?id=<?= e($node->id) ?>'; }, 'warning');" class="btn btn-info btn-outline" data-toggle="tooltip" data-placement="top" title="Duplicate This Page"><i class="fa fa-copy"></i></a>
                <?php endif; ?>
                <?php if ($node->userCan('edit')): ?>
                    <a onclick="$('#page-form').submit();" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-sm hidden-xs"> Save</span></a>
                    <?php if($node->code !== 'home'): ?>
                        <a onclick="j.page.onDelete();" class="btn btn-danger <?= e(($isNew == 1) ? 'hidden' : '') ?>"><i class="fa fa-trash"></i><span class="hidden-sm hidden-xs hidden-md"> Delete</span></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if($node->exists && !in_array($node->type,['category','menu']) && $node->code !== 'home'): ?>
                <div class="text-secondary">
                    <a href="<?= e($node->abs_url) ?>" target="_blank"><?= e(secure_site_url($node->abs_url)) ?></a> <a href="#" data-toggle="modal" data-target="#modal-node-url"><i class="fa fa-pencil-square"></i></a>
                </div>
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="toastify hide">
    <?= dangerouslyUseHTML(app('flash')->output()) ?>
</div>

<?php if ($node->hasMoreRecentAutosave()): ?>
    <div class="alert alert-warning">
        There is an autosave of this page that is more recent than the version below.
        <a href="<?= e(route('backend.page.edit', ['i' => $node, 'revision' => $node->autosaveRevision])) ?>">Use the autosave</a>
    </div>
<?php endif ?>

<?php if(count($membership_ids_required) > 0): ?>
    <div class="alert alert-warning">
        <i class="fa fa-lock"></i> The following membership levels restrict access to this page:
        <ul class="mt-1">
            <?php foreach ($membership_list as $membership): ?>
                <li><a href="<?= e(route('backend.memberships.edit', ['i' => $membership->id])) ?>"><?= e($membership->name) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form name="posting" method="post" id="page-form" action="<?= e($action) ?>" enctype="multipart/form-data" onsubmit="return j.page.validate();" data-autosave="<?= e($node->supportsRevisions()) ?>">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" id="postid" name="id" value="<?= e($node->id) ?>" />

    <!-- page content -->
    <?php if (!$isNew && in_array($node->type, ['html', 'advanced', 'liquid'])): ?>
        <div class="bdy_wrp bottom-gutter <?= e($content_editor_classes) ?> template-suffix--none">

            <?php if ($node->type == 'advanced'): ?>
                <input type="hidden" name="body" value="<?= e($node->body) ?>" />
                <div id="code-body" data-input="#page-form input[name=body]" class="code-editor" style="width:100%;height:600px;"></div>
            <?php elseif ($node->type == 'liquid'): ?>
                <input type="hidden" name="body" value="<?= e($node->body) ?>" />
                <div id="code-body" data-mode="liquid" data-input="#page-form input[name=body]" class="code-editor" style="width:100%;height:600px;"></div>
            <?php else: ?>
                <textarea class="givecloudeditor form-control" name="body" data-primary="true"><?= e($node->body); ?></textarea>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            General
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group <?= e(($node->code == 'home' && $node->isactive) ? 'hidden' : '') ?>">
                    <label for="title" class="col-sm-3 control-label">Page Name</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" value="<?= e($node->title); ?>" name="title" id="title" />
                        <small class="<?= e(($node->protected == 1)?'hidden':'') ?>">The title of the page as it will be displayed on the page and in the menus.<?php if ($node->type == 'html') echo '<br />You can specify a different browser page title under \'More options...\'.'; ?></small>
                    </div>
                </div>

                <div class="form-group hidden" id="category_id_wrap">
                    <label for="title" class="col-sm-3 control-label">Product Category</label>
                    <div class="col-sm-9">
                        <select name="category_id" id="type" class="form-control">
                            <option value="">Choose a Category</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= e($cat->id) ?>" <?php if ($node->category_id == $cat->id) echo 'selected'; ?>><?= e($cat->name) ?></option>
                                <?php foreach($cat->categories as $subcat): ?>
                                    <option value="<?= e($subcat->id) ?>" <?php if ($node->category_id == $subcat->id) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;<?= e($subcat->name) ?></option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group <?= e(($node->code == 'home' && $node->isactive) ? 'hidden' : '') ?>">
                    <label for="isactive" class="col-sm-3 control-label">Status</label>
                    <div class="col-sm-3 col-lg-2">
                        <select name="isactive" id="isactive" class="form-control">
                            <option value="1" <?php if ($node->isactive == 1) echo 'selected'; ?>>Online</option>
                            <option value="0" <?php if ($node->isactive == 0) echo 'selected'; ?> style="font-weight:bold; color:#c00;">Offline</option>
                        </select>
                    </div>
                </div>

                <!-- "Donor Portal" pages cannot change their template since they inherit the accounts/custom-page template by default -->
                <div class="form-group <?= e((! in_array($node->type, ['html', 'advanced', 'liquid']) || $node->isChildOfDonorPortalMenu()) ? 'hidden' : '') ?>">
                    <label for="template-suffix" class="col-sm-3 control-label">Page Layout</label>
                    <div class="col-sm-7">
                        <select name="template_suffix" id="template-suffix" class="form-control">
                            <option value=""  <?php if ($node->template_suffix == '') echo 'selected'; ?>>Default</option>

                            <?php if (in_array($node->type, ['advanced', 'liquid'])): ?>
                                <option value="none" <?php if ($node->template_suffix == 'none') echo 'selected'; ?>>None</option>
                            <?php endif ?>

                            <?php foreach (\Ds\Models\Node::getTemplateSuffixes() as $template_suffix): ?>
                                <option value="<?= e($template_suffix) ?>"  <?php if ($node->template_suffix == $template_suffix) echo 'selected'; ?>><?= e(ucwords(strtr($template_suffix,'-',' '))) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="text-muted">Select the template you want this page to use.</small>
                    </div>
                </div>

                <?php if (in_array($node->type, ['html', 'advanced', 'liquid'])): ?>
                    <div class="form-group">
                        <label for="title" class="col-sm-3 control-label">Content Type</label>
                        <div class="col-sm-5 col-lg-4">
                            <select name="type" id="type" class="form-control">
                                <option value="html" <?php if ($node->type == 'html') echo 'selected'; ?>>Page</option>
                                <option value="advanced" <?php if ($node->type == 'advanced') echo 'selected'; ?>>Advanced Page (HTML)</option>
                                <option value="liquid" <?php if ($node->type == 'liquid') echo 'selected'; ?>>Advanced Page (Liquid)</option>
                            </select>
                        </div>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="type" id="type" value="<?= e($node->type) ?>">
                <?php endif; ?>

                <?php if (in_array($node->type, ['html', 'advanced', 'liquid'])): ?>

                    <div class="form-group">
                        <label for="featured_image" class="col-sm-3 control-label">Featured Image</label>
                        <div class="col-sm-7">
                            <input type="hidden" id="featured_image_id" name="featured_image_id" value="<?= e($node->featured_image_id) ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" readonly id="featured_image" value="<?= e($node->featuredImage->filename ?? ''); ?>">
                                <div class="input-group-btn">
                                    <a href="#" class="btn btn-primary image-browser" data-input="#featured_image_id" data-filename="#featured_image"><i class="fa fa-photo"></i> Choose</a>
                                    <a href="#" class="btn btn-info clear-field" data-target="#featured_image_id,#featured_image"><i class="fa fa-times"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alt_image" class="col-sm-3 control-label">Alternate Image</label>
                        <div class="col-sm-7">
                            <input type="hidden" id="alt_image_id" name="alt_image_id" value="<?= e($node->alt_image_id) ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" readonly id="alt_image" value="<?= e($node->altImage->filename ?? ''); ?>">
                                <div class="input-group-btn">
                                    <a href="#" class="btn btn-primary image-browser" data-input="#alt_image_id" data-filename="#alt_image"><i class="fa fa-photo"></i> Choose</a>
                                    <a href="#" class="btn btn-info clear-field" data-target="#alt_image_id,#alt_image"><i class="fa fa-times"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif ?>

            </div>

        </div>

    </div>

    <?php if($isNew == 1): ?>
        <div class="panel panel-default <?= e(($node->code == 'home' && $node->isactive) ? 'hidden' : '') ?>">
            <div class="panel-heading">
                Website Navigation
            </div>
            <div class="panel-body">

                <div class="form-horizontal">

                    <div class="form-group">
                        <label for="ishidden" class="col-sm-3 control-label">Visibility</label>
                        <div class="col-sm-6">
                            <select name="ishidden" id="ishidden" class="form-control">
                                <option value="0" <?php if ($node->ishidden == 0) echo 'selected'; ?>>Visible in Website Navigation</option>
                                <option value="1" <?php if ($node->ishidden == 1) echo 'selected'; ?>>Hidden in Website Navigation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="parentid" class="col-sm-3 control-label">Menu Placement</label>
                        <div class="col-sm-7">
                            <select name="parentid" id="parentid" class="form-control">
                                <option value="">(None)</option>
                                <option value="" disabled>--</option>

                                    <?php while ($s = db_fetch_assoc($qSection)) { ?>
                                        <option value="<?= e($s['id']) ?>" <?php if ($s['id'] == $node->parentid) echo 'selected'; ?>><?php if ($s['level'] == 2) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s['title']) ?></option>

                                        <?php
                                            $qSection2 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s['id'],$s['id']));
                                            while ($s2 = db_fetch_assoc($qSection2)) {
                                        ?>
                                            <option value="<?= e($s2['id']) ?>" <?php if ($s2['id'] == $node->parentid) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s2['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s2['title']) ?></option>


                                                <?php
                                                $qSection3 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s2['id'],$s2['id']));
                                                while ($s3 = db_fetch_assoc($qSection3)) {
                                            ?>
                                                <option value="<?= e($s3['id']) ?>" <?php if ($s3['id'] == $node->parentid) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s3['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s3['title']) ?></option>

                                                    <?php
                                                    $qSection4 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s3['id'],$s3['id']));
                                                    while ($s4 = db_fetch_assoc($qSection4)) {
                                                ?>
                                                    <option value="<?= e($s4['id']) ?>" <?php if ($s4['id'] == $node->parentid) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s4['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s4['title']) ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                            </select>
                            <small class="text-muted">The menu item this page belongs to in the menus.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ishidden" class="col-sm-3 control-label">Sequence</label>
                        <div class="col-sm-3 col-lg-2">
                            <input type="text" class="form-control" name="sequence" id="sequence" value="<?= e($node->sequence) ?>" />
                            <small class="text-muted">The contribution in which this page will be displayed in the menus.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="target" class="col-sm-3 control-label">New Window</label>
                        <div class="col-sm-5">
                            <select name="target" id="target" class="form-control">
                                <option value="" <?php if ($node->target == '') echo 'selected'; ?>>No - Open this link in the same browser window.</option>
                                <option value="_blank" <?php if ($node->target == '_blank') echo 'selected'; ?>>Yes - Open this link in a new browser window.</option>
                            </select>
                            <small class="text-muted">Depending on the browser, this page may open in a new tab, not a new window.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="requires_login" class="col-sm-3 control-label">Require Login</label>
                        <div class="col-sm-9 col-lg-7">
                            <input type="hidden" name="requires_login" value="0">
                            <input type="checkbox" class="switch" value="1" name="requires_login" id="requires_login" <?= e(($node->requires_login == 1)?'checked':'') ?> ><br />
                            <small class="text-muted">If checked, this page can only be accessed by supporters that are logged in.</small>
                        </div>
                    </div>

                    <div class="form-group hide_menu_link_when_logged_out">
                        <label for="hide_menu_link_when_logged_out" class="col-sm-3 control-label">Hide Menu Link</label>
                        <div class="col-sm-9 col-lg-7">
                            <input type="hidden" name="hide_menu_link_when_logged_out" value="0">
                            <input type="checkbox" class="switch" value="1" name="hide_menu_link_when_logged_out" id="hide_menu_link_when_logged_out" <?= e(($node->hide_menu_link_when_logged_out == 1)?'checked':'') ?> ><br />
                            <small class="text-muted">When this page requires login, checking this setting will hide the menu link if the supporter is not logged in.</small>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    <?php else: ?>


        <div class="panel panel-default <?= e(($node->code == 'home' && $node->isactive) ? 'hidden' : '') ?>">
            <div class="panel-heading">
                Website Navigation
            </div>
            <div class="panel-body">

                <div class="form-horizontal">

                    <div class="form-group">
                        <label for="ishidden" class="col-sm-3 control-label">Visibility</label>
                        <div class="col-sm-5">
                            <select name="ishidden" id="ishidden" class="form-control">
                                <option value="0" <?php if ($node->ishidden == 0) echo 'selected'; ?>>Visible in Website Navigation</option>
                                <option value="1" <?php if ($node->ishidden == 1) echo 'selected'; ?>>Hidden in Website Navigation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="parentid" class="col-sm-3 control-label">Menu Placement</label>
                        <div class="col-sm-7">
                            <select name="parentid" id="parentid" class="form-control">
                                <option value="">(None)</option>
                                <option value="" disabled>--</option>

                                    <?php while ($s = db_fetch_assoc($qSection)) { ?>
                                        <option value="<?= e($s['id']) ?>" <?php if ($s['id'] == $node->parentid) echo 'selected'; ?>><?php if ($s['level'] == 2) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s['title']) ?></option>

                                        <?php
                                            $qSection2 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s['id'],$s['id']));
                                            while ($s2 = db_fetch_assoc($qSection2)) {
                                        ?>
                                            <option value="<?= e($s2['id']) ?>" <?php if ($s2['id'] == $node->parentid) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s2['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s2['title']) ?></option>


                                                <?php
                                                $qSection3 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s2['id'],$s2['id']));
                                                while ($s3 = db_fetch_assoc($qSection3)) {
                                            ?>
                                                <option value="<?= e($s3['id']) ?>" <?php if ($s3['id'] == $node->parentid) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s3['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s3['title']) ?></option>

                                                    <?php
                                                    $qSection4 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s3['id'],$s3['id']));
                                                    while ($s4 = db_fetch_assoc($qSection4)) {
                                                ?>
                                                    <option value="<?= e($s4['id']) ?>" <?php if ($s4['id'] == $node->parentid) echo 'selected'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s4['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s4['title']) ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>

                            </select>
                            <small class="text-muted">The menu item this page belongs to in the menus.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ishidden" class="col-sm-3 control-label">Sequence</label>
                        <div class="col-sm-3 col-lg-2">
                            <input type="text" class="form-control" name="sequence" id="sequence" value="<?= e($node->sequence) ?>" />
                            <small class="text-muted">The contribution in which this page will be displayed in the menus.</small>
                        </div>
                    </div>

                    <?php if ($node->type == 'menu') { ?>
                        <div class="form-group">
                            <label for="url" class="col-sm-3 control-label">Link to Page</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control ds-urls" value="<?= e($node->url); ?>" name="url" id="url" />
                                <small class="text-muted">Leave this blank unless you want this menu item to link to an existing page.</small>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <label for="target" class="col-sm-3 control-label">New Window</label>
                        <div class="col-sm-7">
                            <select name="target" id="target" class="form-control">
                                <option value="" <?php if ($node->target == '') echo 'selected'; ?>>No - Open this link in the same browser window.</option>
                                <option value="_blank" <?php if ($node->target == '_blank') echo 'selected'; ?>>Yes - Open this link in a new browser window.</option>
                            </select>
                            <small class="text-muted">Depending on the browser, this page may open in a new tab, not a new window.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="requires_login" class="col-sm-3 control-label">Require Login</label>
                        <div class="col-sm-9 col-lg-7">
                            <input type="hidden" name="requires_login" value="0">
                            <input type="checkbox" class="switch" value="1" name="requires_login" id="requires_login" <?= e(($node->requires_login == 1)?'checked':'') ?> ><br />
                            <small class="text-muted">If checked, this page can only be accessed by supporters that are logged in.</small>
                        </div>
                    </div>

                    <div class="form-group hide_menu_link_when_logged_out">
                        <label for="hide_menu_link_when_logged_out" class="col-sm-3 control-label">Hide Menu Link</label>
                        <div class="col-sm-9 col-lg-7">
                            <input type="hidden" name="hide_menu_link_when_logged_out" value="0">
                            <input type="checkbox" class="switch" value="1" name="hide_menu_link_when_logged_out" id="hide_menu_link_when_logged_out" <?= e(($node->hide_menu_link_when_logged_out == 1)?'checked':'') ?> ><br />
                            <small class="text-muted">When this page requires login, checking this setting will hide the menu link unless a supporter is logged in.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (in_array($node->type, ['html', 'advanced', 'liquid'])): ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    Search Engine Optimization
                </div>
                <div class="panel-body">

                    <div class="form-horizontal">

                        <div class="form-group">
                            <label for="ishidden" class="col-sm-3 control-label">Browser Title</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?= e($node->pagetitle); ?>" name="pagetitle" id="pagetitle" />
                                    <div class="input-group-addon"> - <?= e(sys_get('defaultPageTitle')); ?></div>
                                </div>
                                <small class="text-muted">The page title that shows in the browser window. >Leave this blank to use the page title you've already defined above.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="metadescription" class="col-sm-3 control-label">Meta Description</label>
                            <div class="col-sm-9">
                                <textarea name="metadescription" id="metadescription" class="form-control" style="height:70px;"><?= e($node->metadescription); ?></textarea>
                                <small class="text-muted">Description used by search engines to index this page.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="metakeywords" class="col-sm-3 control-label">Meta Keywords</label>
                            <div class="col-sm-9">
                                <textarea name="metakeywords" id="metakeywords" class="form-control" style="height:70px;"><?= e($node->metakeywords); ?></textarea>
                                <small class="text-muted">The keywords a search engine may use to index this page.</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <?php if (feature('metadata')): ?>
                <?php gc_metadata_schema('page', $node->metadata) ?>
            <?php endif ?>

            <?php foreach ($schemas as $template): ?>
                <?php foreach ($template->schema as $data): ?>
                    <?php gc_metadata_template_suffixes($node, $template, $data) ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

        <?php else: ?>

            <input type="hidden" name="pagetitle" value="<?= e($node->pagetitle) ?>" />
            <input type="hidden" name="metadescription" value="<?= e($node->metadescription); ?>" />
            <input type="hidden" name="metakeywords" value="<?= e($node->metakeywords); ?>" />

        <?php endif; ?>

        <div class="panel panel-default <?= e($node->revisions()->withoutAutosave()->doesntExist() ? 'hidden' : '') ?>">
            <div class="panel-heading">
                Revisions
            </div>
            <div class="panel-body">
                <div class="flow-root">
                    <ul role="list" class="list-none pl-0 -mb-2">
                        <?php foreach ($node->revisions->sortByDesc('created_at') as $revision): ?>

                            <li>
                                <div class="relative">
                                    <div class="relative flex items-center space-x-3">
                                        <div>
                                            <div class="relative px-1">
                                                <div class="h-6 w-6 bg-gray-100 rounded-full ring-8 ring-white flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1 py-1.5">
                                            <div class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900"><?= e($revision->createdBy->full_name) ?></span>,
                                                <span class="whitespace-nowrap"><?= e(toLocalFormat($revision->created_at, 'humans_short')) ?></span>
                                                (<a href="<?= e(route('backend.page.edit', ['i' => $node, 'revision' => $revision])) ?>"><?= e(toLocalFormat($revision->created_at, 'M j, Y @ h:ia')) ?></a>)
                                                <?php if ($revision->autosave): ?>
                                                    <span class="text-gray-900">[Autosave]</span>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        </div>

    <?php endif; ?>


<?php if($node->exists && !in_array($node->type,['category','menu']) && $node->code !== 'home'): ?>
    <div class="modal modal-info fade" id="modal-node-url">
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
                            <div class="input-group-addon"><?= e('https://'.site('secure_domain')) ?>/</div>
                            <input type="text" class="form-control" name="url" value="<?= e(ltrim($node->abs_url, '/')) ?>" placeholder="page-title-goes-here">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

</form>

<script>
spaContentReady(function() {
    $('#metadata_donation_sidebar').on('switchChange.bootstrapSwitch', function(e, state){
        if (state) {
            $(this).parents('.form-group').siblings().show();
        } else {
            $(this).parents('.form-group').siblings().hide();
        }
    }).trigger('switchChange.bootstrapSwitch', [<?= e($node->metadata['donation_sidebar'] ? 'true' : 'false') ?>]);

    $('#requires_login').on('switchChange.bootstrapSwitch', function(e, state){
        if (state) {
            $(this).parents('.form-horizontal').find('.hide_menu_link_when_logged_out').show();
        } else {
            $(this).parents('.form-horizontal').find('.hide_menu_link_when_logged_out').hide();
        }
    }).trigger('switchChange.bootstrapSwitch', [<?= e($node->requires_login ? 'true' : 'false') ?>]);

    $('#template-suffix').bind('change', function() {
        $('.template-suffix').addClass('hide').attr('disabled', true);
        $('.template-suffix.template-suffix--' + $(this).val()).removeClass('hide').removeAttr('disabled');
    }).change();
});
</script>
