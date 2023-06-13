<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus"></i><span class="hidden-xs hidden-sm"> Add</span> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li><a href="/jpanel/shipping/add" ><i class="fa fa-plus fa-fw"></i> Add Shipping Method</a></li>
                        <li><a href="/jpanel/shipping/tiers/add" ><i class="fa fa-plus fa-fw"></i> Add Shipping Tier</a></li>
                    </ul>
                </div>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="16"></th>
                        <th>Name</th>
                        <th>Priority</th>
                        <th>Country(s)</th>
                        <th>Region(s)</th>
                        <?php foreach ($tiers as $tier): ?>
                            <th class="text-center" width="140"><a href="/jpanel/shipping/tiers/edit?i=<?= e($tier->id) ?>"><?= e(money($tier->min_value)) ?> <?= e(($tier->is_infinite)?'+':'- '.money($tier->max_value)) ?> <i class="fa fa-pencil-square"></i></a></th>
                        <?php endforeach ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($methods as $method): ?>
                        <tr class="<?= e(($method->is_default == 1)?'bold':'') ?>">
                            <td width="16"><a href="/jpanel/shipping/edit?i=<?= e($method->id) ?>"><i class="fa fa-search"></i></a></td>
                            <td><?= e($method->name) ?> <?php if($method->is_default): ?><span class="label label-default"><i class="fa fa-check"></i> Default</span><?php endif ?></td>
                            <td><?= e($method->priority) ?></td>
                            <td><?php if($method->countries): foreach($method->countries as $country): ?><span class="btn btn-xs btn-default btn-outline"><?= e($country) ?></span>&nbsp;<?php endforeach; else: ?><span class="text-muted">Any Country</span><?php endif; ?></td>
                            <td><?php if($method->regions): foreach($method->regions as $region): ?><span class="btn btn-xs btn-default btn-outline"><?= e($region) ?></span>&nbsp;<?php endforeach; else: ?><span class="text-muted">Any Region</span><?php endif; ?></td>
                            <?php foreach($tiers as $tier): ?>
                                <?php $qValue = db_query(sprintf("SELECT amount FROM shipping_value WHERE method_id = %d AND tier_id = %d",$method->id,$tier->id)); ?>
                                <?php $value = db_fetch_object($qValue); ?>
                                <td class="text-center"><?= e(money($value->amount)) ?>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
