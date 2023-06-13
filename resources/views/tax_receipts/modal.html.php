
<?php if ($receipt): ?>

    <div class="tax-receipt-details">
        <div class="modal-body">

            <?= dangerouslyUseHTML(app('flash')->output()) ?>

            <?php if($receipt->status === 'void'): ?>
                <div class="alert alert-danger text-center">
                    <i class="fa fa-times"></i> This receipt was voided on <?= e(toLocalFormat($receipt->voided_at, 'M, d, Y')) ?> <small>(<?= e(toLocal($receipt->voided_at)->diffForHumans()) ?>)</small>.
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#receipt-tab-detail" aria-controls="receipt-tab-detail" role="tab" data-toggle="tab">Details</a></li>

                <?php if ($receipt->status !== 'draft'): ?>
                    <li role="presentation"><a href="#receipt-tab-revisions" aria-controls="receipt-tab-revisions" role="tab" data-toggle="tab">Revisions <span class="badge"><?= e(count($receipt->changes ?? [])) ?></span></a></li>
                <?php endif; ?>
            </ul>

            <div class="tab-content top-gutter">
                <div role="tabpanel" class="tab-pane active in fade" id="receipt-tab-detail">

                    <div class="row">

                        <div class="col-sm-6">

                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Number:</strong></div>
                                <div class="col-xs-8"><?= e($receipt->number) ?></div>
                            </div>

                            <div class="row bottom-gutter-xs text-extra-bold">
                                <div class="col-xs-4">Amount:</div>
                                <div class="col-xs-8"><?= e(money($receipt->amount, $receipt->currency_code)) ?></div>
                            </div>

                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Date:</strong></div>
                                <div class="col-xs-8"><?= e(toLocalFormat($receipt->issued_at)) ?> <small class="text-muted"><?= e(toLocalFormat($receipt->getAttributeValue('issued_at'), 'humans')) ?></small></div>
                            </div>

                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Source(s):</strong></div>
                                <div class="col-xs-8">
                                <?php foreach ($receipt->lineItems as $lineItem): ?>
                                    <?php if ($lineItem->order_id): ?>
                                        <i class="fa fa-shopping-cart"></i> <?= dangerouslyUseHTML($lineItem->html_description) ?><br>
                                    <?php else: ?>
                                        <i class="fa fa-credit-card-alt"></i> <?= dangerouslyUseHTML($lineItem->html_description) ?><br>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Account:</strong></div>
                                <div class="col-xs-8"><a href="<?= e(route('backend.member.edit', $receipt->account_id)) ?>" target="_blank"><?= e($receipt->account->display_name) ?: '(blank)' ?></a></div>
                            </div>

                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Issued To:</strong></div>
                                <div class="col-xs-8">
                                    <?= dangerouslyUseHTML(($receipt->name) ? e($receipt->name) . '<br>' : '') ?>
                                    <?= dangerouslyUseHTML(($receipt->full_address) ? $receipt->full_address . '<br>' : '') ?>
                                    <?= dangerouslyUseHTML(($receipt->email) ? '<i class="fa fa-envelope fa-fw"></i> '.$receipt->email . '<br>' : '') ?>
                                    <?= dangerouslyUseHTML(($receipt->phone) ? '<i class="fa fa-phone fa-fw"></i> '.phone_format($receipt->phone) . '<br>' : '') ?>
                                </div>
                            </div>

                        </div>

                    </div>


                </div>
                <div role="tabpanel" class="tab-pane fade" id="receipt-tab-revisions">

                    <?php if($receipt->changes): ?>

                        <?= e(str_replace(chr(10),'<br>',implode('<hr>',$receipt->changes_formatted))) ?>

                    <?php else: ?>
                        <div class="text-muted">No revisions.</div>
                    <?php endif; ?>

                </div>
            </div>


        </div>

        <div class="modal-footer">
            <?php if ($receipt->status === 'draft' && $receipt->userCan('edit')): ?>
                <a class="btn btn-success btn-outline pull-left tax-receipt-issue-btn"><i class="fa fa-university"></i> Issue</a>
                <a class="btn btn-danger btn-outline pull-left tax-receipt-void-btn"><i class="fa fa-times"></i> Delete</a>
                <a class="btn btn-default btn-outline pull-left tax-receipt-revise-btn"><i class="fa fa-pencil"></i> Edit</a>
            <?php elseif ($receipt->status === 'issued' && $receipt->userCan('edit')): ?>
                <a class="btn btn-default btn-outline pull-left tax-receipt-revise-btn"><i class="fa fa-pencil"></i> Revise</a>
                <a class="btn btn-default btn-outline pull-left tax-receipt-notify-btn"><i class="fa fa-envelope-o"></i> Notify</a>
                <a class="btn btn-danger btn-outline pull-left tax-receipt-void-btn"><i class="fa fa-times"></i> Void</a>
            <?php endif; ?>

            <a href="/jpanel/tax_receipt/<?= e($receipt->id) ?>/pdf" target="_blank" class="btn btn-info">
                <i class="fa fa-file-pdf-o"></i> <?= e(($receipt->status === 'draft') ? 'Preview' : 'View') ?> Receipt
            </a>
        </div>
    </div>

    <?php if($receipt->userCan('edit')): ?>
        <div class="tax-receipt-revise hide">
            <div class="modal-body">

                <?php if ($receipt->status === 'issued'): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-exclamation-circle"></i> Every change to this tax receipt is tracked for reporting and auditing.
                    </div>
                <?php endif; ?>

                <div class="row">

                    <form class="tax-receipt-revise-form">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Date</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                                    <input type="text" class="form-control date-pretty" name="issued_at" value="<?= e(toLocalFormat($receipt->issued_at, 'M j, Y')) ?>" placeholder="Issued Date">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-dollar"></i></div>
                                    <input type="text" class="form-control text-right" name="amount" value="<?= e(number_format($receipt->amount,2)) ?>" placeholder="Amount">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" name="name" value="<?= e($receipt->name) ?>" placeholder="Name">
                            </div>
                            <hr style="margin: 30px -15px 25px">
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name" value="<?= e($receipt->first_name) ?>" placeholder="First Name">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="<?= e($receipt->last_name) ?>" placeholder="Last Name">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address_01" value="<?= e($receipt->address_01) ?>" placeholder="Address Line 1">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="text" class="form-control" name="address_02" value="<?= e($receipt->address_02) ?>" placeholder="Address Line 2">
                            </div>
                        </div>

                        <div class="col-sm-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="city" value="<?= e($receipt->city) ?>" placeholder="City">
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="state" value="<?= e($receipt->state) ?>" placeholder="State/Prov">
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="zip" value="<?= e($receipt->zip) ?>" placeholder="ZIP/Postal">
                            </div>
                        </div>

                        <div class="col-sm-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="country" value="<?= e($receipt->country) ?>" placeholder="Country">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    <input type="text" class="form-control" name="email" value="<?= e($receipt->email) ?>" placeholder="Email">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    <input type="text" class="form-control" name="phone" value="<?= e($receipt->phone) ?>" placeholder="Email">
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success tax-receipt-save-btn">
                    <i class="fa fa-check"></i> <?= e(($receipt->status === 'draft') ? 'Save as Draft' : 'Revise') ?>
                </button>
                <button type="button" class="btn btn-default tax-receipt-cancel-btn"><i class="fa fa-times"></i> Cancel</button>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>

    <p class="text-muted text-center"><br><br><i class="fa fa-exclamation-triangle fa-4x"></i><h1 class="text-center">No Tax Receipt Found</h1></p>

<?php endif; ?>
