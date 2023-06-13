
<div id="theme-editor-app">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                <?= e($pageTitle) ?>

                <div class="pull-right">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Download</span> <span class="caret"></span></button>
                        <ul class="dropdown-menu pull-right" role="menu">
                            <li><a href="<?= e(secure_site_url("jpanel/themes/{$theme->id}.zip")) ?>"><i class="fa fa-fw fa-download"></i> Current</a></li>
                            <li><a href="<?= e(secure_site_url("jpanel/themes/{$theme->id}-latest.zip")) ?>"><i class="fa fa-fw fa-download"></i> Latest Version</a></li>
                        </ul>
                    </div>
                </div>
            </h1>
        </div>
    </div>
    <theme-editor></theme-editor>
</div>

<script>
window['theme_editor_assets'] = <?= dangerouslyUseHTML(json_encode($assets)) ?>;
</script>
