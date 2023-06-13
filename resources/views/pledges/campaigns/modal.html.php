<div class="modal-header" style="margin-bottom:-20px;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><i class="fa fa-thermometer"></i> <?= e(($pledgeCampaign->exists) ? $pledgeCampaign->name : 'New Pledge Type') ?></h4>
</div>

<form action="/jpanel/pledges/campaigns/<?php if($pledgeCampaign->exists): ?><?= e($pledgeCampaign->id) ?>/update<?php else: ?>insert<?php endif; ?>" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <div class="modal-body">
        <div class="form-group">
            <label>Campaign Name</label>
            <input type="text" class="form-control" name="name" value="<?= e($pledgeCampaign->name) ?>" required>
        </div>
        <div class="row row-padding-sm">
            <div class="form-group col-sm-6">
                <label>Start Date</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                    <input type="tel" class="form-control datePretty" autocomplete="no" name="start_date" value="<?= e(($pledgeCampaign->start_date) ? $pledgeCampaign->start_date->format('M d, Y') : '') ?>" placeholder="n/a">
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label>End Date</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                    <input type="tel" class="form-control datePretty" autocomplete="no" name="end_date" value="<?= e(($pledgeCampaign->end_date) ? $pledgeCampaign->end_date->format('M d, Y') : '') ?>" placeholder="n/a">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Track Sales Of:</label>
            <select class="form-control ds-products auto-height" name="product_ids[]" multiple size="1">
                <?php if($pledgeCampaign->products): ?>
                    <?php foreach($pledgeCampaign->products as $product): ?>
                        <option value="<?= e($product->id) ?>" selected="selected"><?= e($product->name) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-info">Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    </div>
</form>
