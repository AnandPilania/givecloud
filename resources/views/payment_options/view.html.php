
<script>
    <?php if ($payment_group->use_count == 0): ?>
    function onDelete () {
        var f = confirm('Are you sure you want to delete this payment bundle?');
        if (f) {
            document.payment_group.action = '/jpanel/sponsorship/payment_options/destroy';
            document.payment_group.submit();
        }
    }
    <?php else: ?>
    function onDelete () {
        alert('You cannot delete this payment bundle. It is in use (<?= e($payment_group->use_count) ?>) time(s).');
    }
    <?php endif; ?>

    function onRestore () {
        var f = confirm('Are you sure you want to restore (un-delete) this payment bundle?');
        if (f) {
            document.payment_group.action = '/jpanel/sponsorship/payment_options/restore';
            document.payment_group.submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <?= e($pageTitle) ?>

            <div class="pull-right">
                <?php if(!$payment_group->trashed()): ?>
                    <a onclick="$('#payment_group_form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></a>
                    <a onclick="onDelete();" class="btn btn-danger <?= e(($payment_group == false) ? 'hidden' : '') ?>"><i class="fa fa-trash fa-fw"></i></a>
                <?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?php if($payment_group->trashed()): ?>
    <div class="alert alert-danger">
        <i class="fa fa-trash"></i> This payment group has been deleted. <a onclick="onRestore();" class="btn btn-success btn-xs"><i class="fa fa-refresh fa-fw"></i><span class="hidden-xs hidden-sm"> Restore</span></a>
    </div>
<?php endif; ?>

<style>
    .dynamic-form-table { border-collapse:collapse; }
    .dynamic-form-table th { border-bottom:1px solid #000; padding:4px; }
    .dynamic-form-table td { border-bottom:1px solid #eee; padding:4px; }
</style>

<form id="payment_group_form" name="payment_group" method="post" action="/jpanel/sponsorship/payment_options/save" enctype="multipart/form-data">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($payment_group->id) ?>" />

    <div class="panel panel-default">
        <div class="panel-heading">
            General
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">Name</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" name="name" id="name" value="<?= e($payment_group->name) ?>" maxlength="150" />
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            Options
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="payment_options-table" class="dynamic-form-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="text-align:left;">#</th>
                            <th style="text-align:left;">Type</th>
                            <th style="text-align:left; width:420px;">Recurring Options</th>
                            <th style="text-align:left;">Amount</th>
                            <th style="text-align:left;"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <Br />
            <a href="javascript:void(0);" onclick="j.payment_group.options.add();" class="btn btn-info"><i class="fa fa-plus"></i> Add Option</a>
            <script type="application/json" id="paymentOptionsJSON"><?= dangerouslyUseHTML(json_encode($payment_group->options)) ?></script>
        </div>
    </div>
</form>

<?php if($payment_group->exists): ?>
<hr />
<small>
    Created by <?= e($payment_group->createdBy->full_name) ?> on <?= e($payment_group->created_at) ?>.<br />
    <?php if ($payment_group->updatedBy): ?>Last modified by <?= e($payment_group->updatedBy->full_name) ?> on <?= e($payment_group->updated_at) ?>.<?php endif; ?>
</small>
<?php endif; ?>
