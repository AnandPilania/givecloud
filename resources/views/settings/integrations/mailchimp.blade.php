@extends('layouts.app')
@section('title', 'Mailchimp')

@section('content')
    <form action="{{ route('backend.settings.integrations.mailchimp.sync') }}" method="post">
        @csrf
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Mailchimp
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8 col-lg-offset-2">

            <div class="form-horizontal">
                {{ app('flash')->output() }}

                <div class="panel panel-default">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-exchange"></i> Mailchimp
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-exchange"></i> Mailchimp</div>
                                <div class="panel-sub-desc">
                                    <p>
                                        Connect your Mailchimp account to automatically sync your supporters from Givecloud to a specified list in Mailchimp.
                                    </p>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-8">
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4"
                                         style="margin-top:20px">
                                            <button
                                                id="hotglue_connected"
                                                type="button"
                                                data-hotglue-show-when-connected
                                                class="{{ $isConnected ? '' : 'hide' }} inline-flex items-center rounded-md border border-green-500 bg-white px-6 py-3 text-base font-medium text-green-500 shadow-sm hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                <i class="-ml-1 mr-3 fa fa-check"></i>
                                                Connected
                                            </button>
                                            <button
                                                id="hotglue_disconnected"
                                                data-hotglue-hide-when-connected class="{{ $isConnected ? 'hide' : '' }} btn btn-primary btn-lg">
                                                Connect Mailchimp
                                            </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-hotglue-show-when-connected class="panel panel-default {{ ! $isConnected ? 'hide' : '' }}">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-lock"></i> Sync Supporters
                    </div>
                    <div class="panel-body" style="padding-bottom:15px">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-sync"></i> Sync Supporters</div>
                                <div class="panel-sub-desc">
                                    <p>If this is the first time you've connected Mailchimp, you can use this option to sync your existing supporters from Givecloud to your specified Mailchimp list.</p>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-8">
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4">
                                        <button
                                            type="submit"
                                            class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                        >
                                            Sync existing supporters
                                        </button>
                                    </div>
                                </div>
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


