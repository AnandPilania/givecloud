@extends('layouts.app')
@section('title', 'Integrations')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Integrations

                <div class="pull-right">
                    <a href="https://help.givecloud.com/en/collections/931194-integrations" target="_blank" class="btn btn-default"><i class="fa fa-book"></i> Getting Started</a>
                </div>
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4 col-md-3">
            <!--<div class="input-group input-group-lg input-group-transparent bottom-gutter">
                <div class="input-group-addon"><i class="fa fa-search"></i></div>
                <input type="search" class="form-control setting-search" placeholder="Search...">
                <div class="input-group-btn"><button class="btn btn-default reset-search" type="button"><i class="fa fa-times"></i></button></div>
            </div>-->

            <ul class="list-group" role="tablist" data-tabs="tabs">
                <a href="#all" role="tab" data-filter="all" data-all="1" class="active list-group-item stop-search">All Integrations <span class="badge">{{ $integrations->count() }}</span></a>
                <a href="#installed" role="tab" data-filter="installed" data-installed="1" class="list-group-item stop-search">Installed <span class="badge">{{ $installed_count }}</span></a>
            </ul>

            <ul class="list-group" role="tablist" data-tabs="tabs">
                @foreach($categories as $category)
                    <a href="#{{ \Illuminate\Support\Str::slug($category,'-') }}" role="tab" data-filter="category" data-category="{{ \Illuminate\Support\Str::slug($category,'-') }}" class="list-group-item stop-search">{{ $category }}</a>
                @endforeach
            </ul>

        </div>

        <div class="col-sm-8 col-md-9">
            <!--<div id="customizations-pane">
                <div class="search-status text-center text-muted hide">
                    <i class="fa fa-search fa-3x bottom-gutter"></i><br>
                    Type something to search
                </div>
            </div>-->

            <style>
                .panel.panel-default.panel-integration .panel-body { padding-top:0px; }
                .panel-integration .panel-img { width:100%; height:auto; border-top-right-radius:10px; border-top-left-radius:10px; }
                .panel-integration .panel-category { font-size:11px; color:#f00; line-height: 13px; }
                .panel-integration .panel-title { margin-bottom:12px; font-weight:bold; }
                [data-available='0'] .panel-integration .panel-img { opacity:0.3; transition: opacity 0.3s; }
                [data-available='0'] .panel-integration:hover .panel-img { opacity:1; transition: opacity 0.3s; }
            </style>

            <div class="row">
                @foreach($integrations as $integration)
                    <div class="col-sm-6 col-md-4" data-all="1" data-available="{{ $integration->available ? 1 : 0 }}" data-installed="{{ $integration->installed ? 1 : 0 }}" data-category="{{ \Illuminate\Support\Str::slug($integration->category,'-') }}">
                        <div class="panel panel-default panel-integration">
                            @if($integration->installed)
                                <div class="flags">
                                    <div class="flag flag-success"><i class="fa fa-check"></i> Installed</div>
                                </div>
                            @endif
                            <img class="panel-img" src="{{ $integration->getImageSrc() }}">
                            <div class="panel-body" data-mh="integrations">
                                <p><small class="text-info">{{ $integration->category }}</small></p>
                                <h2 class="panel-title">{{ $integration->name }}
                                    @if($integration->isDeprecated())
                                        <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Deprecated</span>
                                    @endif
                                </h2>
                                <p class="panel-text">{{ $integration->description }}</p>
                            </div>
                            <div class="panel-body">
                                @if($integration->help_url)
                                    <a href="{{ $integration->help_url }}" target="_blank" class="btn btn-outline btn-default pull-right"><i class="fa fa-book"></i> Help</a>
                                @endif
                                @if(!$integration->available)
                                    @if(user()->can_live_chat)
                                    <a class="btn btn-info" href="javascript:Intercom('showNewMessage', 'I\'d like access to your {{ $integration->name }} integration.');">Request</a>
                                    @endif
                                @elseif($integration->installed)
                                    <a href="{{ $integration->config_url }}" class="btn btn-outline btn-primary"><i class="fa fa-gear"></i> Settings</a>
                                @elseif(!$integration->installed)
                                    <a href="{{ $integration->config_url }}" class="btn btn-primary"><i class="fa fa-plus"></i> Install</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <script>
        spaContentReady(function() {
            $('[data-filter]').click(function(ev){
                var type = $(this).data('filter'),
                    data = $(this).data(type);

                $('.list-group-item').removeClass('active');
                $(this).addClass('active');
                $('[data-'+type+']').not('[data-filter]').css('display','none');
                $('[data-'+type+'='+data+']').css('display','block');
            });
        });
    </script>
@endsection

