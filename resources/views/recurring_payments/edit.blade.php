
@extends('layouts.app')
@section('title', 'Modify Payment')

@section('content')
<form class="recurringpaymentprofile" action="/jpanel/recurring_payments/{{ $recurring_payment_profile->profile_id }}/edit" method="POST">
    @csrf

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header clearfix">
                <span class="page-header-text">Modify Payment</span>
                <div class="visible-xs-block"></div>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                </div>

                <p class="text-secondary">
                    Profile start date {{ toLocalFormat($recurring_payment_profile->profile_start_date, 'M j, Y') }}
                    Profile ID No. {{ $recurring_payment_profile->profile_id }}
                </p>
            </h1>
        </div>
    </div>

    @inject('flash', 'flash')

    @if ($flash->output())
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle fa-fw"></i> {{ $flash->output() }}
    </div>
    @endif

    {{--<div class="panel panel-default">
        <div class="panel-heading">Next Payment <small class="text-muted">Only affects the next recurring payment</small></div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Next payment date</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                            <input type="text" class="form-control datePretty" name="next_billing_date" value="{{ toLocalFormat($recurring_payment_profile->next_billing_date, 'M j, Y') }}">
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>--}}

    <div class="panel panel-default">
        <div class="panel-heading">Recurring Settings <small class="text-muted">Affects all future payments</small></div>
        <div class="panel-body">

            <div class="form-horizontal">

                @if (feature('givecloud_pro') && ! $recurring_payment_profile->is_locked)
                <div class="form-group">
                    <label for="is_manual" class="col-sm-4 control-label">Processing</label>
                    <div class="col-sm-3 col-lg-2">
                        <select name="is_manual" id="is_manual" class="form-control">
                            @if ($payment_methods->count())
                                <option value="0" @selected($recurring_payment_profile->is_manual == '0')>Auto</option>
                            @endif
                            <option value="1" @selected($recurring_payment_profile->is_manual == '1')>Manual</option>
                        </select>
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Payment Method</label>
                    <div class="col-sm-3">
                        @if ($payment_methods->count())
                            @foreach ($payment_methods as $method)
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="payment_method_id" id="paymentMethod{{ $method->id }}" value="{{ $method->id }}" @checked($method->id == $recurring_payment_profile->payment_method_id)> <i class="fa fa-fw {{ $method->fa_icon }}"></i> {{ $method->display_name }} <small>(Ending in {{ $method->account_last_four }})</small>
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <div class="form-control-static text-muted">
                                <i class="fa fa-exclamation-circle"></i> None Available<br>
                                <small class="block mt-2 leading-tight">You cannot set this profile to automatic until there are valid payment methods added to the supporter.</small>
                            </div>
                        @endif
                    </div>
                </div>

                @if (feature('givecloud_pro'))
                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Cycle frequency</label>
                    <div class="col-sm-3">
                        <select name="billing_period" id="billing_period" class="form-control">
                            <option value="Week"      @selected($recurring_payment_profile->billing_period == 'Week')>Weekly</option>
                            <option value="SemiMonth" @selected($recurring_payment_profile->billing_period == 'SemiMonth')>Bi-Weekly</option>
                            <option value="Month"     @selected($recurring_payment_profile->billing_period == 'Month')>Monthly</option>
                            <option value="Quarter"   @selected($recurring_payment_profile->billing_period == 'Quarter')>Quarterly</option>
                            <option value="SemiYear"  @selected($recurring_payment_profile->billing_period == 'SemiYear')>Bi-Annually</option>
                            <option value="Year"      @selected($recurring_payment_profile->billing_period == 'Year')>Annually</option>
                        </select>
                    </div>
                </div>
                @else
                <input type="hidden" name="billing_period" value="{{ $recurring_payment_profile->billing_period }}">
                @endif

                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Payment day</label>
                    <div class="col-sm-3">
                        <div id="recurring_day_calendar" class="date-inline" data-input="#next-billing-date" data-date="{{ toLocalFormat($recurring_payment_profile->next_billing_date, 'Y-m-d') }}">
                        </div>
                        <input type="hidden" id="next-billing-date" name="next_billing_date_override" value="{{ toLocalFormat($recurring_payment_profile->next_billing_date, 'Y-m-d') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Amount due each cycle</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="tel" class="form-control text-right" name="amt" value="{{ numeral($recurring_payment_profile->amt)->format('0.00') }}">
                            <div class="input-group-addon">{{ $recurring_payment_profile->currency_code }}</div>
                        </div>
                    </div>
                </div>

                @if (sys_get('dcc_enabled') && ! sys_get('bool:dcc_ai_is_enabled'))
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Is DCC enabled for this profile</label>
                        <div class="col-sm-3">
                            <div class="radio">
                                <label class="right-gutter">
                                    <input type="radio" name="dcc_enabled_by_customer" id="dcc_enabled_by_customer" value="1" @checked($recurring_payment_profile->dcc_enabled_by_customer)> Yes
                                </label>
                                <label>
                                    <input type="radio" name="dcc_enabled_by_customer" id="dcc_enabled_by_customer" value="0" @checked(!$recurring_payment_profile->dcc_enabled_by_customer)> No
                                </label>
                            </div>
                        </div>
                    </div>
                @elseif(sys_get('bool:dcc_ai_is_enabled'))
                    <div class="form-group">
                        <label for="inputCoverCostsType" class="col-sm-4 control-label">Help Cover our Costs</label>
                        <div class="col-sm-3">
                            @if (feature('givecloud_pro'))
                            <select id="inputCoverCostsType" class="form-control form-control-sm" name="dcc_type">
                                <option @selected($recurring_payment_profile->dcc_type === 'most_costs') value="most_costs">Most Costs ({{ money($dcc_amounts['most_costs'], $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]')  }})</option>
                                <option @selected($recurring_payment_profile->dcc_type === 'more_costs') value="more_costs">More Costs ({{ money($dcc_amounts['more_costs'], $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]')  }})</option>
                                <option @selected($recurring_payment_profile->dcc_type === 'minimum_costs') value="minimum_costs">Minimum Costs ({{ money($dcc_amounts['minimum_costs'], $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]')  }}) </option>
                                @if($has_original_dcc)
                                    <option @selected($has_original_dcc || $recurring_payment_profile->dcc_type === 'original')  value="original">Original ({{ money($recurring_payment_profile->dcc_amount, $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]')  }}) </option>
                                @endif
                                <option @selected(!$has_original_dcc && !$recurring_payment_profile->dcc_type) value="">No Thank You</option>
                            </select>
                            @elseif($recurring_payment_profile->dcc_amount)
                                <div class="input-group">
                                    <input type="text" class="form-control text-right" readonly value="{{ numeral($recurring_payment_profile->dcc_amount)->format('0.00') }}">
                                    <div class="input-group-addon">{{ $recurring_payment_profile->currency_code }}</div>
                                </div>
                            @else
                                <select id="inputCoverCostsType" class="form-control form-control-sm" name="dcc_type">
                                    <option value="most_costs">Most Costs ({{ money($dcc_amounts['most_costs'], $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]')  }})</option>
                                    <option value="more_costs">More Costs ({{ money($dcc_amounts['more_costs'], $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]')  }})</option>
                                    <option value="minimum_costs">Minimum Costs ({{ money($dcc_amounts['minimum_costs'], $recurring_payment_profile->currency_code)->format('$0,000.00 [$$$]')  }}) </option>
                                    <option value="" selected>No Thank You</option>
                                </select>
                            @endif
                        </div>
                    </div>
                @endif

                @if (feature('givecloud_pro') && ! $recurring_payment_profile->is_locked)
                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Remaining billing cycles</label>
                    <div class="col-sm-3">
                        <input placeholder="Indefinite" type="text" class="form-control" name="num_cycles_remaining" value="{{ $recurring_payment_profile->num_cycles_remaining }}">
                    </div>
                </div>
                @endif

                @if (feature('givecloud_pro') && ! $recurring_payment_profile->is_locked)
                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Stop payments after</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                            <input type="text" class="form-control datePretty" name="final_payment_due_date" value="{{ toLocalFormat($recurring_payment_profile->final_payment_due_date, 'M j, Y') }}">
                        </div>
                        <small class="block mt-2 leading-tight text-muted">No payments will be processed after this date.</small>
                    </div>
                </div>
                @endif

            </div>

        </div>
    </div>

</form>

<script>
    spaContentReady(function() {
        $('.date-inline').datepicker({
            'format'    : 'yyyy-mm-dd',
            'startDate' : {!! json_encode(toUtcFormat('tomorrow','Y-m-d')) !!},
            'beforeShowDay' : function(date) {
                var allowed_days = [1,15],
                    is_allowed = (allowed_days.indexOf(date.getDate()) >= 0);

                return {
                    'enabled' : true,
                    'classes' : (is_allowed) ? 'text-extra-bold' : '',
                    'tooltip' : '',
                    'content' : ''
                };
            }
        });
        $('.date-inline').on('changeDate', function(ev){
            var $this = $(this);
            if ($this.data('input')) {
                var $in = $($this.data('input'));
                if ($in) {
                    $in.val($this.datepicker('getFormattedDate'));
                }
            }
        });
    });
</script>
@endsection
