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
                            <i class="fa fa-lock"></i> Login
                        </div>
                        <div class="panel-body">

                            <div class="row">

                                <div class="col-sm-6 col-md-4 hidden-xs">
                                    <div class="panel-sub-title"><i class="fa fa-lock"></i> Consumer Keys</div>
                                    <div class="panel-sub-desc">
                                        <p>Create a Connected App in Salesforce and enter your keys here.</p>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-md-8">
                                    <div class="form-group">
                                        <label for="name" class="col-md-4 control-label">Consumer Key</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="salesforce_consumer_key" name="salesforce_consumer_key" value="{{ sys_get('salesforce_consumer_key') }}" />
                                            <small class="form-text text-muted">This can be found in <em>App Manager -> View Connected App</em> under the API settings section</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="col-md-4 control-label">Consumer Secret</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="salesforce_consumer_secret" name="salesforce_consumer_secret" value="{{ sys_get('salesforce_consumer_secret') }}" />
                                            <small class="form-text text-muted">This can be found in <em>App Manager -> View Connected App</em> under the API settings section</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="col-md-4 control-label">Callback URI</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <input id="callbackURI" type="text" class="form-control" style="height:auto" readonly value="{{ secure_site_url(route('backend.settings.integrations.salesforce.callback', [], false), true) }}">
                                                <span class="input-group-btn input-group-append">
                                                    <button class="btn btn-default btn-outline-secondary" type="button"
                                                            onclick="copyUrl()"
                                                    >Copy</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="mt-3 pull-right btn btn-success"><i class="fa fa-check"></i> Save</button>

                                </div>
                            </div>
                        </div>
                    </div>


                <div class="panel panel-default">
                    <div class="panel-heading visible-xs">
                        <i class="fa fa-exchange"></i> Connect Salesforce
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4 hidden-xs">
                                <div class="panel-sub-title"><i class="fa fa-exchange"></i> Connect Salesforce</div>
                                <div class="panel-sub-desc">
                                    <p>
                                        By granting us third party access permissions, we're able to perform
                                        Salesforce API operations on your behalf.
                                    </p>
                                    @if($token)
                                        <p class="mt-2">
                                            <a class="btn btn-danger btn-sm" href="{{ route('backend.settings.integrations.salesforce.disconnect') }}">
                                                <i class="fa fa-times"></i> Disconnect
                                            </a>
                                            <a class="btn btn-sm btn-default" href="{{route('backend.settings.integrations.salesforce.test')}}">
                                                <i class="fa fa-check-square-o"></i> Test Connection
                                            </a>&nbsp;
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-8">
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4" style="margin-top:20px">
                                        @if(! $token )
                                            @if(! sys_get('salesforce_consumer_key') || ! sys_get('salesforce_consumer_secret') )
                                            <div class="alert alert-warning">
                                                <div>Enter your Customer Key and Customer Secret to be able to connect to Salesforce.</div>
                                            </div>
                                            @endif
                                            <a id="salesforce_connect"
                                               class="btn btn-primary btn-lg {{ ! sys_get('salesforce_consumer_key') || ! sys_get('salesforce_consumer_secret') ? 'disabled' : ''   }}"
                                               href="{{ route('backend.settings.integrations.salesforce.connect') }}">
                                                Connect Salesforce
                                            </a>
                                        @else
                                        <div>
                                            <span class="text-lg text-success">
                                                <i class="fa fa-check"></i> Connected
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- /.form-horizontal -->

        </div></div>

</form>
    <script>
        function copyUrl(){
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(document.getElementById('callbackURI').value).select();
            document.execCommand('copy');
            $temp.remove();
            toastr['success']('Copied!');
        }
    </script>
@endsection

