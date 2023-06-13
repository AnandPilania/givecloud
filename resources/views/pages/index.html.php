<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <?= e($pageTitle) ?>&nbsp;

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if (user()->can('node.add')) { ?>
                    <div class="btn-group">
                        <a href="#modal-new-page" data-toggle="modal" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add a Page</span></a>
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            <li><a href="#modal-new-page" data-toggle="modal"><i class="fa fa-plus fa-fw"></i> Add a Page</a></li>
                            <li><a href="#modal-new-link" data-toggle="modal"><i class="fa fa-plus fa-fw"></i> Add a Link</a></li>
                            <li><a href="#modal-new-category" data-toggle="modal"><i class="fa fa-plus fa-fw"></i> Add a Category</a></li>
                            <li><a href="#modal-new-menu" data-toggle="modal"><i class="fa fa-plus fa-fw"></i> Add a Menu</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="javascript:$.confirm('Are you sure you want to add all Product Categories to your menu?<br><br><i class=\'fa fa-question-circle\'></i> Adding all product categories will append all your product categories (as you\'ve structured them) to the end of your existing menu.', function(){ location = '/jpanel/pages/add/categories'; }, 'warning', 'fa-exclamation-triangle');"><i class="fa fa-plus fa-fw"></i> Add All Product Categories</a></li>
                        </ul>
                    </div>
                <?php } ?>

                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-gear"></i> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li><a href="#" class="hidden-offline-toggle-btn"><i class="fa fa-square-o fa-fw"></i> Show Offline/Hidden Pages</a></li>
                        <li class="divider"></li>
                        <li><a href="/sitemap.xml" target="_blank"><i class="fa fa-sitemap fa-fw"></i> View Sitemap.xml</a></li>
                    </ul>
                </div>
            </div>

        </h1>
    </div>
</div>

<?php app('flash')->output() ?>

<style>.-hidden-offline { display:none; }</style>

<div class="rounded bg-white p-8">

    <a href="#" class="pull-right hidden-xs hidden-offline-toggle-btn btn btn-sm btn-default"><i class="fa fa-square-o fa-fw"></i> Show Offline/Hidden Pages</a>

    <ul class="dir">
        <li class=""><i class="fa fa-file-o"></i> <a href="/jpanel/pages/edit?i=1" class="">Home</a><ul></ul></li>
        <?= dangerouslyUseHTML(pageCurs(0)) ?>
    </ul>

</div>

<div class="modal fade modal-success" tabindex="-1" role="dialog" id="modal-new-page">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus fa-fw"></i> Add a Page</h4>
            </div>

            <form action="/jpanel/pages/quick-add" method="post">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="type" value="html">

                <div class="modal-body">

                    <div class="form-group form-group-lg">
                        <label>Page Name</label>
                        <input type="text" name="name" value="" required placeholder="My New Page" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Menu</label>
                        <select class="form-control" name="parent_id" required>
                            <option value="">(None)</option>
                            <option value="" disabled>--</option>
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= e($menu->id) ?>" <?= e(volt_selected($menu->title, 'Main Menu')); ?>><?= e($menu->title) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Page Layout</label>
                        <select class="form-control" name="template_suffix">
                            <option value="">Default</option>
                            <?php foreach ($templates as $template_suffix): ?>
                                <option value="<?= e($template_suffix) ?>"><?= e(ucwords(strtr($template_suffix,'-',' '))) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_enabled" value="1"> Publish this page now
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success btn-outline" type="submit" name="redirect" value="back">Add</button>
                    <button class="btn btn-success" type="submit" name="redirect" value="">Add & Edit</button>
                </div>
            </form>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade modal-success" tabindex="-1" role="dialog" id="modal-new-link">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus fa-fw"></i> Add a Link</h4>
            </div>

            <form action="/jpanel/pages/quick-add" method="post">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="type" value="menu">

                <div class="modal-body">

                    <div class="form-group form-group-lg">
                        <label>Link Name</label>
                        <input type="text" name="name" value="" required placeholder="My New Menu" class="form-control">
                    </div>

                    <div class="form-group form-group-lg">
                        <label>Url</label>
                        <input type="text" name="url" value="" required placeholder="http://url.org/url" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Menu</label>
                        <select class="form-control" name="parent_id">
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= e($menu->id) ?>"><?= e($menu->title) ?></option>
                            <?php endforeach; ?>
                            <option value="" disabled>--</option>
                            <?php $qSection = db_query('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = 0 AND protected = 0 ORDER BY n.sequence'); ?>
                            <?php while ($s = db_fetch_assoc($qSection)) { ?>
                                <option value="<?= e($s['id']) ?>"><?php if ($s['level'] == 2) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s['title']) ?></option>

                                <?php
                                    $qSection2 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s['id'],$s['id']));
                                    while ($s2 = db_fetch_assoc($qSection2)) {
                                ?>
                                    <option value="<?= e($s2['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s2['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s2['title']) ?></option>


                                        <?php
                                        $qSection3 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s2['id'],$s2['id']));
                                        while ($s3 = db_fetch_assoc($qSection3)) {
                                    ?>
                                        <option value="<?= e($s3['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s3['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s3['title']) ?></option>

                                            <?php
                                            $qSection4 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s3['id'],$s3['id']));
                                            while ($s4 = db_fetch_assoc($qSection4)) {
                                        ?>
                                            <option value="<?= e($s4['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s4['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s4['title']) ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="target" value="_blank"> Open in a new window
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_enabled" value="1"> Publish now
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" type="submit" name="redirect" value="back">Add</button>
                </div>
            </form>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade modal-success" tabindex="-1" role="dialog" id="modal-new-category">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus fa-fw"></i> Add a Category</h4>
            </div>

            <form action="/jpanel/pages/quick-add" method="post">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="type" value="category">

                <div class="modal-body">

                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="category_id" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= e($cat->id) ?>"><?= e($cat->name) ?></option>
                                <?php foreach($cat->categories as $subcat): ?>
                                    <option value="<?= e($subcat->id) ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?= e($subcat->name) ?></option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Menu</label>
                        <select class="form-control" name="parent_id">
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= e($menu->id) ?>"><?= e($menu->title) ?></option>
                            <?php endforeach; ?>
                            <option value="" disabled>--</option>
                            <?php $qSection = db_query('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = 0 AND protected = 0 ORDER BY n.sequence'); ?>
                            <?php while ($s = db_fetch_assoc($qSection)) { ?>
                                <option value="<?= e($s['id']) ?>"><?php if ($s['level'] == 2) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s['title']) ?></option>

                                <?php
                                    $qSection2 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s['id'],$s['id']));
                                    while ($s2 = db_fetch_assoc($qSection2)) {
                                ?>
                                    <option value="<?= e($s2['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s2['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s2['title']) ?></option>


                                        <?php
                                        $qSection3 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s2['id'],$s2['id']));
                                        while ($s3 = db_fetch_assoc($qSection3)) {
                                    ?>
                                        <option value="<?= e($s3['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s3['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s3['title']) ?></option>

                                            <?php
                                            $qSection4 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s3['id'],$s3['id']));
                                            while ($s4 = db_fetch_assoc($qSection4)) {
                                        ?>
                                            <option value="<?= e($s4['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s4['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s4['title']) ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="include_subcategories" value="1" checked> Include any subcategories
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_enabled" value="1" checked> Publish now
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" type="submit" name="redirect" value="back">Add</button>
                </div>
            </form>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade modal-success" tabindex="-1" role="dialog" id="modal-new-menu">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus fa-fw"></i> Add a Menu</h4>
            </div>

            <form action="/jpanel/pages/quick-add" method="post">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="type" value="menu">

                <div class="modal-body">

                    <div class="form-group form-group-lg">
                        <label>Menu Name</label>
                        <input type="text" name="name" value="" required placeholder="My New Menu" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Parent Menu</label>
                        <select class="form-control" name="parent_id">
                            <option value="0">[ None ]</option>
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= e($menu->id) ?>"><?= e($menu->title) ?></option>
                            <?php endforeach; ?>
                            <option value="" disabled>--</option>
                            <?php $qSection = db_query('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = 0 AND protected = 0 ORDER BY n.sequence'); ?>
                            <?php while ($s = db_fetch_assoc($qSection)) { ?>
                                <option value="<?= e($s['id']) ?>"><?php if ($s['level'] == 2) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s['title']) ?></option>

                                <?php
                                    $qSection2 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s['id'],$s['id']));
                                    while ($s2 = db_fetch_assoc($qSection2)) {
                                ?>
                                    <option value="<?= e($s2['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s2['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s2['title']) ?></option>


                                        <?php
                                        $qSection3 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s2['id'],$s2['id']));
                                        while ($s3 = db_fetch_assoc($qSection3)) {
                                    ?>
                                        <option value="<?= e($s3['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s3['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s3['title']) ?></option>

                                            <?php
                                            $qSection4 = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE n.parentid = %d ORDER BY n.sequence',$s3['id'],$s3['id']));
                                            while ($s4 = db_fetch_assoc($qSection4)) {
                                        ?>
                                            <option value="<?= e($s4['id']) ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($s4['level'] == 3) echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?><?= e($s4['title']) ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_enabled" value="1" checked> Publish this menu now
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" type="submit" name="redirect" value="back">Add</button>
                </div>
            </form>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
    spaContentReady(function() {
        $('#modal-new-menu, #modal-new-page, #modal-new-link').on('shown.bs.modal',function(e){
            $(e.target).find('input[type=text]:first').focus();
        })
    });
</script>
