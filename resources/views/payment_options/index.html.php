<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <a href="/jpanel/sponsorship/payment_options/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
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
            <th>Name</th>
            <th align="center" width="140">Options</th>
            <th align="center" width="140">Use Count</th>
        </tr>
                </thead>
                <tbody>
    <?php foreach ($payment_groups as $group): ?>
        <tr>
            <td width="16"><a href="/jpanel/sponsorship/payment_options/edit?i=<?= e($group->id) ?>"><i class="fa fa-search"></i></a></td>
            <td><?= e($group->name) ?></td>
            <td align="center" width="140"><?= e(number_format($group->option_count)) ?></td>
            <td align="center" width="140"><?= e(number_format($group->use_count)) ?></td>
        </tr>
    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
