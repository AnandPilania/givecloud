<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <a href="/jpanel/sponsorship/segments/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
            </div>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead>
        <tr>
            <th style="width:16px;"></th>
            <th style="width:130px; text-align:center;">Sequence</th>
            <th>Name</th>
            <th style="width:130px;">Type</th>
            <th style="width:130px; text-align:center;">Options</th>
        </tr>
                </thead>
                <tbody>
    <?php foreach ($segments as $segment): ?>
        <tr>
            <td width="16"><a href="/jpanel/sponsorship/segments/edit?i=<?= e($segment->id) ?>"><i class="fa fa-search"></i></a></td>
            <td style="text-align:center;"><?= e($segment->sequence) ?></td>
            <td><?= e($segment->name) ?><?php if($segment->show_in_detail == 0): ?>&nbsp;&nbsp;<i class="fa fa-lock"></i><?php endif ?></td>
            <td><?= e($segment->type_formatted) ?></td>
            <td style="text-align:center;"><?= e($segment->items->count()) ?></td>
        </tr>
    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
