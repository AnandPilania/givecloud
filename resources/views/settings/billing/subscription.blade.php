<div class="panel panel-default">
    <div class="panel-body">

        <div class="row">
            <div class="col-sm-6 col-md-4 bottom-gutter">
                <div class="panel-sub-title"><i class="fa fa-credit-card"></i> Billing</div>
                <div class="panel-sub-desc">
                    Manage your subscription and payment method.
                </div>
            </div>

            <div class="col-sm-6 col-md-8">

                <div class="form-group">
                    <label for="meta1" class="col-md-4 control-label">Subscription</label>
                    <div class="col-md-8">

                        <!-- are we billing SUBSCRIPTION FEES directly? -->
                        @if (site('direct_billing_enabled'))
                            <div class="form-control-static">
                                @if ($cb_plan)
                                    {{ $cb_plan->param('name') }} at ${{ number_format($cb_subscription->param('plan_amount') / 100, 2)}} / {{ $cb_subscription->param('billing_period_unit') }}
                                    <br><span class="text-muted">To change your plan, please <a href="javascript:Intercom('showNewMessage','I would like to change my billing plan');">contact support</a>.</span>
                                    @if ($cb_plan->description)
                                        <br><small class="text-muted">{{ $cb_plan->description }}</small>
                                    @endif
                                @endif
                            </div>

                            <!-- if not, is there a partner? -->
                        @elseif (site()->partner)
                            <div class="form-control-static">Managed through {{ site()->partner->name }}</div>

                            <!-- if not, pick a plan! (this should never happen) -->
                        @else
                            <div class="form-control-static"><a href="#plans" class="text-bold">Choose a Plan</a></div>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label for="meta1" class="col-md-4 control-label">Payment Method</label>
                    <div class="col-md-8">
                        @if ($cb_card)
                            <div class="form-control-static"><i class="fa fa-fw {{ fa_payment_icon($cb_card->brand) }}"></i> {{ $cb_card->masked }} &nbsp;&nbsp;<a href="javascript:j.openCustomerPortal('PAYMENT_SOURCES');" class="btn btn-xs btn-outline btn-info"><i class="fa fa-pencil"></i> Change</a></div>
                        @else
                            <div class="form-control-static"><strong class="text-danger"><i class="fa fa-exclamation-triangle"></i> None on file</strong> &nbsp;&nbsp;<a href="javascript:j.openCustomerPortal('ADD_PAYMENT_SOURCE');" class="btn btn-xs btn-outline btn-info"><i class="fa fa-credit-card"></i> Add a Payment Method</a></div>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label for="meta1" class="col-md-4 control-label">Givecloud Platform Fee</label>
                    <div class="col-md-8">
                        <div class="form-control-static">
                            @if (site()->txn_fee < 0.02)
                                <span class="text-line-thru text-muted">2%</span> <strong>{{ number_format(site()->txn_fee * 100, 2)}}%</strong>
                            @else
                                {{ number_format(site()->txn_fee * 100, 2)}}%
                            @endif
                            &nbsp;&nbsp;<a href="/jpanel/reports/platform-fees" class="btn btn-xs btn-outline btn-info"><i class="fa fa-bar-chart"></i> View Report</a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="meta1" class="col-md-4 control-label">Billing History</label>
                    <div class="col-md-8">
                        <div class="form-control-static"><a href="javascript:j.openCustomerPortal('BILLING_HISTORY');" class="btn btn-xs btn-outline btn-info"><i class="fa fa-table"></i> View History</a></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
