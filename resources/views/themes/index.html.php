<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<div class="row">
    <div class="col-md-12">
        <div class="row">
        <?php foreach ($themes->where('title','Global') as $theme): ?>

            <div class="col-lg-4 col-sm-4 col-xs-6">
                <div class="theme-block">
                    <?php if (!$theme->locked): ?><div class="flag flag-info"><i class="fa fa-unlock"></i> Unlocked</div><?php endif; ?>

                    <div>
                        <img src="<?= e($theme->thumbnail) ?>" class="theme-thumbnail">
                    </div>
                    <hr>
                    <div class="theme-desc">
                        <h4><?= e($theme->title) ?> <?php if ($theme->active): ?><span class="label label-xs label-success"><i class="fa fa-check"></i> Active</span><?php endif ?></h4>
                        <p><?= e($theme->description) ?></p>
                    </div>

                    <hr>
                    <div class="theme-footer">
                        <?php if ($theme->active): ?>
                            <a href="/jpanel/design/customize"><div class="btn btn-primary"><i class="fa fa-paint-brush fa-fw"></i> Edit</div></a>
                        <?php else: ?>
                            <a href="/jpanel/themes/<?= e($theme->id) ?>/activate"><div class="btn btn-success">Activate</div></a>
                        <?php endif ?>
                        <?php if ($theme->locked): ?>
                            <div class="pull-right">
                                <a href="<?= e(secure_site_url("jpanel/themes/{$theme->id}-latest.zip")) ?>" class="btn btn-default" title="Download"><i class="fa fa-fw fa-download"></i> Download</a>&nbsp;
                                <?php if (is_super_user()): ?>
                                    <a href="javascript:void(0);" onclick="$.confirm('Do you want to unlock this theme? This gives access to the advanced website editor.<span class=\'text-danger\'><br><br><strong><i class=\'fa fa-exclamation-triangle\'></i> Warning</strong> Unlocking means that theme updates won\'t be auto-installed.</span>', function(){ window.location = '/jpanel/themes/<?= e($theme->id) ?>/unlock'; }, 'danger', 'fa-unlock', '#690202');" class="btn btn-default" title="Unlock"><i class="fa fa-code fa-fw"></i> Code</a>
                                <?php else: ?>
                                    <a href="javascript:void(0);" onclick="$.upgrade('<img src=\'/jpanel/assets/images/theme-editor.png\' class=\'upgrade-feature-img\'><p>Upgrade to access the core template files for your theme.</p><ul class=\'fa-ul top-gutter\'><li><i class=\'fa fa-li fa-check\'></i> HTML + Liquid Templates</li><li><i class=\'fa fa-li fa-check\'></i> SCSS Editting</li><li><i class=\'fa fa-li fa-check\'></i> Javascript Access</li><li><i class=\'fa fa-li fa-check\'></i> Customize Payment Pages</li><li><i class=\'fa fa-li fa-check\'></i> Custom Schemas</li></ul>');" class="btn btn-default" title="Unlock"><i class="fa fa-code fa-fw"></i> Code</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="pull-right">
                                <a href="javascript:void(0);" onclick="$.confirm('Do you want to relock your theme? <span class=\'text-danger\'><br><br><strong><i class=\'fa fa-exclamation-triangle\'></i> Warning</strong> Locking means that any template, stylesheet or javascript customizations you\'ve made will be lost.</span>', function(){ window.location = '/jpanel/themes/<?= e($theme->id) ?>/lock'; }, 'danger', 'fa-unlock', '#690202');" class="btn btn-default" title="Lock"><i class="fa fa-lock fa-fw"></i></a>&nbsp;
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-download"></i> Download</button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="<?= e(secure_site_url("jpanel/themes/{$theme->id}.zip")) ?>"><i class="fa fa-fw fa-download"></i> Current</a></li>
                                        <li><a href="<?= e(secure_site_url("jpanel/themes/{$theme->id}-latest.zip")) ?>"><i class="fa fa-fw fa-download"></i> Latest Version</a></li>
                                    </ul>
                                </div>&nbsp;
                                <a href="/jpanel/themes/<?= e($theme->id) ?>/editor"><div class="btn btn-default"><i class="fa fa-code fa-fw"></i> Code</div></a>
                            </div>
                        <?php endif ?>

                    </div>
                </div>
            </div>

        <?php endforeach ?>
        </div>
    </div>
</div>
