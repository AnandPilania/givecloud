
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <?php if(user()->can('promocode.add')): ?>
                <div class="pull-right">
                    <a href="/jpanel/promotions/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
                </div>
            <?php endif; ?>
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
                        <th width="200">Code</th>
                        <th>Description</th>
                        <th>Discount</th>
                        <th>Start</th>
                        <th>End</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promos as $promo): ?>
                        <tr>
                            <td width="16"><a href="/jpanel/promotions/edit?i=<?= e($promo->id) ?>"><i class="fa fa-search"></i></a></a></td>
                            <td>
                                <?= e($promo->code) ?>
                                <?php if($promo->membershipsCount > 0): ?><span class="pull-right text-muted"><i class="fa fa-lock"></i> Locked</span><?php endif; ?>
                            </td>
                            <td><?= e($promo->description) ?></td>
                            <td data-order="<?= e($promo->discount_type) ?>-<?= e($promo->discount) ?>"><?= e($promo->discount_formatted) ?></td>
                            <td data-order="<?= e($promo->startdate) ?>"><?= e($promo->startdate) ?></td>
                            <td data-order="<?= e($promo->enddate) ?>"><?= e($promo->enddate) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
