@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<form class="form-horizontal" action="{{ route('backend.settings.zapier.store') }}" method="post">
    @csrf

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                {{ $pageTitle }}

                <div class="pull-right">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check fa-fw"></i>
                        <span class="hidden-xs hidden-sm"> Save</span>
                    </button>
                </div>
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8 col-lg-offset-2">
            {!! app('flash')->output(); !!}

            <div class="panel panel-default">
                <div class="panel-heading visible-xs">
                    <i class="fa fa-gears"></i> {{ $pageTitle }}
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6 col-md-4 hidden-xs">
                            <div class="panel-sub-title"><i class="fa fa-gears"></i> {{ $pageTitle }}</div>
                            <div class="panel-sub-desc">
                                Zapier allows you to automate workflows between your apps and your Givecloud account.
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-8">

                            <div class="form-group">
                                <label for="meta1" class="col-md-4 control-label">Enable</label>
                                <div class="col-md-8">
                                    <input
                                        type="checkbox"
                                        class="switch"
                                        value="1"
                                        name="enabled"
                                        @checked(sys_get('zapier_enabled'))
                                        onchange="$('.zapier-enabled-options').toggleClass('hide', !$(this).is(':checked'))">
                                </div>
                            </div>

                            <div class="zapier-enabled-options {{ sys_get('zapier_enabled') ? '' : 'hide' }}" style="margin-top:30px">
                                <div class="form-group">
                                    <div>
                                        <strong>Step 1</strong><br>
                                        <a
                                            href="https://zapier.com/developer/public-invite/116260/6eddd08b237d767c9c0e2ab908195d77/"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-neutral"
                                            onclick="window.open(this.href, 'Zapier', 'height=600,width=500,resizable=1');">
                                            Get invited to Zapier Givecloud app
                                            <i class="fa fa-external-link text-xs ml-1"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <strong>Step 2</strong><br>
                                        <a
                                            href="https://zapier.com/engine/auth/start/App116260CLIAPI@1.0.0/"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-neutral"
                                            onclick="window.open(this.href, 'Zapier', 'height=500,width=500,resizable=1');">
                                            Connect your Givecloud account to Zapier
                                            <i class="fa fa-external-link text-xs ml-1"></i>
                                        </a>
                                        <br>
                                        <span class="text-info">
                                            <i class="fa fa-info-circle"></i>
                                            Your Givecloud domain is <strong>{{ $currentDomain }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
