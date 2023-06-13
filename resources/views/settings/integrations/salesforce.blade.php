@extends('layouts.app')
@section('title', 'Salesforce')

@section('content')
    <form action="{{ route('backend.settings.integrations.salesforce.store') }}" method="post">
        @csrf
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Salesforce
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8 col-lg-offset-2">

            <div class="form-horizontal">
                {{ app('flash')->output() }}

                <div class="panel panel-default">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-exchange"></i> Salesforce
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-exchange"></i> Salesforce</div>
                                <div class="panel-sub-desc">
                                    <p>
                                        By granting us third party access permissions, we're able to perform
                                        Salesforce API operations on your behalf.
                                    </p>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-8">
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4" style="margin-top:20px">
                                        <button
                                            id="hotglue_connected"
                                            data-hotglue-show-when-connected
                                            type="button"
                                            class="{{ $isConnected ? '' : 'hide' }} inline-flex items-center rounded-md border border-green-500 bg-white px-6 py-3 text-base font-medium text-green-500 shadow-sm hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                            <i class="-ml-1 mr-3 fa fa-check"></i>
                                            Connected
                                        </button>
                                        <button id="hotglue_disconnected"
                                                data-hotglue-hide-when-connected
                                                class="{{ $isConnected ? 'hide' : '' }} btn btn-primary btn-lg">
                                            Connect Salesforce
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-lock"></i> External IDs
                    </div>
                    <div class="panel-body" style="padding-bottom:15px">

                        <div class="row">

                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-lock"></i> External IDs</div>
                                <div class="panel-sub-desc">
                                    <p>For us to reference your contributions and supporters, you need to create a custom external id field in Salesforce for your Contacts (Supporters) and your Opportunities (Donations). </p>
                                    <p class="mt-4 max-w-[340px]">Ask your Salesforce administrator or get <a href="https://help.givecloud.com/en/articles/5659398-salesforce" target="_blank">more info here</a></p>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-8">
                                <div class="form-group">
                                    <label for="salesforce_contact_external_id" class="col-md-4 control-label">Contact External Id Field </label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="salesforce_contact_external_id" name="salesforce_contact_external_id" value="{{ sys_get('salesforce_contact_external_id') }}" />
                                        <p class="mt-2 text-sm text-gray-500">Should look like <span class="italic">Givecloud_Contact_ExternalID__c</span></p>
                                    </div>
                                </div>

                                <div class="form-group mt-2">
                                    <label for="salesforce_opportunity_external_id" class="col-md-4 control-label">Opportunity External Id Field </label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" class="form-control" id="salesforce_opportunity_external_id" name="salesforce_opportunity_external_id" value="{{ sys_get('salesforce_opportunity_external_id') }}" />
                                        <p class="mt-2 text-sm text-gray-500">Should look like <span class="italic">Givecloud_Opportunity_ExternalID__c</span></p>
                                    </div>
                                </div>

                                <div class="form-group mt-2">
                                    <label for="salesforce_recurring_donation_external_id" class="col-md-4 control-label">Recurring Donations External Id Field </label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" class="form-control" id="salesforce_recurring_donation_external_id" name="salesforce_recurring_donation_external_id" value="{{ sys_get('salesforce_recurring_donation_external_id') }}" />
                                        <p class="mt-2 text-sm text-gray-500">Should look like <span class="italic">Givecloud_RecurringDonation_ExternalID__c</span></p>
                                    </div>
                                </div>

                                <button type="submit"
                                        class="inline-flex items-center pull-right btn btn-success">
                                    Save
                                </button>

                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- /.form-horizontal -->

        </div>
    </div>
</form>
@endsection

@section('scripts')
    <script src="https://hotglue.xyz/widgetv2.js"></script>
    <script>
        spaContentReady(function(){
            j.hotglue(@json($config));
        });
    </script>
@endsection
