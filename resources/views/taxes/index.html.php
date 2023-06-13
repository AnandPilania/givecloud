<?php if (sys_get('taxcloud_api_key')): ?>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                <?= e($pageTitle) ?>
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 text-center text-muted">

            <i class="fa fa-5x fa-check"></i>
            <h1 class="text-muted">Tax Cloud Enabled</h1>
            <p>
                You have enabled TaxCloud to manage your sales tax.<br>
                <a href="/jpanel/settings/taxcloud">Update Tax Cloud Settings</a>
            </p>

        </div>
    </div>

<?php else: ?>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                <?= e($pageTitle) ?>

                <div class="pull-right">
                    <a href="/jpanel/taxes/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-xs hidden-sm"> Add</span></a>
                    <a href="https://help.givecloud.com/en/articles/3081744-sales-tax" target="_blank" class="btn btn-default btn-outline"><i class="fa fa-book"></i> Getting Started</a>
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
                <th width="16"></th>
                <th width="80">Code</th>
                <th>Description</th>
                <th>%</th>
                <th width="80">Regions</th>
            </tr>
                    </thead>
                    <tbody>
        <?php while ($r = db_fetch_assoc($qList)) { ?>
            <tr>
                <td width="16"><a href="/jpanel/taxes/edit?i=<?= e($r['id']) ?>"><i class="fa fa-search"></i></a></td>
                <td><?= e($r['code']) ?></td>
                <td><?= e($r['description']) ?></td>
                <td><?= e($r['rate']) ?></td>
                <td><?= e($r['regioncount']) ?></td>
            </tr>
        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>
