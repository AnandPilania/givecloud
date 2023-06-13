
@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="row clearfix">
    <div class="col-lg-12">
        <h1 class="page-header">
            {{ $pageTitle }}

            <div class="pull-right">
                <a href="#" class="btn btn-default datatable-export"><i class="fa fa-download fa-fw"></i> Export</a>
            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

@if (dpo_is_enabled() and $unsynced_count > 0 and $filters->unsynced == 0)
    <div class="alert alert-danger text-center">
        <i class="fa fa-exclamation-triangle fa-4x"></i><br />You have <strong><?= e(number_format($unsynced_count)) ?></strong> transactions that are not sync'd with DonorPerfect.<br /><a href="/jpanel/reports/transactions?unsynced=1" class="btn btn-xs btn-danger">Show Transactions</a>
    </div>
@endif

<div class="row">
    <form class="datatable-filters">
        <input type="hidden" name="unsynced" value="{{ $filters->unsynced }}">

        <div class="datatable-filters-fields flex flex-wrap items-end -mx-2">

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" name="search" id="filterSearch" value="{{ $filters->search }}" placeholder="Search" data-placement="top" data-toggle="popover" data-trigger="focus" data-content="Use <i class='fa fa-search'></i> Search to filter transactions by:<br><i class='fa fa-check'></i> Supporter Name &amp; Email <span class='label label-success'>NEW</span><br><i class='fa fa-check'></i> Transaction<br><i class='fa fa-check'></i> Response" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-control">
                    <option value="">Any Payment Status</option>
                    <option value="fail"     @selected($filters->payment_status == "fail")>Fail</option>
                    <option value="success"  @selected($filters->payment_status == "success")>Success</option>
                    <option value="refunded" @selected($filters->payment_status == "refunded")>Refunded</option>
                </select>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Charged on</label>
                <div class="input-group input-daterange">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="text" class="form-control" name="ordertime_str" value="{{ $filters->ordertime_str }}" placeholder="Charged on..." />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="ordertime_end" value="{{ $filters->ordertime_end }}" />
                </div>
            </div>

            <div class="form-group w-full md:w-1/2 lg:w-1/3 xl:w-1/4 px-2 flex-none more-field">
                <label class="form-label">Amount</label>
                <div class="input-group">
                    <div class="input-group-addon">{{ currency()->symbol }}</div>
                    <input type="text" class="form-control" name="amt_str" value="{{ $filters->amt_str }}" placeholder="Amount" />
                    <span class="input-group-addon">to</span>
                    <input type="text" class="form-control" name="amt_end" value="{{ $filters->amt_end }}" />
                </div>
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

            <div class="form-group pt-1 px-2">
                <button type="button" class="btn btn-default toggle-more-fields form-control w-max">More Filters</button>
            </div>

        </div>
    </form>
</div>

<div class="table-responsive">
    <table id="transactionHistory" class="table table-striped table-bordered table-hover responsive">
        <thead>
            <tr>
                <th width="16"></th>
                <th>Date</th>
                <th>Supporter</th>
                <th>Profile</th>
                <th>Method</th>
                <th>Reference</th>
                <th style="text-align:center;">Status</th>
                <th>Response</th>
                <th style="text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection
