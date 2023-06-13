@extends('layouts.app')
@section('title', 'Import')

@section('content')
<form id="import-upload" class="form-horizontal" method="post" action="/jpanel/import/upload" enctype="multipart/form-data">
@csrf

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Import

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <a href="/jpanel/import" class="btn btn-default"><i class="fa fa-search fa-fw"></i> Back</a>
                <button type="submit" class="btn btn-success">Create import <i class="fa fa-arrow-right"></i></button>
                {{--<a href="" class="btn btn-info"><span class="badge">3</span> In Progress</a>
                <a href="" class="btn btn-default"><i class="fa fa-search fa-fw"></i> View Archive</a>--}}
            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

<div class="panel panel-default">
    <div class="panel-body">

        <div class="row">

            <div class="col-sm-6 col-lg-4">
                <div class="panel-sub-title">Import Type</div>
                <div class="panel-sub-desc">
                    Choose the type of import you'd like to perform.
                </div>
            </div>

            <div class="col-sm-6 col-md-5 col-md-offset-1 col-lg-6 col-lg-offset-2">

                <div class="radio">
                    <label>
                        <input name="import_type" type="radio" value="SupportersFromFile" checked>
                        <strong>Supporters/Donors from a File</strong>
                        <div class="text-muted">
                            Update your list of supporters based on a list of contacts in a spreadsheet you upload.<br>
                            <a href="/jpanel/import/templates/supporters_from_file"><i class="fa fa-file"></i> Supporters Import Template</a>
                            <br><br>
                            <span class="text-info"><strong><i class="fa fa-question-circle"></i> Note:</strong> Supporters in your import file with the same email address as an existing supporter will be merged with the existing supporter.</span>
                        </div>
                    </label>
                </div>

                <br>

                <div class="radio">
                    <label>
                        <input name="import_type" type="radio" value="SponsorshipsFromFile">
                        <strong>Sponsorship Records from a File</strong>
                        <div class="text-muted">
                            Create new sponsorship records ({{ sys_get('syn_sponsorship_children') }}) from a list of records in spreadsheet you upload.<br>
                            <a href="/jpanel/import/templates/sponsorships_from_file"><i class="fa fa-file"></i> Sponsorship Import Template</a>
                        </div>
                    </label>
                </div>

                <br>

                <div class="radio">
                    <label>
                        <input name="import_type" type="radio" value="ContributionsFromFile">
                        <strong>Purchase History from a File</strong>
                        <div class="text-muted">
                            Create historical purchase records from a file you upload.<br>
                            <a href="/jpanel/import/templates/contributions_from_file"><i class="fa fa-file"></i> Contribution History Import Template</a>
                        </div>
                    </label>
                </div>

                <br>

                <div class="radio">
                    <label>
                        <input name="import_type" type="radio" value="RecurringPaymentProfilesFromFile">
                        <strong>Recurring Payments from a File</strong>
                        <div class="text-muted">
                            Create new recurring payment profiles from a list of recurring payments you upload.<br>
                            <a href="/jpanel/import/templates/recurring_payment_profiles_from_file"><i class="fa fa-file"></i> Recurring Payment Profile Import Template</a>
                        </div>
                    </label>
                </div>

                <br>

            </div>

        </div>
    </div>
</div>
</div></div>

</form>
@endsection
