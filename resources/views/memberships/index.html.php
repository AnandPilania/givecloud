<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <a href="/jpanel/memberships/add" class="btn btn-success"><i class="fa fa-plus fa-fw"></i><span class="hidden-sm hidden-xs"> Add</span></a>
                <a href="https://help.givecloud.com/en/articles/2822810-groups-memberships" target="_blank" class="btn btn-default"><i class="fa fa-book"></i> Getting Started</a>
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
                        <th>Promo(s)</th>
                        <?php if(dpo_is_enabled()): ?><th style="width:80px; text-align:center;">DP ID</th><?php endif; ?>
                        <th style="width:110px; text-align:center;">Members</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($memberships as $membership): ?>
                    <tr>
                        <td width="16"><a href="/jpanel/memberships/edit?i=<?= e($membership->id) ?>"><i class="fa fa-search"></i></a></td>
                        <td><?= e($membership->name) ?> <small class="text-muted">(ID <?= e($membership->id) ?>)</small></td>
                        <td><?php if ($membership->promoCodes): ?><?php foreach ($membership->promoCodes as $promo): ?><a href="/jpanel/promotions/<?= e($promo->code) ?>/edit" class="btn btn-xs btn-default btn-outline" target="_blank"><?= e($promo->code) ?></a>&nbsp;<?php endforeach; ?><?php endif; ?></td>
                        <?php if(dpo_is_enabled()): ?><td style="width:80px; text-align:center;"><?= e($membership->dp_id) ?></td><?php endif; ?>
                        <td style="width:80px; text-align:center;"><?= e(number_format($membership->member_count)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
