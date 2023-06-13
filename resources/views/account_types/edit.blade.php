
@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<form action="{{ $accountType->exists ? route('backend.supporter_types.update', $accountType->getKey()) : route('backend.supporter_types.store') }}" method="post">
@csrf

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            {{ $pageTitle }}

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></button>

                @if ($accountType->exists && ! $accountType->is_protected)
                    <button type="button" class="btn btn-danger" onclick="$.confirm('Are you sure you want to delete this supporter type?', function(){ window.location = '{{ route('backend.supporter_types.destroy', $accountType->getKey()) }}'; }, 'danger', 'fa-trash');"><i class="fa fa-trash"></i></button>
                @endif
                {{--<a href="/jpanel/tributes?type={{ tributeType.id }}" class="btn btn-info"><i class="fa fa-bar-chart"></i><span class="hidden-xs hidden-sm"> Sales</span></a> --}}
            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

<div class="row">

    <div class="col-sm-12">

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-user"></i> Supporter Type</div>
                    <div class="panel-sub-desc">
                        These are general settings specific to this supporter type.
                    </div>
                </div>

                <div class="form-horizontal">

                    <div class="form-group">
                        <label class="col-md-2 control-label">Name</label>
                        <div class="col-lg-4 col-md-5 col-sm-9">
                            <input type="text" class="form-control" autofocus name="name" value="{{ $accountType->name }}">
                            <small class="text-muted">The name of this supporter type.<br>(Ex: Volunteer or Business)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Type</label>
                        <div class="col-md-8">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="is_organization" @disabled($accountType->is_protected) @checked(!$accountType->is_organization) value="0"> <i class="fa fa-user fa-fw"></i> Individual
                                </label><br>
                                <small class="text-muted">This supporter type is for an individual.</small>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" name="is_organization" @disabled($accountType->is_protected) @checked($accountType->is_organization) value="1"> <i class="fa fa-building fa-fw"></i> Organization
                                </label><br>
                                <small class="text-muted">This supporter type is for an organization.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Sequence</label>
                        <div class="col-lg-2 col-md-3 col-sm-3">
                            <input type="text" class="form-control" autofocus name="sequence" value="{{ $accountType->sequence }}">
                            <small class="text-muted">The sequence in dropdown list of supporter types.</small>
                        </div>
                    </div>

                    <div class="form-group {% if accountType.is_protected %}hide{% endif %}">
                        <label class="col-md-2 control-label">Visibility</label>
                        <div class="col-md-8">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="on_web" @checked($accountType->on_web) value="1"> Show On Website
                                </label>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" name="on_web" @checked(!$accountType->on_web) value="0"> Hide On Website (Internal Only)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group {% if !accountType.on_web %}hide{% endif %}">
                        <label class="col-md-2 control-label">Is Default</label>
                        <div class="col-md-8">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="is_default" @checked($accountType->is_default) value="1"> This is the supporter type that will be selected by default on your checkout pages
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if (user()->can('admin.dpo') and dpo_is_enabled())
            <div class="panel panel-info">
                <div class="panel-heading">
                    <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> DonorPerfect Integration

                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-gear fa-fw"></i> <i class="fa fa-caret-down"></i>
                        </button>
                        <ul class="dropdown-menu slidedown">
                            <li><a href="#" class="dpo-codes-refresh"><i class="fa fa-refresh fa-fw"></i> Refresh DonorPerfect Codes</a></li>
                        </ul>
                    </div>
                </div>
                <div class="panel-body">

                    <div class="form-horizontal">

                        <div class="form-group">
                            <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                Donor Type<br>
                                <small class="text-muted">Choose the corresponding DonorPerfect Donor Type that this supporter type maps to.</small>
                            </label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control dpo-codes" data-code="DONOR_TYPE" name="dp_code" id="dp_code" value="{{ $accountType->dp_code }}" maxlength="11" />
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        @endif

    </div>

</div>


</form>
@endsection
