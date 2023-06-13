
@extends('layouts.app')
@section('title', 'Webhooks')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Webhooks

            <div class="pull-right">
                <a href="{{ route('backend.settings.hooks.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Webhook</a>
            </div>
        </h1>
        <p>
            Webhooks allow external services to be notified when certain events happen within your site.
            When the specified events happen, we’ll send a POST request to each of the URLs you provide.
            Learn more in our Webhooks Guide.
        </p>
    </div>
</div>

<div class="row" style="margin-top:20px">
    <div class="col-lg-12">

        <div class="list-group">
        @foreach($hooks as $hook)
            <div id="hook-{{ $hook->id }}" class="list-group-item clearfix" style="line-height:30px">

                @if (!$hook->active)
                    <a href="/jpanel/settings/hooks/{{ $hook->id }}#deliveries" style="color:#aaa"><i class="fa fa-circle" aria-hidden="true"></i></a>
                @elseif (0)
                    <a href="/jpanel/settings/hooks/{{ $hook->id }}#deliveries" style="color:#a94442"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
                @elseif (0)
                    <a href="/jpanel/settings/hooks/{{ $hook->id }}#deliveries" style="color:#3c763d"><i class="fa fa-check" aria-hidden="true"></i></a>
                @else
                    <a href="/jpanel/settings/hooks/{{ $hook->id }}#deliveries" style="color:#3c763d"><i class="fa fa-circle" aria-hidden="true"></i></a>
                @endif

                &nbsp;

                <a href="/jpanel/settings/hooks/{{ $hook->id }}">{{ $hook->payload_url }}</a>

                <div class="btn-group pull-right" role="group">
                    <a class="btn btn-sm btn-neutral" href="/jpanel/settings/hooks/{{ $hook->id }}"><strong>Edit</strong></a>
                    <button type="button" onclick="j.hooks.delete({{ $hook->id }});" class="btn btn-sm btn-neutral btn-text-danger"><strong>Delete</strong></button>
                </div>

            </div>
        @endforeach
        </div>

    </div>
</div>



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
@endsection
