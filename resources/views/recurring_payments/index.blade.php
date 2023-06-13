
@extends('layouts.app')
@section('title', 'Recurring Payments')

@section('content')
<script>
    exportRecords = function () {
        var d = j.ui.datatable.filterValues('table.dataTable');
        window.location = '/jpanel/recurring_payments.csv?' + $.param(d);
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Recurring Payments
            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a class="btn btn-default" onclick="exportRecords(); return false;"><i class="fa fa-download"></i><span class="hidden-xs hidden-sm"> Export</span></a>
            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

<div class="row">
    <div class="col-md-7">
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-bar-chart-o"></i> All Recurring Payments</div>
                </div>

                <div class="row">
                    <div class="col-sm-4">
                        <div id="recurring_payment_status_breakdown-chart" style="height:160px;"></div>
                        <script type="application/json" id="recurring_payment_status_breakdown-chart-data">{!! json_encode($status_breakdown_stats); !!}</script>
                    </div>

                    <div class="col-xs-6 col-sm-4">
                        <div class="row">
                            <div class="col-xs-12 stat">
                                <div class="stat-value-bold text-success">{{ numeral($total_stats->active_accounts)->format('0,0') }}</div>
                                <div class="stat-label">Active Payments</diV>
                            </div>
                            <div class="col-xs-12 stat">
                                <div class="stat-value-bold text-success">{{ currency()->symbol }}{{ numeral($total_stats->active_amount)->format('0,0') }}</div>
                                <div class="stat-label">Total Recurring Amount</diV>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-4">
                        <div class="row">
                            <div class="col-xs-12 stat">
                                <div class="stat-value text-warning">{{ currency()->symbol }}{{ numeral($total_stats->suspended_amount)->format('0,0') }}</div>
                                <div class="stat-label">Suspended Amounts</diV>
                            </div>
                            <div class="col-xs-12 stat">
                                <div class="stat-value text-danger">{{ currency()->symbol }}{{ numeral($total_stats->cancelled_amount)->format('0,0') }}</div>
                                <div class="stat-label">Cancelled Amounts</diV>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
    <div class="col-md-5">
        <div class="panel panel-default">

            <!-- /.panel-heading -->
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-calendar"></i> Next Payment Date</div>
                </div>

                <div class="row">

                    @if ($next_bill_date)
                        <div class="col-xs-6 stat">
                            <div class="stat-value"><i class="fa fa-calendar"></i> {{ toLocalFormat($next_bill_date, 'M jS') }}</div>
                            <div class="stat-label">Next billing date</diV>
                        </div>

                        <div class="col-xs-6 stat">
                            <div class="stat-value">{{ numeral($accounts_to_charge)->format('0,0') }}</div>
                            <div class="stat-label">Accounts to charge</diV>
                        </div>

                        <div class="col-xs-6 stat">
                            <div class="stat-value-bold">{{ currency()->symbol }}{{ numeral($amount_to_charge)->format('0,0') }}</div>
                            <div class="stat-label">To collect</diV>
                        </div>
                    @else
                        <div class="col-xs-12 stat">
                            <div class="stat-value">&nbsp;</div>
                            <div class="stat-label">&nbsp;</diV>
                        </div>

                        <div class="col-xs-12 stat">
                            <div class="stat-value">&nbsp;</div>
                            <div class="stat-label">&nbsp;</diV>
                        </div>
                    @endif

                </div>
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
</div>

<div class="row">
    <form class="datatable-filters">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" value="{{ $filters->search }}" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter recurring payments by:<br><i class='fa fa-check'></i> Subscriber Name<br><i class='fa fa-check'></i> Description<br><i class='fa fa-check'></i> Profile reference<br><i class='fa fa-check'></i> Profile ID" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Profile Status</label>
                <select name="status" class="form-control selectize" id="filterStatus" placeholder="Profile Status">
                    <option value=""></option>
                    <option value="active" @selected($filters->status == "Active")>Active</option>
                    <!--<option value="pending" @selected($filters->status == "Pending")>Pending</option>-->
                    <option value="suspended" @selected($filters->status == "Suspended")>Suspended</option>
                    <option value="expired" @selected($filters->status == "Expired")>Expired</option>
                    <option value="cancelled" @selected($filters->status == "Cancelled")>Cancelled</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Starts On</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="startdate_str" value="{{ $filters->startdate_str }}" placeholder="Starts on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="startdate_end" value="{{ $filters->startdate_end }}" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Next billing</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="nextbilldate_str" value="{{ $filters->nextbilldate_str }}" placeholder="Next billing..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="nextbilldate_end" value="{{ $filters->nextbilldate_end }}" />
                </div>
            </div>

            @if (feature('givecloud_pro'))
            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Profile Type</label>
                <select name="profile_type" class="form-control selectize" id="filterType" placeholder="Profile Type">
                    <option value=""></option>
                    <option value="auto" @selected($filters->profile_type == "auto")>Auto</option>
                    <option value="manual" @selected($filters->profile_type == "manual")>Manual</option>
                    @if (feature('legacy_importer'))
                        <option value="legacy" @selected($filters->profile_type == "legacy")>Legacy</option>
                    @endif
                </select>
            </div>
            @endif

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Payment Provider</label>
                <select name="payment_provider" class="form-control selectize" id="filterProvider" placeholder="Payment Provider">
                    <option value=""></option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" @selected($provider->id == $filters->payment_provider)>{{ $provider->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Payment type</label>
                <select name="payment_method_type" class="form-control selectize" id="filterStatus" placeholder="Payment Type">
                    <option value=""></option>
                    @foreach(['Visa','MasterCard','Discover','Amex','ACH','PayPal','Other'] as $type)
                        <option value="{{ $type }}" @selected($type == $filters->payment_method_type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            @if (count($currencies) > 1)
                <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                    <label class="form-label">Currency</label>
                    <select name="currency_code" class="form-control selectize" id="filterStatus" placeholder="Currency">
                        <option value=""></option>
                        @foreach ($currencies as $currency)
                        <option value="{{ $currency }}" @selected($currency == $filters->currency_code)>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Payment Method Status</label>
                <select name="payment_method_status" class="form-control selectize" id="filterStatus" placeholder="Payment Method Status">
                    <option value=""></option>
                    <option value="active"   @selected($filters->payment_method_status == "active")>Active</option>
                    <option value="expiring" @selected($filters->payment_method_status == "expiring")>Expiring in 30 Days</option>
                    <option value="expired_deleted"  @selected($filters->payment_method_status == "expired_deleted")>Expired or Missing</option>
                    <option value="expired"  @selected($filters->payment_method_status == "expired")>Only Expired</option>
                    <option value="deleted"  @selected($filters->payment_method_status == "deleted")>Only Missing</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Frequency</label>
                <select name="frequency" class="form-control selectize" id="filterStatus" placeholder="Frequency">
                    <option value=""></option>
                    <option value="Day" @selected($filters->frequency == "Day")>Day</option>
                    <option value="Week" @selected($filters->frequency == "Week")>Week</option>
                    <option value="SemiMonth" @selected($filters->frequency == "SemiMonth")>SemiMonth</option>
                    <option value="Month" @selected($filters->frequency == "Month")>Month</option>
                    <option value="Quarter" @selected($filters->frequency == "Quarter")>Quarter</option>
                    <option value="SemiYear" @selected($filters->frequency == "SemiYear")>SemiYear</option>
                    <option value="Year" @selected($filters->frequency == "Year")>Year</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Cancel Reason</label>
                <select name="cancel_reason" class="form-control selectize" id="filterStatus" placeholder="Cancel Reason">
                    <option value=""></option>
                    @foreach (sys_get('list:rpp_cancel_reasons') as $reason)
                        <option value="{{ $reason }}" @selected($filters->cancel_reason == $reason)>{{ $reason }}</option>
                    @endforeach
                </select>
            </div>

            @if (feature('givecloud_pro'))
            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Supporter Type</label>
                <select name="account_type" class="form-control selectize" id="filterAccountType" placeholder="Supporter Type">
                    <option value=""></option>
                    @foreach ($account_types as $account_type)
                        <option value="{{ $account_type->id }}" @selected($filters->account_type == $account_type->id)>{{ $account_type->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if (feature('givecloud_pro'))
            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">End date</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="enddate_str" value="{{ $filters->enddate_str }}" placeholder="End date..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="enddate_end" value="{{ $filters->enddate_end }}" />
                </div>
            </div>
            @endif

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table id="rpp-list" class="table table-striped table-bordered table-hover responsive">
                <thead>
                    <tr>
                        <th data-orderable="false" width="16"></th>
                        <th>Profile</th>
                        <th>Contribution</th>
                        <th>Supporter</th>
                        <th>Description</th>
                        <th>Start date</th>
                        <th>Next billing date</th>
                        <th data-class-name="text-right">Amount</th>
                        <th data-class-name="text-center" style="width:40px;"></th>
                        <th>Frequency</th>
                        <th data-class-name="text-right">Lifetime Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection
