
<script>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this redirect?');
        if (f) {
            document.aliasForm.action = '/jpanel/aliases/<?= e($alias->id) ?>/destroy';
            document.aliasForm.submit();
        }
    }
</script>

<form name="aliasForm" method="post" action="/jpanel/aliases/<?= e((!$alias->exists)?'add':$alias->id.'/edit') ?>" enctype="multipart/form-data">
<?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Redirect

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if(($alias->exists && $alias->userCan('edit')) || (!$alias->exists && $alias->userCan('add'))): ?>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                <?php endif; ?>
                <?php if($alias->exists && $alias->userCan('edit')): ?>
                    <a onclick="onDelete();" class="btn btn-danger"><i class="fa fa-times fa-fw"></i><span class="hidden-xs hidden-sm"> Delete</span></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<div class="panel panel-default">
    <div class="panel-heading">
        General
    </div>
    <div class="panel-body">

        <div class="form-horizontal">

            <div class="form-group">
                <label for="source" class="col-sm-3 control-label">Source</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <?= e(secure_site_url()) ?>/
                        </div>
                        <input type="text" class="form-control" name="source" id="source" value="<?= e($alias->source) ?>" />
                    </div>
                    <small class="text-muted">The URL you want to perform a redirect on.</small>
                    <?php if(count(site()->custom_domains) > 0): ?>
                        <br><small class="text-info">This URL will work with all domains you have pointing to your GC site including:<br>
                        <ul class="fa-ul">
                            <?php foreach(site('custom_domains') as $domain): ?>
                                <li><i class="fa-li fa fa-check"></i><?= e($domain) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        </small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="alias" class="col-sm-3 control-label">Destination</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control ds-urls" name="alias" id="alias" value="<?= e($alias->alias) ?>" />
                    <small class="text-muted">The URL you want the redirect to go to.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Type</label>
                <div class="col-sm-6">

                    <div class="radio">
                        <label>
                            <input type="radio" name="type" value="http_301" <?= e(($alias->type == 'http' && $alias->status_code == '301') ? 'checked' : '') ?> > Permanent (301) redirect
                        </label>
                        <p class="help-block">
                            These redirections are meant to last forever. They imply that the Source URL should no longer be used, and replaced with the new one.
                            Search engine robots, RSS readers, and other crawlers will update the Source URL for the resource.
                        </p>
                    </div>

                    <div class="radio">
                        <label>
                            <input type="radio" name="type" value="http_302" <?= e(($alias->type == 'http' && $alias->status_code == '302') ? 'checked' : '') ?> > Temporary (302) redirect
                        </label>
                        <p class="help-block">
                            These redirections are meant to be temporary. They imply that the Source URL will continue to be used.
                            Search engines expect the Source URL will be active and will not update their indexes.
                        </p>
                    </div>

                    <div class="radio">
                        <label>
                            <input type="radio" name="type" value="html" <?= e(($alias->type == 'html') ? 'checked' : '') ?> > HTML redirect
                        </label>
                        <p class="help-block">
                            These redirects are a type of redirect executed on the page level rather than the server level. They are usually slower, and not a
                            recommended SEO technique.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</form>
