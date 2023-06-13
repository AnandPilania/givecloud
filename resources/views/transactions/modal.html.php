

<?php if ($txn): ?>

    <div class="modal-body">

        <?= dangerouslyUseHTML(app('flash')->output()) ?>

        <?php if (!$txn->is_payment_accepted): ?>

            <div class="alert alert-danger text-center">
                <i class="fa fa-exclamation-triangle"></i> This transaction failed.
            </div>

        <?php endif ?>

        <?php if ($txn->is_refunded): ?>
            <div class="alert alert-danger text-center">
                <i class="fa fa-exclamation-triangle"></i> Refunded on <?= e($txn->refunded_at) ?> by <?= e($txn->refundedBy->full_name) ?>.
            </div>
        <?php endif; ?>

        <?php if (isGivecloudPro()): ?>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#txn-tab-detail" aria-controls="txn-tab-detail" role="tab" data-toggle="tab"><i class="fa fa-search"></i> Details</a></li>
            <li role="presentation"><a href="#txn-tab-log" aria-controls="txn-tab-log" role="tab" data-toggle="tab"><i class="fa fa-file-code-o"></i> Processing Log</a></li>
        </ul>
        <?php endif; ?>

        <div class="tab-content top-gutter">
            <div role="tabpanel" class="tab-pane active in fade" id="txn-tab-detail">

                <div class="row">

                    <div class="col-sm-6">

                        <div class="row bottom-gutter-xs">
                            <div class="col-xs-4"><strong>Account:</strong></div>
                            <div class="col-xs-8"><a href="<?= e(route('backend.member.edit', $txn->recurringPaymentProfile->member->id)) ?>"><i class="fa fa-user"></i> <?= e($txn->recurringPaymentProfile->member->display_name) ?></a></div>
                        </div>

                        <div class="row bottom-gutter-xs">
                            <div class="col-xs-4"><strong>Profile:</strong></div>
                            <div class="col-xs-8"><a href="/jpanel/recurring_payments/<?= e($txn->recurringPaymentProfile->profile_id) ?>" target="_blank"><?= e($txn->recurringPaymentProfile->profile_id) ?></a></div>
                        </div>

                        <div class="row bottom-gutter-xs">
                            <div class="col-xs-4"><strong>Transaction:</strong></div>
                            <div class="col-xs-8"><?= e(($txn->transaction_id) ? $txn->transaction_id : 'n/a') ?></div>
                        </div>

                        <div class="row bottom-gutter-xs text-extra-bold">
                            <div class="col-xs-4"><strong>Amount:</strong></div>
                            <div class="col-xs-8"><?= e(money($txn->amt,$txn->currency_code)->format('$0,000.00 [$$$]')) ?></div>
                        </div>

                        <?php if ($txn->is_refunded): ?>
                            <div class="row bottom-gutter-xs text-extra-bold text-danger">
                                <div class="col-xs-4"><strong>Refunded:</strong></div>
                                <div class="col-xs-8"><?= e(money($txn->refunded_amt,$txn->currency_code)->format('$0,000.00 [$$$]')) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="row bottom-gutter-xs">
                            <div class="col-xs-4"><strong>Response:</strong></div>
                            <div class="col-xs-8">
                                <?php if($txn->is_payment_accepted): ?><?= e($txn->reason_code) ?><?php else: ?><span class="text-danger"><i class="fa fa-exclamation-triangle"></i> <?= e($txn->reason_code) ?></span><?php endif; ?>
                            </div>
                        </div>

                        <?php if (dpo_is_enabled()): ?>
                            <?php if($txn->is_payment_accepted || $txn->dpo_gift_id): ?>

                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Gift ID:</strong></div>
                                    <div class="col-xs-8">
                                        <?= dangerouslyUseHTML(($txn->dpo_gift_id) ? '<i class="fa fa-gift"></i> '.e($txn->dpo_gift_id) : '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> No gift.</span>') ?>
                                        <?php if(user()->can('admin.dpo')): ?>
                                            &nbsp;&nbsp;<a class="btn btn-xs btn-success btn-outline txn-resync" onclick=""><i class="fa fa-refresh"></i> Resync</a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php else: ?>

                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Gift ID:</strong></div>
                                    <div class="col-xs-8">
                                        <span class="text-info"><i class="fa fa-info-circle"></i> No gift required.</span>
                                    </div>
                                </div>

                            <?php endif; ?>
                        <?php endif; ?>

                    </div>

                    <div class="col-sm-6">

                        <div class="row bottom-gutter-xs">
                            <div class="col-xs-4"><strong>Time:</strong></div>
                            <div class="col-xs-8">
                                <?= e(toLocalFormat($txn->order_time, 'M j, Y \a\t g:iA')) ?>
                                <br><small class="text-muted"><?= e(toLocal($txn->order_time)->diffForHumans()) ?></small>
                            </div>
                        </div>

                        <div class="row bottom-gutter-xs">
                            <div class="col-xs-4"><strong>Method:</strong></div>
                            <div class="col-xs-8">
                                <?php if(in_array($txn->payment_method_type,['eft','cash','check','other'])): ?>
                                    <?= e($txn->payment_description) ?>
                                <?php elseif ($txn->paymentMethod): ?>
                                    <?= e($txn->paymentMethod->display_name) ?><br />
                                    **** **** **** <?= e($txn->paymentMethod->account_last_four) ?><br />
                                    <?php if($txn->paymentMethod->is_expired): ?>
                                        <span class="text-danger"><i class="fa fa-times"></i> This card has expired.</span>
                                    <?php else: ?>
                                        <span class="text-success"><i class="fa fa-check"></i> Valid Card</span>
                                    <?php endif; ?>
                                <?php else:?>
                                    <span class="text-muted">(None)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if($txn->paymentMethod->paymentProvider ?? false): ?>
                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Gateway:</strong></div>
                                <div class="col-xs-8">
                                    <?= e($txn->paymentMethod->paymentProvider->display_name) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(sys_get('tax_receipt_pdfs') == 1 && user()->can('taxreceipt.view') && $txn->is_payment_accepted): ?>
                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4 whitespace-nowrap"><strong>Tax Receipt:</strong></div>
                                <div class="col-xs-8">
                                    <?php if($txn->taxReceipt): ?>
                                        <a class="btn btn-info btn-xs btn-outline" target="_blank" href="/jpanel/tax_receipt/<?= e($txn->taxReceipt->id) ?>/pdf"><i class="fa fa-file"></i> <?= e($txn->taxReceipt->number) ?></a>
                                    <?php elseif($txn->recurringPaymentProfile->is_tax_receiptable): ?>
                                        <span class="text-warning"><i class="fa fa-exclamation-triangle"></i> No receipt issued.</span>
                                    <?php else: ?>
                                        <span class="text-muted">This transaction is not receiptable.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <div role="tabpanel" class="tab-pane fade" id="txn-tab-log">
                <textarea id="txn-log" class="form-control" style="font-family:monospace; height:200px; font-size:11px"><?= e($txn->transaction_log) ?></textarea>
                <small class="text-muted">All timestamps are in UTC time.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer">

        <?php if ($txn->is_refundable && user()->can('transaction.refund')): ?>
            <button type="button" class="btn btn-danger btn-outline pull-left txn-refund" style="margin-right:7px;"><i class="fa fa-reply"></i> Refund</button>
        <?php endif; ?>

        <?php if(!$txn->is_refunded && user()->can('taxreceipt.add') && !$txn->taxReceipt && $txn->recurringPaymentProfile->is_tax_receiptable): ?>
            <button type="button" class="btn btn-info btn-outline pull-left txn-issue-tax-receipt" style="margin-right:7px;"><i class="fa fa-send"></i> Issue Tax Receipt</a></button>
        <?php endif; ?>

        <?php if (user()->can('transaction.refund')): ?>
            <button type="button" class="btn btn-default pull-left txn-refresh" style="margin-right:7px;" title="Refresh Payment Status"><i class="fa fa-refresh fa-fw"></i></button>
        <?php endif; ?>

        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>

<?php endif; ?>
