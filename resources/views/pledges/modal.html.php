<div class="modal-header" style="margin-bottom:-20px;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><i class="fa fa-thermometer"></i> <?= e(($pledge->account) ? \Illuminate\Support\Str::possessive($pledge->account->display_name) : 'New') ?> Pledge</h4>

    <?php if($pledge->exists): ?>
        <div class="row">
            <div class="col-sm-6">
                <?php if($pledge->funded_percent >= 1): ?>
                    <div class="progress progress-lg" style="margin-bottom:0px; margin-top:14px;">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= e($pledge->funded_percent*100) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width:3em; font-weight:bold; width:<?= e(min(100,$pledge->funded_percent*100)) ?>%;">
                        <?= e(number_format($pledge->funded_percent*100,1)) ?>%
                        </div>
                    </div>
                <?php else: ?>
                    <div class="progress progress-lg" style="margin-bottom:0px; margin-top:14px;">
                        <div class="progress-bar <?= e(($pledge->funded_percent == 0) ? 'progress-bar-default' : 'progress-bar-info') ?>" role="progressbar" aria-valuenow="<?= e($pledge->funded_percent*100) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width:3em; width:<?= e(min(100,$pledge->funded_percent*100)) ?>%;">
                        <?= e(number_format($pledge->funded_percent*100,1)) ?>%
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-sm-3 stat">
                <div class="stat-value-sm"><strong><?= e(money($pledge->funded_amount, $pledge->currency_code)) ?></strong></div>
                <div class="stat-label">Funded</diV>
            </div>
            <div class="col-sm-3 stat">
                <div class="stat-value-sm"><?= e(money($pledge->total_amount, $pledge->currency_code)) ?></div>
                <div class="stat-label">Total Amount</diV>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="pledge-views">

    <!-- /////////////////////// -->
    <!-- ////  GENERAL VIEW //// -->
    <!-- /////////////////////// -->
    <div class="<?php if(!$pledge->exists): ?>hide<?php endif; ?>" id="general-view">
        <div class="modal-body">

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#pledge-detail" aria-controls="pledge-detail" role="tab" data-toggle="tab">Pledge</a></li>
                <li role="presentation"><a href="#pledge-items" aria-controls="pledge-items" role="tab" data-toggle="tab">Payments</a></li>
            </ul>

            <div class="tab-content top-gutter">
                <div role="tabpanel" class="tab-pane active in fade" id="pledge-detail">
                    <?php if($pledge->exists): ?>
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Campaign:</strong></div>
                                    <div class="col-xs-8"><?= e($pledge->campaign->name) ?></div>
                                </div>
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Starts:</strong></div>
                                    <div class="col-xs-8"><?= e($pledge->campaign->start_date ? fromLocalFormat($pledge->campaign->start_date) : 'n/a') ?></div>
                                </div>
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Ends:</strong></div>
                                    <div class="col-xs-8"><?= e($pledge->campaign->end_date ? fromLocalFormat($pledge->campaign->end_date) : 'n/a') ?></div>
                                </div>
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Tracking:</strong></div>
                                    <div class="col-xs-8">
                                        <?php if ($pledge->campaign->products): ?>
                                            <?php foreach($pledge->campaign->products as $product): ?>
                                                <a class="btn btn-default btn-xs"><?= e($product->name) ?></a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">n/a</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Payments:</strong></div>
                                    <div class="col-xs-8"><?= e(number_format($pledge->funded_count)) ?></div>
                                </div>
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>First:</strong></div>
                                    <div class="col-xs-8"><?= e($pledge->first_donation_date ? fromLocalFormat($pledge->first_donation_date) : 'n/a') ?></div>
                                </div>
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Last:</strong></div>
                                    <div class="col-xs-8"><?= e($pledge->last_donation_date ? fromLocalFormat($pledge->last_donation_date) : 'n/a') ?></div>
                                </div>
                            </div>

                            <?php if ($pledge->comments): ?>
                                <div class="col-sm-12">
                                    <hr>
                                    <div class="row mt-2 mb-1">
                                        <div class="col-xs-2"><strong class="inline-block mr-2">Comments:</strong></div>
                                        <div class="col-xs-10"><?= e($pledge->comments) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                </div>

                <div role="tabpanel" class="tab-pane fade" id="pledge-items">

                    <?php if($pledge->exists && $payments->count() > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount <i class="fa fa-fw"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($payments as $payment): ?>
                                    <tr>
                                        <td><?= e(toLocalFormat($payment->order_date)) ?></td>
                                        <?php if ($payment->reference_type == 'Order'): ?>
                                            <td><a href="<?= e(route('backend.orders.edit', $payment->reference_id)) ?>"><?= e($payment->reference) ?></a></td>
                                        <?php elseif ($payment->reference_type == 'Recurring Profile'): ?>
                                            <td><a href="/jpanel/recurring_payments/<?= e($payment->reference_id) ?>"><?= e($payment->reference) ?></a></td>
                                        <?php endif; ?>
                                        <td><?= e($payment->description) ?></td>
                                        <td class="text-right"><?= e(number_format($payment->amount,2)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-muted text-lg" style="margin:80px; width:70%; text-align:center;">
                            No payments yet.<br>
                            <small>Payments are automatically added from all your fundraising tools based on the settings on this pledge.<br><br>To add a payment, use the POS to record a new payment for this supporter using one of the tracked items.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <div class="pull-left">
                <a href="/jpanel/pledges/<?= e($pledge->id) ?>/calculate" data-ajax-modal-link="/jpanel/pledges/<?= e($pledge->id) ?>/calculate" class="btn btn-default"><i class="fa fa-refresh"></i> Update</a>
                <button type="button" class="btn btn-info btn-outline" onclick="$('#general-view, #edit-view').toggleClass('hide');"><i class="fa fa-pencil"></i> Edit</button>
                <button type="button" class="btn btn-danger btn-outline" data-ajax-confirm="Are you sure you want to delete this Pledge?" data-ajax-modal-link="/jpanel/pledges/<?= e($pledge->id) ?>/destroy"><i class="fa fa-trash"></i> Delete</button>
            </div>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

    <!-- /////////////////////// -->
    <!-- ////   EDIT VIEW   //// -->
    <!-- /////////////////////// -->
    <div class="<?php if($pledge->exists): ?>hide<?php endif; ?>" id="edit-view">
        <form action="/jpanel/pledges/<?php if($pledge->exists): ?><?= e($pledge->id) ?>/update<?php else: ?>insert<?php endif; ?>" method="post">
            <div class="modal-body">
                <div class="form-group">
                    <label>Supporter</label>
                    <select class="form-control ds-members" name="account_id">
                        <?php if($pledge->account_id): ?>
                            <option selected value="<?= e($pledge->account->id) ?>"><?= e($pledge->account->display_name) ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="row row-padding-sm">
                    <div class="form-group col-sm-8">
                        <label>Campaign</label>
                        <select class="form-control" required name="pledge_campaign_id">
                            <?php foreach($pledgeCampaigns as $campaign): ?>
                                <option value="<?= e($campaign->id) ?>" <?= e(($campaign->id == $pledge->pledge_campaign_id) ? 'selected' : '') ?> ><?= e($campaign->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Pledge Amount</label>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-dollar"></i></div>
                            <input type="tel" class="form-control text-right" name="total_amount" value="<?= e(number_format($pledge->total_amount,2)) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info">Save</button>
                <button type="button" class="btn btn-default" <?php if(!$pledge->exists): ?>data-dismiss="modal"<?php else: ?>onclick="$('#general-view, #edit-view').toggleClass('hide');"<?php endif; ?>>Cancel</button>
            </div>
        </form>
    </div>

</div>
