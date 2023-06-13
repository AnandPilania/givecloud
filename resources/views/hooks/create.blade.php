
@extends('layouts.app')
@section('title', 'Manage Webhooks')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Manage webhook

            <div class="pull-right">
                <button type="button" onclick="j.hooks.add();" class="btn btn-success">Add webhook</a>
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
                    <input type="text" class="form-control" autofocus name="payload_url" required placeholder="https://example.com/postreceive" maxlength="255">
                </div>

                <div class="form-group row">
                    <div class="col-sm-7">
                        <label class="control-label">Content Type</label>
                        <select name="content_type" class="form-control">
                            <option selected>application/json</option>
                            <option disabled>application/x-www-form-urlencoded</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">Secret</label>
                    <input type="password" class="form-control" name="secret" maxlength="255">
                </div>
            </div>
        </div>
    </div>

    <div class="list-group-item">
        <div class="form-group" style="margin:20px auto">
            <strong>Which events would you like to trigger this webhook?</strong>
            @foreach (\Ds\Models\HookEvent::getEnabledEvents() as $eventName)
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="events[]" value="{{ $eventName }}">
                        <code>{{ $eventName }}</code>
                        @if($eventName === 'contributions_paid')<span class="text-sm">(Includes all contributions (one-time & recurring)</span>@endif
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
                    <input type="checkbox" name="active" value="1" checked> We will deliver event details when this hook is triggered.
                </label>
            </div>
        </div>
    </div>

</form>
@endsection
