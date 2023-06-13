@php
    use Ds\Enums\RecurringPaymentProfileStatus;
@endphp

@extends('layouts.app')
@section('title', 'Recurring Payment')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            <span class="page-header-text">Recurring Payment</span>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                @if (user()->can('recurringpaymentprofile.edit'))
                    @if ($recurring_payment_profile->status == RecurringPaymentProfileStatus::ACTIVE)
                        {{--% if recurring_payment_profile.transaction_type == 'Donation' %--}}
                            <a href="/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/edit" class="btn btn-info" title="Modify"><i class="fa fa-pencil"></i><span class="hidden-xs hidden-sm"> Modify</span></a>
                        {{--% endif %--}}
                        <a href="javascript:void(0);" onclick="$.confirm('Are you sure you want to suspend this recurring payment? @if($recurring_payment_profile->sponsorship and sys_get('sponsorship_end_on_rpp_suspend')) <span class=\'text-danger\'><br><br><strong><i class=\'fa fa-exclamation-triangle\'></i> Warning</strong> This will also end <strong>{{ $recurring_payment_profile->member->display_name }}\'s</strong> sponsorship of <strong>{{ $recurring_payment_profile->sponsorship->display_name }}</strong>.</span>@endif', function(){ window.location = '/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/suspend'; }, 'warning', 'fa-exclamation-circle');" class="btn btn-warning" title="Suspend"><i class="fa fa-fw fa-exclamation-circle"></i><span class="hidden-xs hidden-sm"> Suspend</span></a>

                        <a href="#cancel-rpp-modal" data-toggle="modal" class="btn btn-danger" title="Cancel"><i class="fa fa-fw fa-times"></i><span class="hidden-xs hidden-sm"> Cancel</span></a>
                    @endif

                    @if ($recurring_payment_profile->status == RecurringPaymentProfileStatus::SUSPENDED)
                        <a href="javascript:void(0);" onclick="$.confirm('Are you sure you want to enable this recurring payment? @if($recurring_payment_profile->sponsorship and $recurring_payment_profile->sponsor->ended_at) <span class=\'text-danger\'><br><br><strong><i class=\'fa fa-exclamation-triangle\'></i> Warning</strong> This will also restart <strong>{{ $recurring_payment_profile->member->display_name }}\'s</strong> sponsorship of <strong>{{ $recurring_payment_profile->sponsorship->display_name }}</strong>.</span>@endif', function(){ window.location = '/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/enable'; }, 'success', 'fa-check');" class="btn btn-success" title="Enable"><i class="fa fa-fw fa-check"></i><span class="hidden-xs hidden-sm"> Enable</span></a>

                        <a href="#cancel-rpp-modal" data-toggle="modal" class="btn btn-danger" title="Cancel"><i class="fa fa-fw fa-times"></i><span class="hidden-xs hidden-sm"> Cancel</span></a>
                    @endif
                @endif

                @if ($recurring_payment_profile->status == RecurringPaymentProfileStatus::ACTIVE and (user()->can('recurringpaymentprofile.charge') or (user()->can('recurringpaymentprofile.edit') and dpo_is_enabled())))
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-gear"></i> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            @if (user()->can('recurringpaymentprofile.charge'))
                                <li><a href="#" data-toggle="modal" data-target="#charge-now"><i class="fa fa-credit-card-alt fa-fw"></i> Charge Now</a></li>
                            @endif
                            @if (user()->can('recurringpaymentprofile.edit') and dpo_is_enabled())
                                <li><a href="#" data-toggle="modal" data-target="#dp-pledge"><i class="fa fa-money fa-fw"></i> Override DP Pledge</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

            <p class="text-secondary">
                <a href="{{ route('backend.member.edit', $recurring_payment_profile->member_id) }}"><i class="fa {{ $recurring_payment_profile->member->fa_icon }}"></i> {{ $recurring_payment_profile->member->display_name }}</a> |
                {{ $recurring_payment_profile->payment_string }} since {{ toLocalFormat($recurring_payment_profile->profile_start_date, 'M j, Y') }} |
                Profile ID No. {{ $recurring_payment_profile->profile_id }}
            </p>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

<div class="toastify hide">
    {{ $flash->output() }}
</div>

@set('pay_method_err', ($recurring_payment_profile->payment_method_id and ($recurring_payment_profile->paymentMethod->is_expired or $recurring_payment_profile->paymentMethod->deleted_at)))

@if ($recurring_payment_profile->status != RecurringPaymentProfileStatus::CANCELLED and $pay_method_err)
    <div class="alert alert-danger">
        <strong><i class="fa fa-exclamation-triangle"></i> Payment method error</strong> - There is a problem with the payment method linked to this recurring payment.
    </div>
@endif

@if ($recurring_payment_profile->status == RecurringPaymentProfileStatus::CANCELLED)
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i> {{ RecurringPaymentProfileStatus::CANCELLED }} {{ $recurring_payment_profile->final_payment_due_date }} @if($recurring_payment_profile->cancel_reason) because "{{ $recurring_payment_profile->cancel_reason }}" @else with no reason supplied. @endif
        <a href="#update-cancel-reason-rpp-modal" data-toggle="modal" class="pl-2" title="Cancel">Change</a>
    </div>
@endif

<div id="recurringPaymentDetails">

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#details" aria-controls="details" role="tab" data-toggle="tab"><i class="fa fa-search"></i> Details</a></li>
        <li role="presentation"><a href="#transactions" aria-controls="transactions" role="tab" data-toggle="tab"><i class="fa fa-money"></i> Transactions <span class="badge">{{ count($recurring_payment_profile->transactions) }}</span></a></li>
    </ul>

    <br />

    <!-- Tab panes -->
    <div class="tab-content">

        <div role="tabpanel" class="tab-pane active" id="details">

            <div class="row">

                <div class="col-sm-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">Payment Details</div>
                        <div class="panel-body">
                            <div class="row">

                                @if ($recurring_payment_profile->status == RecurringPaymentProfileStatus::ACTIVE)
                                    <div class="col-sm-6 stat">
                                        <div class="stat-value-bold"><i class="fa fa-calendar"></i> {{ toLocalFormat($recurring_payment_profile->next_billing_date, 'M jS') }}</div>
                                        <div class="stat-label">Next billing date ({{ toLocalFormat($recurring_payment_profile->next_billing_date, 'Y') }})</div>
                                    </div>
                                @elseif ($recurring_payment_profile->status == RecurringPaymentProfileStatus::SUSPENDED)
                                    <div class="col-sm-6 stat text-warning">
                                        <div class="stat-value-bold"><i class="fa fa-exclamation-circle"></i> {{ $recurring_payment_profile->status }}</div>
                                        <div class="stat-label">Current Status</diV>
                                    </div>
                                @elseif ($recurring_payment_profile->status == RecurringPaymentProfileStatus::EXPIRED)
                                    <div class="col-sm-6 stat">
                                        <div class="stat-value-bold">{{ $recurring_payment_profile->status }}</div>
                                        <div class="stat-label">Current Status</diV>
                                    </div>
                                @else
                                    <div class="col-sm-6 stat text-danger">
                                        <div class="stat-value-bold"><i class="fa fa-exclamation-triangle"></i> {{ $recurring_payment_profile->status }}</div>
                                        <div class="stat-label">Current Status</diV>
                                    </div>
                                @endif

                                <div class="col-sm-6 stat">
                                    <div class="stat-value-bold">
                                        {{ money($recurring_payment_profile->total_amt, $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]') }}
                                    </div>
                                    <div class="stat-label">
                                        Recurring amount
                                        @if ($recurring_payment_profile->dcc_enabled_by_customer && $recurring_payment_profile->dcc_amount > 0)
                                            <span class="text-muted">( Includes {{ money($recurring_payment_profile->dcc_amount, $recurring_payment_profile->currency_code) }} for {{ sys_get('dcc_label') }} )</span>
                                        @endif
                                    </diV>
                                </div>

                                <div class="col-sm-6 stat">
                                    <div class="stat-value">{{ $recurring_payment_profile->billing_period_description }}</div>
                                    <div class="stat-label">Frequency ({{ $recurring_payment_profile->billing_period_day }})</diV>
                                </div>

                                @if ($recurring_payment_profile->is_manual)
                                    <div class="col-sm-6 stat">
                                        @if ($recurring_payment_profile->is_locked)
                                            <div class="stat-value">Legacy Importer</div>
                                            <div class="stat-label">
                                                {{ $recurring_payment_profile->paypal_subscription_id ?? $recurring_payment_profile->stripe_subscription_id }}
                                            </diV>
                                        @else
                                            <div class="stat-value">Manual</div>
                                            <div class="stat-label">Processing</diV>
                                        @endif
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">Lifetime Stats</div>
                        <div class="panel-body">
                            <div class="row">

                                <div class="col-sm-12 stat">
                                    <div class="stat-value">{{ money($recurring_payment_profile->aggregate_amount, $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]') }}</div>
                                    <div class="stat-label">Lifetime amount</diV>
                                </div>

                                @set('failed_count', $recurring_payment_profile->transactions()->failed()->count())
                                <div class="col-sm-12 stat @iftrue($failed_count > 0, 'text-danger')">
                                    <div class="@iftrue($failed_count > 0, 'stat-value-bold', 'stat-value')">{{ numeral($failed_count)->format('0,0') }}</div>
                                    <div class="stat-label">Failed payments</diV>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">

                    <div class="panel panel-default">
                        <div class="panel-heading">Billing Details</div>
                        <div class="panel-body">
                            <div class="row">

                                <div class="form-group col-sm-12">
                                    <label>Item name</label>
                                    <div class="form-control">
                                        {{ $recurring_payment_profile->description }}
                                        @isset($recurring_payment_profile->order_item->gl_code)
                                            (GL: {{ $recurring_payment_profile->order_item->gl_code }})
                                        @endisset
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label>Contribution Number</label>
                                    <div class="input-group">
                                        <div class="form-control">{{ $recurring_payment_profile->profile_reference }}</div>
                                        <div class="input-group-btn">
                                            <a href="{{ route('backend.orders.edit', $recurring_payment_profile->productorder_id) }}" class="btn btn-default">
                                                <i class="fa fa-search"></i> View Contribution</a>
                                        </div>
                                    </div>
                                </div>

                                @if (feature('givecloud_pro'))
                                <div class="form-group col-sm-12">
                                    <label>Last payment due</label>
                                    <div class="input-group">
                                        <div class="form-control">
                                        @if ($recurring_payment_profile->final_billing_date)
                                            {{ toLocalFormat($recurring_payment_profile->final_billing_date, 'M j, Y') }}
                                        @else
                                            Indefinite - Continue until canceled
                                        @endif
                                        </div>
                                        <div class="input-group-btn"><a href="/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/edit" class="btn btn-info"><i class="fa fa-pencil"></i></a></div>
                                    </div>
                                </div>
                                @endif

                                {{--
                                <div class="form-group col-sm-12">
                                    <label>Add payments that failed to next bill</label>
                                    <div class="form-control">{{ $recurring_payment_profile->auto_bill_out_amt ? 'Yes' : 'No' }}</div>
                                </div>
                                --}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">

                    <div class="panel panel-default">
                        <div class="panel-heading">Payment Method</div>
                        <div class="panel-body">
                            <div class="row">

                                <div class="form-group col-sm-12 @iftrue($pay_method_err, 'has-error has-feedback')">
                                    <label class="control-label">Primary payment method</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-credit-card"></i></div>
                                        <div class="form-control">
                                        @if ($recurring_payment_profile->payment_method_id)
                                            {{ $recurring_payment_profile->paymentMethod->display_name }} - {{ $recurring_payment_profile->paymentMethod->account_number }}
                                        @else
                                            None
                                        @endif
                                        </div>
                                        @if ($pay_method_err)
                                            <span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>
                                        @endif

                                        {{--% if recurring_payment_profile.payment_method_id %}
                                            <div class="input-group-btn"><a href="/account/payment_methods/{{ $recurring_payment_profile->payment_method_id }}" class="btn btn-default"><i class="fa fa-search"></i> View</a></div>
                                        {% endif %--}}
                                    </div>
                                    @if ($pay_method_err)
                                        @if ($recurring_payment_profile->paymentMethod->is_expired)
                                            <small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Expired on {{ $recurring_payment_profile->paymentMethod->cc_expiry }}</small>
                                        @endif
                                        @if ($recurring_payment_profile->paymentMethod->deleted_at)
                                            <small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Deleted on {{ $recurring_payment_profile->paymentMethod->deleted_at }}</small>
                                        @endif
                                    @endif
                                </div>

                                {{--<div class="form-group col-sm-12">
                                    <label>Backup payment method</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-credit-card"></i></div>
                                        <div class="form-control">Not specified.</div>
                                    </div>
                                </div>--}}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="transactions">

            @if ($initial_order && $recurring_payment_profile->init_amt > 0)
                <div class="callout bg-info">
                    <i class="fa fa-inbox"></i> This profile was created with an initial payment of {{ money($recurring_payment_profile->init_amt, $initial_order->currency_code) }} on the original contribution. <a href="{{ route('backend.orders.edit', $initial_order) }}">Click here to view the contribution</a>.
                </div>
                &nbsp;<br />
            @endif

            @if ($recurring_payment_profile->transactions->count())
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="16"></th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Transaction ID No</th>
                            <th style="text-align:center;">Payment status</th>
                            <th>Payment response</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($recurring_payment_profile->transactions as $transaction)
                        <tr class="@iftrue($transaction->is_refunded,'text-muted') @iftrue(!$transaction->is_payment_accepted,'text-danger') @iftrue(dpo_is_enabled() and $transaction->is_unsynced,'danger')">
                            <td><a href="#" class="ds-txn" data-txn-id="{{ $transaction->id }}"><i class="fa fa-search"></i></a></td>
                            <td>{{ toLocalFormat($transaction->order_time) }} <small class="text-muted">{{ toLocalFormat($transaction->order_time, 'g:iA') }}</small></td>
                            <td>{{ $transaction->payment_description }}</td>
                            <td>{{ $transaction->transaction_id }}</td>
                            <td style="text-align:center;">{{ $transaction->payment_status }}</td>
                            <td>{{ $transaction->reason_code }}</td>
                            <td style="text-align:right;">@iftrue($transaction->is_refunded,'<i class="fa fa-reply"></i>') {{ money($transaction->amt, $transaction->currency_code) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            @else

                <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> No transactions have been processed.</div>

            @endif

        </div>
    </div>

</div>

<div class="modal fade modal-primary" id="charge-now">
    <div class="modal-dialog modal-xs">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-money"></i> Charge Now</h4>
            </div>
            <form class="form-horizontal" method="post" action="/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/charge">
                @csrf
                <div class="modal-body">

                    <div class="alert alert-info text-center">
                        You are processing the <strong>{{ money($recurring_payment_profile->total_amt, $recurring_payment_profile->currency_code) }}</strong> payment due <strong>{{ toLocalFormat($recurring_payment_profile->next_billing_date, 'M j, Y') }}</strong>.
                    </div>

                    {{--<div class="form-group">
                        <label for="" class="col-sm-3 control-label">Amount</label>
                        <div class="col-sm-4">
                            <div class="form-control-static">${{ (recurring_payment_profile.amt + recurring_payment_profile.tax_amt)|numeral(2) }}</div>
                        </div>
                    </div>
                    --}}

                    <div class="form-group">
                        <label for="linkAccount-member_id" class="col-sm-3 control-label">Method</label>
                        <div class="col-sm-9">
                            <div class="row">
                                @foreach ($payment_methods as $method)
                                    <div class="col-sm-6">
                                        <label class="radio-inline @iftrue($method->is_expired,'text-danger')">
                                            <input type="radio" name="payment_method" value="{{ $method->id }}">&nbsp;
                                            <i class="fa {{ fa_payment_icon($method->account_type) }}"></i> &nbsp;
                                            {{ $method->display_name }} <small>{{ $method->account_number }} @iftrue($method->is_expired,'<strong>EXPIRED</strong>')</small>
                                        </label>
                                    </div>
                                @endforeach
                                <div class="col-sm-6"><label class="radio-inline"><input type="radio" name="payment_method" value="eft"> EFT</label></div>
                                <div class="col-sm-6"><label class="radio-inline"><input type="radio" name="payment_method" value="check"> Check</label></div>
                                <div class="col-sm-6"><label class="radio-inline"><input type="radio" name="payment_method" value="cash"> Cash</label></div>
                                <div class="col-sm-6"><label class="radio-inline"><input type="radio" name="payment_method" value="other"> Other</label></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group reference-wrap hide">
                        <label for="" class="col-sm-3 control-label">Reference</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="reference" value="" maxlength="18">
                            <small class="text-muted">A reference number as proof of this payment (for example: check number).</small>
                        </div>
                    </div>

                    <div class="form-group note-wrap hide">
                        <label for="" class="col-sm-3 control-label">Note</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="note" value="" maxlength="128">
                            <small class="text-muted">Any additional note you want to leave to help track this payment.</small>
                        </div>
                    </div>

                    {{--<div class="form-group">
                        <label for="linkAccount-member_id" class="col-sm-3 control-label">Type</label>
                        <div class="col-sm-9">
                            <div>
                                <label class="radio-inline"><input type="radio" value="1" checked name="change_next_bill_date"> This is the payment for <strong>{{ $recurring_payment_profile->next_billing_date|toUtc('M j, Y') }}</strong>.</label>
                            </div>
                            <div>
                                <label class="radio-inline"><input type="radio" value="0" name="change_next_bill_date"> This is an additional payment.</label>
                            </div>
                        </div>
                    </div>--}}

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary text-bold">Process Charge</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-danger" id="cancel-rpp-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-times"></i> Cancel Recurring Payment</h4>
            </div>
            <form method="post" action="/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/cancel">
                @csrf
                <div class="modal-body">

                    <p>Are you sure you want to cancel this recurring payment?</p>

                    @if ($recurring_payment_profile->sponsorship and sys_get('sponsorship_end_on_rpp_cancel'))
                        <ul class="text-danger fa-ul">
                            <li><i class="fa fa-li fa-exclamation-triangle"></i> <strong>Warning!</strong> This will also end <strong>{{ $recurring_payment_profile->member->display_name }}'s</strong> sponsorship of <strong>{{ $recurring_payment_profile->sponsorship->display_name }}</strong>.</li>
                        </ul>
                    @endif

                    <div class="form-group">
                        <label for="" class="control-label">Reason</label>
                        <select required class="form-control @iftrue(sys_get('rpp_cancel_allow_other_reason'),'selectize-tag')" name="cancel_reason" size="1" placeholder="Choose or Type a Reason">
                            <option></option>
                            @foreach (sys_get('list:rpp_cancel_reasons') as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">You must provide a reason for cancelling the recurring payment.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger text-bold">Cancel Now</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-danger" id="update-cancel-reason-rpp-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Update Cancel Reason</h4>
            </div>
            <form method="post" action="{{ route('backend.recurring_payments.profile.update_cancel', $recurring_payment_profile->profile_id ) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="" class="control-label">Reason</label>
                        <select required class="form-control @iftrue(sys_get('rpp_cancel_allow_other_reason'),'selectize-tag')" name="cancel_reason" size="1" placeholder="Choose or Type a Reason">
                            <option></option>
                            @foreach (sys_get('list:rpp_cancel_reasons') as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger text-bold">Update</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-primary" id="dp-pledge">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-money"></i> Override DonorPerfect Pledge</h4>
            </div>
            <form method="post" action="/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/override_pledge">
                @csrf
                <div class="modal-body">

                    <div class="alert alert-info text-center">
                        Overriding the DonorPerfect Pledge ID ensures that all future transactions will count towards this pledge. Leaving it blank will ensure that they will count towards the original pledge created at the time of the initial contribution.
                    </div>

                    <div class="form-group">
                        <label for="dp-pledge-id" class="control-label">DP Pledge ID</label>
                        <input type="number" id="dp-pledge-id" class="form-control" name="dp-pledge-id" value="{{ $recurring_payment_profile->dp_pledge_id_override }}">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary text-bold">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
spaContentReady(function() {

    onMethodChange = function(){
        $('.reference-wrap, .note-wrap').addClass('hide');

        method = $('input[name=payment_method]:checked').val();

        if (method == 'cash' || method == 'eft' || method == 'check' || method == 'other') {
            $('.reference-wrap, .note-wrap').removeClass('hide');
        }
    }

    $('input[name=payment_method]').first().prop('checked', true);
    $('input[name=payment_method]').on('click', onMethodChange);

    onMethodChange();

});
</script>

{{--
onclick="$.confirm('Are you sure want to charge <strong>${{ (recurring_payment_profile.amt + recurring_payment_profile.tax_amt)|numeral(2) }}</strong> on <strong>{{ $recurring_payment_profile->paymentMethod.display_name }} ({{ $recurring_payment_profile->paymentMethod.account_number }}</strong>)?', function(){ location='/jpanel/recurring_payments/{{recurring_payment_profile.profile_id}}/charge'; }, 'warning', 'fa-credit-card-alt');"--}}
@endsection
