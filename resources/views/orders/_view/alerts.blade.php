@if($order->trashed())
    <div class="alert alert-danger">
        <i class="fa fa-trash fa-fw"></i>
        Deleted by {{  $order->deletedBy->full_name }} on {{ toLocalFormat($order->deleted_at, 'M j, Y \a\t g:ia') }}
        <small>({{ toLocalFormat($order->deleted_at, 'humans') }})</small>.
    </div>
@endif

@if ($order->is_spam)
    <div class="alert alert-danger">
        <i class="fa-regular fa-shield-xmark"></i>
        Marked as Spam by {{ $order->markedAsSpamBy->full_name ?? 'Unknown' }} on {{ toLocalFormat($order->marked_as_spam_at, 'M j, Y \a\t g:ia') }}
        <small>({{ toLocalFormat($order->marked_as_spam_at, 'humans') }})</small>.
    </div>
@endif

@if (request('re') == '1')
    <div class="alert alert-success">
        <i class="fa fa-check fa-fw"></i> Customer notified successfully.
    </div>
@elseif (request('re') == '0')
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle fa-fw"></i> Customer notification failed.
    </div>
@endif

@if (request('ss'))
    <div class="alert alert-success"><i class="fa fa-check fa-fw"></i> </div>
@elseif (request('sf'))
    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle fa-fw"></i>{{ request('sf') }}</div>
@endif
