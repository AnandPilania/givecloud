<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><i class="fa fa-users fa-fw"></i> <?= e(sys_get('syn_groups')) ?></h4>
</div>

<form id="group-account-form" method="post" action="<?= e(($groupAccount) ? '/jpanel/group_accounts/update' : '/jpanel/group_accounts/insert') ?>">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="group_account_id" value="<?= e($groupAccount->id ?? '') ?>">
    <input type="hidden" name="account_id" value="<?= e($account->id ?? '') ?>">

    <div class="modal-body">

        <div class="form-group">
            <label for="" class="control-label"><?= e(sys_get('syn_groups')) ?></label>
            <select class="form-control" name="group_id" id="" <?= e(($groupAccount->order_item_id) ? 'disabled' : 'required') ?> >
                <option value="">Choose One...</option>
                <?php foreach ($all_groups as $group): ?>
                    <option value="<?= e($group->id) ?>" <?= e(($group->id == $groupAccount->group_id) ? 'selected' : '') ?>>
                        <?= e($group->name) ?>
                        <?php if($group->expiry_description): ?>(<?= e($group->expiry_description) ?>)<?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="form-group col-md-6">
                <label for="" class="control-label">Start Date</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                    <?php
                        if ($groupAccount->start_date) {
                            $start_date = fromUtc($groupAccount->start_date, 'M j, Y');
                        } else if (!$group) {
                            $start_date = fromUtc('today', 'M j, Y');
                        } else {
                            $start_date = '';
                        }
                    ?>
                    <input type="text" class="form-control datePretty" name="start_date" value="<?= e($start_date) ?>">
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="" class="control-label">Source</label>
                <?php if ($groupAccount->orderItem): ?>
                    <div class="form-control-static">
                        <a href="<?= e(route('backend.orders.edit', $groupAccount->orderItem->order)) ?>">
                            Contribution #<?= e($groupAccount->orderItem->order->invoicenumber) ?>
                        </a>
                    </div>
                <?php else: ?>
                    <select name="source" class="form-control selectize-tag" required>
                        <option></option>
                        <?php foreach($sources as $source): ?>
                            <option value="<?= e($source) ?>" <?= e(($source == $groupAccount->source) ? 'selected' : '') ?> ><?= e($source) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <?php if($groupAccount): ?>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="" class="control-label">End Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                        <input type="text" class="form-control datePretty" name="end_date" value="<?= e(($groupAccount->end_date) ? fromUtc($groupAccount->end_date,'M j, Y') : '') ?>">
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="" class="control-label">End Reason</label>
                    <select name="end_reason" class="form-control selectize-tag">
                        <?php foreach($end_reasons as $reason): ?>
                            <option value="<?= e($reason) ?>" <?= e(($reason == $groupAccount->end_reason) ? 'selected' : '') ?> ><?= e($reason) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($relatedgroups->count() > 0): ?>
            <div>
                <small><a href="#related-groups" data-toggle="collapse">Related Groups...</a></small>
                <div class="text-muted text-sm collapse top-gutter-sm" id="related-groups">
                <ul>
                    <?php $relatedgroups->each(function($group_account) { ?>
                        <li><a href="#" data-group-account-id="<?= e($group_account->id) ?>"><?= e($group_account->group->name) ?><?= e($group_account->start_date ? ' - ' . $group_account->start_date : '') ?><?= e($group_account->end_date ? ' - ' . $group_account->end_date : '') ?><?= e($group_account->end_reason ? ' (End Reason: ' . $group_account->end_reason . ')' : '') ?><?= e($group_account->source ? ' (Source: ' . $group_account->source . ')' : '') ?></a></li>
                    <?php }); ?>
                </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if (is_super_user() && $groupAccount->metadata): ?>
            <div>
                <small><a href="#advanced-meta" data-toggle="collapse">More Detail...</a></small>
                <div class="text-muted text-sm collapse top-gutter-sm" id="advanced-meta">
                    <?php foreach($groupAccount->metadata() as $key => $val): ?>
                        <?php if ($key === 'dp_data') continue; ?>
                        <strong><?= e(ucwords(str_replace("_", " ", $key))) ?>:</strong> <?= e($val) ?><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <div class="modal-footer">
        <?php if ($groupAccount->orderItem): ?>
            <button type="button" onclick="alert('You cannot delete a group or membership assigned from a contribution.\n\nTry setting the end date to a time in the past and specify the end reason.');" class="btn btn-danger btn-outline pull-left"><i class="fa fa-trash fa-fw"></i> Delete</button>
        <?php else: ?>
            <button type="button" data-group-account-action="delete" class="btn btn-danger btn-outline pull-left"><i class="fa fa-trash fa-fw"></i> Delete</button>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary"><i class="fa fa-check fa-fw"></i> Update</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
</form>
