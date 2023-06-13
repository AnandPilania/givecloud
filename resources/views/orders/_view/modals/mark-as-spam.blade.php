<div class="modal fade modal-danger" id="mark-as-spam-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa-regular fa-shield-xmark"></i> Mark As Spam</h4>
            </div>
            <form method="post" action="{{ route('backend.orders.spam', $order) }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to @if($order->is_paid) refund and @endif mark this contribution and any related supporters and payments as spam?
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger"><i class="fa-regular fa-shield-xmark"></i> Mark As Spam @if($order->is_paid) &amp; Refund @endif</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
