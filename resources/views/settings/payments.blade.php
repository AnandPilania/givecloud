@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<form class="form-horizontal" action="/jpanel/settings/payments/save" method="post">
    @csrf

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Payments

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

    {!! app('flash')->output() !!}

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-money"></i> Currency
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-money"></i> Currency</div>
                    <div class="panel-sub-desc"><span class="text-info"><i class="fa fa-exclamation-circle"></i> <strong>Note:</strong> To have your currency changed please contact <a href="mailto:{{ config('mail.support.address') }}">{{ config('mail.support.address') }}</a>.</span></div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group pt-5">
                        <label for="name" class="col-md-4 control-label">Currency</label>
                        <div class="col-md-8">
                            <select name="dpo_currency" class="form-control" @unless(is_super_user()) disabled @endunless>
                            @foreach ($pinned_currencies as $currency)
                                <option value="{{ $currency['code'] }}" @selected(sys_get('dpo_currency') === $currency['code'])>({{ $currency['code'] }}) {{ $currency['name'] }}</option>
                            @endforeach
                            <option value="" disabled>-------</option>
                            @foreach ($other_currencies as $currency)
                                <option value="{{ $currency['code'] }}" @selected(sys_get('dpo_currency') === $currency['code'])>({{ $currency['code'] }}) {{ $currency['name'] }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-credit-card"></i> Accepted Payments
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-credit-card"></i> Accepted Payments</div>
                    <div class="panel-sub-desc">What payment types do you want to allow your customers to use on your site?<br /><br /><span class="text-info"><i class="fa fa-exclamation-circle"></i> <strong>Note:</strong> Make sure your gateway is configured to process these payment types as well.</span></div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-8 col-md-offset-4">

                    <div class="checkbox">
                        <label><input name="cardtypes[]" type="checkbox" value="v" @checked(in_array('v',explode(',',sys_get('cardtypes')))) > <i class="fa fa-cc-visa fa-fw"></i> Visa</label>
                    </div>
                    <div class="checkbox">
                        <label><input name="cardtypes[]" type="checkbox" value="m" @checked(in_array('m',explode(',',sys_get('cardtypes')))) > <i class="fa fa-cc-mastercard fa-fw"></i> MasterCard</label>
                    </div>
                    <div class="checkbox">
                        <label><input name="cardtypes[]" type="checkbox" value="a" @checked(in_array('a',explode(',',sys_get('cardtypes')))) > <i class="fa fa-cc-amex fa-fw"></i> American Express</label>
                    </div>
                    <div class="checkbox">
                        <label><input name="cardtypes[]" type="checkbox" value="d" @checked(in_array('d',explode(',',sys_get('cardtypes')))) > <i class="fa fa-cc-discover fa-fw"></i> Discover</label>
                    </div>
                    <div class="checkbox">
                        <label><input name="cardtypes[]" type="checkbox" value="b-ach" @checked(in_array('b-ach',explode(',',sys_get('cardtypes')))) > <i class="fa fa-pencil-square-o fa-fw"></i> Business Checks (ACH)</label>
                    </div>
                    <div class="checkbox">
                        <label><input name="cardtypes[]" type="checkbox" value="p-ach" @checked(in_array('p-ach',explode(',',sys_get('cardtypes')))) > <i class="fa fa-pencil-square-o fa-fw"></i> Personal Checks (ACH)</label>
                    </div>

                </div>
                </div>
            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-refresh"></i> Recurring Payments
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-refresh"></i> Recurring Payments</div>
                    <div class="panel-sub-desc">
                        Manage custom settings that will determine how your donor's/customer's recurring payments are processed.

                        <!--<br /><br />
                        <span class="text-info">
                            <i class="fa fa-exclamation-circle"></i> <strong>Note</strong><br>
                            You can change the Recurring Payment Type on the product level.
                        </span>-->
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Default Type</label>
                        <div class="col-md-8">

                            <div class="radio">
                                <label>
                                    <input id="rpp_default_type_natural" name="rpp_default_type" type="radio" value="natural" onchange="onRecurringChange();" @checked(sys_get('rpp_default_type') === 'natural')>
                                    <strong>Natural Payments</strong>
                                    <div class="text-muted">
                                        <i class="fa fa-check"></i> Initial payment is required.<br>
                                        <i class="fa fa-check"></i> Subsequent payments happen based on the day of the first payment.<br>
                                    </div>
                                </label>
                            </div>

                            <br />

                            <div class="radio">
                                <label>
                                    <input id="rpp_default_type_fixed" name="rpp_default_type" type="radio" value="fixed" onchange="onRecurringChange();" @checked(sys_get('rpp_default_type') === 'fixed')>
                                    <strong>Fixed Payments</strong>
                                    <div class="text-muted">
                                        <i class="fa fa-check"></i> Initial payment is optional.<br>
                                        <i class="fa fa-check"></i> Donor/customer must select the day of their next (or first) payment from a list of predefined days (for example: 1st or 15th of the month).<br>
                                    </div>
                                </label>
                            </div>

                            <br>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default collapse fixed-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-calendar-o"></i> Fixed Payment Days
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-calendar-o"></i> Fixed Payment Days</div>
                    <div class="panel-sub-desc">
                        When Fixed Payments are enabled, you must select the payment days that your donors/customers will be forced to select for their recurring payment.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Days of Month<p><a href="javascript:$('.-month-days').prop('checked', true);" class="btn btn-xs btn-info"><i class="fa fa-check-square-o"></i> All</a> <a href="javascript:$('.-month-days').prop('checked', false);" class="btn btn-xs btn-info"><i class="fa fa-square-o"></i> None</a></p></label>
                        <div class="col-md-8">

                            <table>
                                <tr>
                                    @for ($i = 1; $i<=28; $i++)
                                        <td>
                                            <div class="checkbox right-gutter">
                                                <label><input type="checkbox" class="-month-days" @checked(in_array($i, explode(',',sys_get('payment_day_options')))) name="payment_day_options[]" value="{{ $i }}"> {{ $i }}</label>
                                            </div>
                                        </td>

                                        @if ($i > 1 && $i % 5 == 0)
                                            </tr><tr>
                                        @endif
                                    @endfor
                                </tr>
                            </table>

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Days of Week</label>
                        <div class="col-md-8">

                            <table>
                                <tr>

                                    @for ($i = 1; $i<=7; $i++)
                                        <td>
                                            <div class="checkbox right-gutter">
                                                <label><input type="checkbox" class="-week-days" @checked(in_array($i, explode(',',sys_get('payment_day_of_week_options')))) name="payment_day_of_week_options[]" value="{{ $i }}"> {{ day_of_week($i) }}</label>
                                            </div>
                                        </td>

                                        @if ($i > 1 && $i % 2 == 0)
                                            </tr><tr>
                                        @endif
                                    @endfor
                                </tr>
                            </table>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default collapse fixed-only">
        <div class="panel-heading visible-xs">
            <i class="fa fa-calendar"></i> Fixed Payment Snap
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-calendar"></i> Fixed Payment Snap</div>
                    <div class="panel-sub-desc">
                        Select how Payments should snap to the Fixed Payment Days from above when Fixed Payments are enabled.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Weekly</label>
                        <div class="col-md-8">

                            <select class="form-control" name="rpp_start_date_snap_Week">
                                <option @selected(sys_get('rpp_start_date_snap_Week') === 'donor') value="donor">Wait a Full Billing Period (Favor Donor)</option>
                                <option @selected(sys_get('rpp_start_date_snap_Week') === 'organization') value="organization">Soonest Possible Date (Favor Organization)</option>
                            </select>

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Bi-Weekly</label>
                        <div class="col-md-8">

                            <select class="form-control" name="rpp_start_date_snap_SemiMonth">
                                <option @selected(sys_get('rpp_start_date_snap_SemiMonth') === 'donor') value="donor">Wait a Full Billing Period (Favor Donor)</option>
                                <option @selected(sys_get('rpp_start_date_snap_SemiMonth') === 'organization') value="organization">Soonest Possible Date (Favor Organization)</option>
                            </select>

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Monthly</label>
                        <div class="col-md-8">

                            <select class="form-control" name="rpp_start_date_snap_Month">
                                <option @selected(sys_get('rpp_start_date_snap_Month') === 'donor') value="donor">Wait a Full Billing Period (Favor Donor)</option>
                                <option @selected(sys_get('rpp_start_date_snap_Month') === 'organization') value="organization">Soonest Possible Date (Favor Organization)</option>
                            </select>

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Quarterly</label>
                        <div class="col-md-8">

                            <select class="form-control" name="rpp_start_date_snap_Quarter">
                                <option @selected(sys_get('rpp_start_date_snap_Quarter') === 'donor') value="donor">Wait a Full Billing Period (Favor Donor)</option>
                                <option @selected(sys_get('rpp_start_date_snap_Quarter') === 'organization') value="organization">Soonest Possible Date (Favor Organization)</option>
                            </select>

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Annually</label>
                        <div class="col-md-8">

                            <select class="form-control" name="rpp_start_date_snap_Year">
                                <option @selected(sys_get('rpp_start_date_snap_Year') === 'donor') value="donor">Wait a Full Billing Period (Favor Donor)</option>
                                <option @selected(sys_get('rpp_start_date_snap_Year') === 'organization') value="organization">Soonest Possible Date (Favor Organization)</option>
                            </select>

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Bi-Annually</label>
                        <div class="col-md-8">

                            <select class="form-control" name="rpp_start_date_snap_SemiYear">
                                <option @selected(sys_get('rpp_start_date_snap_SemiYear') === 'donor') value="donor">Wait a Full Billing Period (Favor Donor)</option>
                                <option @selected(sys_get('rpp_start_date_snap_SemiYear') === 'organization') value="organization">Soonest Possible Date (Favor Organization)</option>
                            </select>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Require a Donor Login
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-user"></i> Require a Donor Login</div>
                    <div class="panel-sub-desc">
                        Decide whether or not donors should have to create a login when donating on a recurring basis.

                        <br /><br />
                        <span class="text-info">
                            <i class="fa fa-question-circle"></i> <strong>What happens if a donor doesn't create a login?</strong><br>
                            If donors are able to donate on a recurring basis without being forced to create a login, a supporter account with no password will sill be created. However, they will need to "Reset Their Password" if they ever want to access their supporter account for grabbing tax receipts, updating their payment methods, and so on.
                        </span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Recurring Payments</label>
                        <div class="col-md-8">

                            <div class="radio">
                                <label>
                                    <input id="rpp_require_login_yes" name="rpp_require_login" type="radio" value="1" @checked(sys_get('rpp_require_login') == 1)>
                                    <strong>Always Require a Login</strong> (Password Required)
                                    <div class="text-muted">
                                        <i class="fa fa-check"></i> Donor will be forced to create a login by entering a password before paying.<br>
                                        <i class="fa fa-check"></i> Donor will be able to login and manage their profile and recurring donation.<br>
                                    </div>
                                </label>
                            </div>

                            <br />

                            <div class="radio">
                                <label>
                                    <input id="rpp_require_login_no" name="rpp_require_login" type="radio" value="0" @checked(sys_get('rpp_require_login') == 0)
                                    <strong>Never Require a Login</strong> (Password Optional)
                                    <div class="text-muted">
                                        <i class="fa fa-check"></i> Donor can complete a donation without ever creating a login.<br>
                                        <i class="fa fa-check"></i> Donor will not be able to login and manage their profile or recurring donation unless they reset their password.<br>
                                    </div>
                                </label>
                            </div>

                            <br>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-user"></i> Recurring Payment Cancellation
        </div>
        <div class="panel-body clearfix">

            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title"><i class="fa fa-question-circle"></i> Recurring Payment Cancellation</div>
                    <div class="panel-sub-desc"><span class="text-info">"Why are you cancelling your recurring payment?"</span><br><br>Require that donors provide a reason for cancelling their payment.</div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Reasons</label>
                        <div class="col-md-8">
                            <select name="rpp_cancel_reasons[]" multiple class="form-control selectize-info selectize-tags auto-height">
                                @foreach (explode(',',sys_get('rpp_cancel_reasons')) as $source)
                                    <option value="{{ $source }}" selected>{{ $source }}</option>
                                @endforeach
                            </select>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="rpp_cancel_allow_other_reason" value="1" @checked(sys_get('rpp_cancel_allow_other_reason') == 1)> Allow 'Other' option
                                </label>
                            </div>
                            <small class="text-muted">Adds the option 'Other' to the list of options. If 'Other' is selected, a text field will display that will allow a user to enter their own reason.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div></div>

</form>

<script>
    onRecurringChange = function () {
        is_fixed = $('#rpp_default_type_fixed').prop('checked');
        if (is_fixed) {
            $('.fixed-only').collapse('show');
        } else {
            $('.fixed-only').collapse('hide');
        }
    }

    spaContentReady(function() { onRecurringChange(); });
</script>
@endsection
