<div class="modal fade modal-danger" id="refund-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-reply"></i> Refund</h4>
            </div>
            <form method="post" action="{{ route('backend.orders.refund', $order) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fa fa-fw fa-exclamation-triangle"></i> <strong>This is a permanent action and cannot be undone.</strong> You can only issue a refund once.
                    </div>

                    <div class="form-group">
                        <div class="radio">
                            <label>
                                <input type="radio" name="refund_type" value="full" checked> <strong>Full Refund</strong> ({{ money($order->totalamount - $order->refunded_amt, $order->currency) }})
                                @if(dpo_is_enabled() && sys_get('dp_push_order_refunds'))
                                    <br><small>This will also adjust gifts in DonorPerfect.</small>
                                @endif
                            </label>
                        </div>
                    </div>

                    @if (!$order->paymentProvider || ($order->paymentProvider && $order->paymentProvider->supports('partial_refunds')))
                    <div class="form-group">
                        <div class="radio">
                            <label>
                                <input type="radio" name="refund_type" value="custom" {{ feature('givecloud_pro') ? '' : 'disabled' }}> <strong class="{{ feature('givecloud_pro') ? '' : 'text-muted' }}">Partial refund</strong>
                                @if (feature('givecloud_pro'))
                                    (custom amount)
                                @else
                                    <a class="upgrade-pill" href="https://calendly.com/givecloud-sales/givecloud-upgrade-call?month={{ now()->format('Y-d') }}">UPGRADE</a>
                                @endif
                                @if(dpo_is_enabled() && sys_get('dp_push_order_refunds'))
                                    <br><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> No adjustments will be made in DonorPerfect.</small>
                                @endif
                            </label>
                        </div>

                        <div id="custom-refund-amount" class="form-group" style="display:none; margin-left:20px; width:140px;">
                            <div class="input-group">
                                <div class="input-group-addon">{{ $order->currency->symbol }}</div>
                                <input type="tel" class="form-control text-right" name="amount" placeholder="0.00" @if ($order->paymentProvider && !$order->paymentProvider->supports('partial_refunds')) {{ "readonly" }} @endif>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Refund Now</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
