
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Redirects

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <?php if(user()->can('alias.add')): ?>
                <a href="/jpanel/aliases/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i> Add</a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Source</th>
                        <th>Redirect</th>
                        <th width="140" class="text-center">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aliases as $alias): ?>
                        <tr>
                            <td><a href="/jpanel/aliases/<?= e($alias->id) ?>/edit"><i class="fa fa-search"></i></a></td>
                            <td><?= e($alias->source) ?> <a href="/<?= e(trim($alias->source,'/')) ?>" target="_blank"><i class="fa fa-external-link"></i></a></td>
                            <td><?= e($alias->alias) ?></td>
                            <td class="text-center"><?= e($alias->status_code ?? strtoupper($alias->type)) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
