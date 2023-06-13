@extends('layouts.app')
@section('title', $integration->name)

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            {{ $integration->name }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-lg-8 col-lg-offset-2">

        <div class="form-horizontal">
            {{ app('flash')->output() }}

            <div class="panel panel-default">
                <div class="panel-heading visible-xs">
                    <i class="fa fa-exchange"></i> {{ $integration->name }}
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6 col-md-4 hidden-xs">
                            <div class="panel-sub-title"><i class="fa fa-exchange"></i> {{ $integration->name }}</div>
                            <div class="panel-sub-desc">
                                <p>
                                    By granting us third party access permissions, we're able to perform
                                    {{ $integration->name }} operations on your behalf.
                                </p>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-8">
                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-4"
                                     style="margin-top:20px">
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
                                            Connect {{ $integration->name }}
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
@endsection

@section('scripts')
    <script src="https://hotglue.xyz/widgetv2.js"></script>
    <script>
        spaContentReady(function(){
            j.hotglue(@json($config));
        });
    </script>
@endsection


