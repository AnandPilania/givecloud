
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
        <a role="tab" data-toggle="tab" data-target="#hook-delivery-request-{{ $delivery->id }}">
            Request
        </a>
    </li>
    <li role="presentation">
        <a role="tab" data-toggle="tab" data-target="#hook-delivery-response-{{ $delivery->id }}">
            Response
            <span class="badge alert-{{ $delivery->res_status == 200 ? 'success' : 'danger' }}">{{ $delivery->res_status }}</span>
        </a>
    </li>
    <li class="hook-delivery-actions">
        <button type="button" name="redeliver" class="btn btn-sm btn-neutral">
            <i class="fa fa-refresh" aria-hidden="true"></i>
            Redeliver
        </button>
        <span class="hook-delivery-completed-time">
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            Completed in {{ numeral($delivery->completed_in) }} seconds
        </span>
    </li>
</ul>
<div class="tab-content">
    <div id="hook-delivery-request-{{ $delivery->id }}" role="tabpanel" class="tab-pane active">
        <strong>Headers</strong>
        <pre>{!! preg_replace('/^(.*?:)/mu', '<strong>$1</strong>', e($delivery->req_headers)) !!}</pre>
        <strong>Body</strong>
        <pre>{{ $delivery->payload_json_pretty }}</pre>
    </div>
    <div id="hook-delivery-response-{{ $delivery->id }}" role="tabpanel" class="tab-pane">
        <strong>Headers</strong>
        <pre>{!! preg_replace('/^(.*?:)/mu', '<strong>$1</strong>', e($delivery->res_headers)) !!}</pre>
        <strong>Body</strong>
        <pre>{{ $delivery->res_body }}</pre>
    </div>
</div>
