
@extends('layouts.app')
@section('title', 'Manage Webhook')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Manage webhook

            <div class="pull-right">
                <button type="button" onclick="j.hooks.save({{ $hook->id }});" class="btn btn-success">Update webhook</button>
                <button type="button" onclick="j.hooks.delete({{ $hook->id }});" class="btn btn-neutral btn-text-danger">Delete webhook</button>
            </div>
        </h1>
        <p>
            We’ll send a POST request to the URL below with details of any subscribed events.
            More information can be found in our developer documentation.
        </p>
    </div>
</div>

<form id="hook-form" class="list-group" style="margin-top:20px" action="post">

    <div class="list-group-item">
        <div class="row" style="margin-top:20px;margin-bottom:10px">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">Payload URL <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" autofocus name="payload_url" required value="{{ $hook->payload_url }}" placeholder="https://example.com/postreceive" maxlength="255">
                </div>

                <div class="form-group row">
                    <div class="col-sm-7">
                        <label class="control-label">Content Type</label>
                        <select name="content_type" class="form-control">
                            <option {{ volt_selected('application/json', $hook->content_type) }}>application/json</option>
                            <option disabled>application/x-www-form-urlencoded</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">Secret</label>
                    <input type="password" class="form-control" name="secret" value="{{ $hook->secret }}" maxlength="255">
                </div>
            </div>
        </div>
    </div>

    <div class="list-group-item">
        @if ($hook->insecure_ssl)
            <div class="clearfix alert alert-warning" style="line-height:30px">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Warning: SSL verification is not enabled for this hook.
                <div class="pull-right">
                    <button type="button" onclick="j.hooks.enableInsecureSSL({{ $hook->id }});" class="btn btn-sm btn-neutral"><strong>Enable SSL verification</strong></button>
                </div>
            </div>
        @else
            <div class="clearfix text-muted">
                <i class="fa fa-lock" aria-hidden="true"></i> By default, we verify SSL certificates when delivering payloads.
                <div class="pull-right">
                    <button type="button" onclick="j.hooks.disableInsecureSSL({{ $hook->id }});" class="btn btn-sm btn-neutral btn-text-danger">Disable SSL verification</button>
                </div>
            </div>
        @endif
        <div class="form-group" style="margin:20px auto">
            <strong>Which events would you like to trigger this webhook?</strong>
            @foreach (\Ds\Models\HookEvent::EVENTS as $eventName => $eventEnabled)
                @php
                    $matchingEvents = $hook->events->filter(function ($event) use ($eventName) {
                        return $event->name === $eventName;
                    });
                @endphp
                @continue (!$eventEnabled && $matchingEvents->isEmpty())

                <div class="checkbox">
                    <label>
                        <input
                            type="checkbox"
                            name="events[]"
                            value="{{ $matchingEvents->isNotEmpty() ? $matchingEvents->first()->getKey() : $eventName }}"
                            @disabled(!$eventEnabled)
                            @checked($matchingEvents->isNotEmpty())>
                        <code>{{ $eventName }}</code> @if($eventName === 'contributions_paid')<span class="text-sm">Includes all contributions (one-time & recurring)</span>@endif
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="list-group-item">
        <div class="form-group" style="margin:20px auto">
            <label class="control-label">Active</label>
            <div class="checkbox" style="margin:0">
                <label>
                    <input type="checkbox" name="active" value="1" {{ volt_checked($hook->active, true) }}> We will deliver event details when this hook is triggered.
                </label>
            </div>
        </div>
    </div>

</form>


@if (count($deliveries))

    <div id="hook-deliveries" class="list-group">
        <div class="list-group-item list-group-heading">
            Recent Deliveries
        </div>

        @foreach($deliveries as $delivery)
            <div id="hook-delivery-{{ $delivery->id }}" data-hook-id="{{ $hook->id }}" data-delivery-id="{{ $delivery->id }}" class="list-group-item hook-delivery">
                <div class="clearfix">
                    <span class="hook-delivery-status">
                        @if ($delivery->res_status === 200)
                            <i style="color:#3c763d" class="fa fa-check" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Success"></i>
                        @else
                            <i style="color:#a94442" class="fa fa-exclamation-triangle" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Invalid HTTP Response: {{ $delivery->res_status }}"></i>
                        @endif
                    </span>
                    <a href="#details" class="hook-delivery-guid">
                        <i class="fa fa-archive" aria-hidden="true"></i> {{ $delivery->guid }}
                    </a>
                    <div class="pull-right">
                        <span class="hook-delivery-time">
                            <time title="{{ fromUtcFormat($delivery->delivered_at, 'Y-m-d h:i:s e') }}">{{ toLocalFormat($delivery->delivered_at, 'Y-m-d h:i:s') }}</time>
                        </span>
                        <button type="button" class="ellipsis-expander">…</button>
                    </div>
                </div>
                <div class="hook-delivery-loading" style="display:none">
                    <i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="hook-delivery-details" style="display:none"></div>
            </div>
        @endforeach
    </div>

@endif


<div class="modal fade modal-info" tabindex="-1" id="hook-delete-modal" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background:#000">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Delete webhook?</h4>
            </div>
            <div class="modal-body alert-warning">
                <strong>This action cannot be undone. Future events will no longer be delivered to this webhook</strong> <em class="payload-url"></em>.
            </div>
            <div class="modal-footer" style="text-align:left">
                <button type="button" name="delete" class="btn btn-danger"><strong>Yes, delete webhook</strong></button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-info" tabindex="-1" id="hook-ssl-modal" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background:#000">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Are you sure?</h4>
            </div>
            <div class="modal-body alert-warning">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                <strong>Warning: Disabling SSL verification has serious implications.</strong>
            </div>
            <div class="modal-body">
                SSL verification helps ensure that hook payloads are delivered to your URL endpoint securely,
                keeping your data away from prying eyes. Disabling this option is not recommended.
            </div>
            <div class="modal-footer" style="text-align:left">
                <button type="button" name="delete" class="btn btn-danger"><strong>I understand my webhooks may not be secure</strong></button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-info" tabindex="-1" id="hook-redeliver-modal" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background:#fff">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" style="color:#333">Redeliver Payload?</h4>
            </div>
            <div class="modal-body alert-warning">
                The payload will be delivered to <em>{{ $hook->payload_url }}</em> using the current webhook configuration.
            </div>
            <div class="modal-footer" style="text-align:left">
                <button type="button" name="redeliver" class="btn btn-neutral"><strong>Yes, redeliver this payload</strong></button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
