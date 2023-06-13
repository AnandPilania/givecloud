
<?php if ($tribute): ?>

    <div class="tribute-details">
        <div class="modal-body">

            <?= dangerouslyUseHTML(app('flash')->output()) ?>

            <?php if($tribute->trashed()): ?>
                <div class="alert alert-danger text-center">
                    <i class="fa fa-times"></i> This tribute was deleted on <?= e(toLocalFormat($tribute->deleted_at, 'M, d, Y')) ?> <small>(<?= e(toLocal($tribute->deleted_at)->diffForHumans()) ?>)</small>.
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#receipt-tab-detail" aria-controls="receipt-tab-detail" role="tab" data-toggle="tab">Details</a></li>
                <li role="presentation"><a href="#receipt-tab-revisions" aria-controls="receipt-tab-revisions" role="tab" data-toggle="tab">Custom Message</a></li>
            </ul>

            <div class="tab-content top-gutter">
                <div role="tabpanel" class="tab-pane active in fade" id="receipt-tab-detail">

                    <div class="row">

                        <div class="col-sm-6">

                            <?php if($tribute->orderItem): ?>
                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Order:</strong></div>
                                    <div class="col-xs-8">
                                        <a href="<?= e(route('backend.orders.edit', $tribute->orderItem->order)) ?>" target="_blank"><?= e($tribute->orderItem->order->invoicenumber) ?></a><br>
                                        <small class="text-muted"><?= e($tribute->orderItem->order->billing_first_name . ' ' . $tribute->orderItem->order->billing_last_name) ?></small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row bottom-gutter-xs text-extra-bold">
                                <div class="col-xs-4">Name:</div>
                                <div class="col-xs-8"><?= e($tribute->name) ?></div>
                            </div>

                            <div class="row bottom-gutter-xs text-extra-bold">
                                <div class="col-xs-4">Amount:</div>
                                <div class="col-xs-8"><?= e(money($tribute->amount, $tribute->orderItem->order->currency_code)) ?></div>
                            </div>

                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Created:</strong></div>
                                <div class="col-xs-8"><?= e(toLocalFormat($tribute->created_at)) ?> <small class="text-muted"><?= e(toLocal($tribute->created_at)->diffForHumans()) ?></small></div>
                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="row bottom-gutter-xs">
                                <div class="col-xs-4"><strong>Type:</strong></div>
                                <div class="col-xs-8"><?= e($tribute->tributeType->label) ?></div>
                            </div>

                            <?php if($tribute->notify == ''): ?>

                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Send Via:</strong></div>
                                    <div class="col-xs-8 text-danger">
                                        <i class="fa fa-exclamation-triangle"></i> No notification.
                                    </div>
                                </div>

                            <?php else: ?>

                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Send Via:</strong></div>
                                    <div class="col-xs-8">
                                        <?= e(ucwords($tribute->notify)) ?>
                                    </div>
                                </div>

                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Send To:</strong></div>
                                    <div class="col-xs-8">
                                        <?= dangerouslyUseHTML(($tribute->notify_name) ? e($tribute->notify_name) . '<br>' : '') ?>
                                        <?php if($tribute->notify == 'email'): ?>
                                            <a href="mailto:<?= e($tribute->notify_email) ?>"><?= e($tribute->notify_email) ?></a>
                                        <?php elseif($tribute->notify == 'letter'): ?>
                                            <?= dangerouslyUseHTML(($tribute->notify_full_address) ? $tribute->notify_full_address . '<br>' : '') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row bottom-gutter-xs">
                                    <div class="col-xs-4"><strong>Sent On:</strong></div>
                                    <div class="col-xs-8">
                                        <?php if($tribute->notified_at): ?>
                                            <span class="text-success"><i class="fa fa-check"></i> Sent on <?= e(toLocalFormat($tribute->notified_at, 'M j, Y')) ?></span>
                                        <?php elseif($tribute->notify == 'letter'): ?>
                                            <span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Undelivered</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <div role="tabpanel" class="tab-pane fade" id="receipt-tab-revisions">

                    <?php if($tribute->message): ?>
                        <?= e(str_replace(chr(10), '<br>', $tribute->message)) ?>
                    <?php else: ?>
                        <div class="text-muted">No custom message.</div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="modal-footer">
            <?php if(!$tribute->trashed() && $tribute->userCan('edit')): ?>
                <a class="btn btn-default btn-outline pull-left tribute-edit-btn"><i class="fa fa-pencil"></i> Edit</a>
                <?php if($tribute->notify == 'email'): ?><a class="btn btn-default btn-outline pull-left tribute-notify-btn"><i class="fa fa-envelope-o"></i> Re-Notify</a><?php endif; ?>
                <?php if($tribute->notify == 'letter' && !$tribute->notified_at): ?><a class="btn btn-default btn-outline pull-left tribute-notify-btn"><i class="fa fa-envelope-o"></i> Mark as Notified</a><?php endif; ?>
                <a class="btn btn-danger btn-outline pull-left tribute-destroy-btn"><i class="fa fa-trash"></i></a>
            <?php endif; ?>

            <a href="/jpanel/tributes/<?= e($tribute->id) ?>/pdf" target="_blank" class="btn btn-info"><i class="fa fa-file-pdf-o"></i> View Letter</a>
        </div>
    </div>

    <?php if($tribute->userCan('edit')): ?>
        <div class="tribute-edit hide">
            <div class="modal-body">

                <div class="row">

                    <form class="tribute-edit-form">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Tribute To:</label>
                                <input type="text" class="form-control" name="name" value="<?= e($tribute->name) ?>" placeholder="First Name">
                                <small class="text-muted">Name of the individual this donation is dedicated to</small>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Notification Type</label>
                                <div class="form-control-static">
                                    <label class="radio-inline">
                                        <input type="radio" class="notify-type" name="notify" <?= e(($tribute->notify == 'email') ? 'checked' : '') ?> value="email"> Email
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" class="notify-type" name="notify" <?= e(($tribute->notify == 'letter') ? 'checked' : '') ?> value="letter"> Letter
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" class="notify-type" name="notify" <?= e(($tribute->notify == null) ? 'checked' : '') ?> value=""> None
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Notify Name</label>
                                <input type="text" class="form-control" name="notify_name" value="<?= e($tribute->notify_name) ?>" placeholder="Name">
                                <small class="text-muted">Name of the individual being notified</small>
                            </div>
                        </div>

                        <div class="tribute-edit-form-email">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Notify Email</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                        <input type="text" class="form-control" name="notify_email" value="<?= e($tribute->notify_email) ?>" placeholder="Email">
                                    </div>
                                    <small class="text-muted">Email of the individual being notified</small>
                                </div>
                            </div>
                        </div>

                        <div class="tribute-edit-form-letter">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Notify Address</label>
                                    <input type="text" class="form-control" name="notify_address" value="<?= e($tribute->notify_address) ?>" placeholder="Address">
                                </div>
                            </div>

                            <div class="col-sm-8">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="notify_city" value="<?= e($tribute->notify_city) ?>" placeholder="City">
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="notify_state" value="<?= e($tribute->notify_state) ?>" placeholder="State/Prov">
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="notify_zip" value="<?= e($tribute->notify_zip) ?>" placeholder="ZIP/Postal">
                                </div>
                            </div>

                            <div class="col-sm-8">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="notify_country" value="<?= e($tribute->notify_country) ?>" placeholder="Country">
                                </div>
                            </div>
                        </div>

                        <div class="tribute-edit-form-letter tribute-edit-form-email">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Custom Message</label>
                                    <textarea class="form-control" name="message" value="<?= e($tribute->message) ?>" rows="6" placeholder="Optional custom message..."><?= e($tribute->message) ?></textarea>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success tribute-save-btn"><i class="fa fa-check"></i> Save</button>
                <button type="button" class="btn btn-default tribute-cancel-btn"><i class="fa fa-times"></i> Cancel</button>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>

    <p class="text-muted text-center"><br><br><i class="fa fa-exclamation-triangle fa-4x"></i><h1 class="text-center">No Tribute Found</h1></p>

<?php endif; ?>
