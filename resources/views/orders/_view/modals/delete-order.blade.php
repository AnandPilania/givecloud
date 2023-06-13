<div class="modal fade modal-danger" id="delete-order-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-trash"></i> Delete {{ $order->is_test ? 'Test ' : ''}}Contribution</h4>
            </div>

            @if($order->is_trashable)
            <form method="post" action="{{ route('backend.orders.destroy', $order) }}">
                @csrf
                <div class="modal-body">

                    <div class="alert alert-danger">
                        <i class="fa fa-fw fa-exclamation-triangle"></i> <strong>This is a permanent action and cannot be undone.</strong> Only delete contributions created in error. It's always better to void or refund a contribution.
                    </div>

                    <p>Are you sure you want to delete <strong>{{ $order->is_test ? 'Test ' : ''}}Contribution #{{ $order->invoicenumber }}</strong> for <strong>{{ money($order->totalamount, $order->currency) }}</strong>.</p>

                        @php
                        $delete_warnings = [];
                        if ($order->taxReceipt) {
                            $delete_warnings[] = "Tax Receipt <strong>{$order->taxReceipt->number}</strong> will be deleted.";
                        }
                        if ($tribute_count = $order->items->reject(function($i){ return !$i->tribute; })->count()) {
                            $delete_warnings[] = "<strong>({$tribute_count})</strong> Tributes will be deleted.";
                        }
                        if ($rpp_count = $order->items->reject(function($i){ return !$i->recurringPaymentProfile; })->count()) {
                            $delete_warnings[] = "<strong>({$rpp_count})</strong> Recurring Payment Profiles will be deleted.";
                        }
                        if ($group_count = $order->items->reject(function($i){ return $i->groupAccount; })->count()) {
                            $delete_warnings[] = "<strong>({$group_count})</strong> Groups/Memberships assignments will be deleted.";
                        }
                        if ($order->billingemail) {
                            $delete_warnings[] = sprintf('Any emails sent to <strong>%s</strong> will now contain broken links.', e($order->billingemail));
                        }
                        if ($order->member) {
                            $delete_warnings[] = sprintf("The supporter <strong>'%s' will NOT be deleted</strong>. You'll need to delete this manually afterwards.", e($order->member->display_name));
                        }
                        if ($order->payments->reject(function($p){ return in_array($p->type, ['cash','cheque','unknown']); })->count() > 0) {
                            $delete_warnings[] = "Any test transactions created in the payment gateway <strong>cannot and will NOT be deleted</strong>.";
                        }
                        @endphp

                        @if (count($delete_warnings) > 0)
                            <ul class="fa-ul">
                            @foreach($delete_warnings as $warn)
                                <li><i class="fa fa-li fa-exclamation-triangle"></i> {!! $warn !!}</li>
                           @endforeach
                            </ul>
                        @endif

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Permanently Delete</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
            @else
            <div class="modal-body">
                <p>This contribution cannot be deleted.</p>

                <ul class="fa-ul">
                    @foreach($order->trashable_messages as $msg)
                        <li><i class="fa fa-li fa-fw fa-times"></i> {{ $msg }}</li>
                    @endforeach
                </ul>

                @if (user()->can_live_chat)
                    <p>If you believe you should still be able to delete this contribution, please <a href="javascript:Intercom('showNewMessage');">chat with support</a>.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
            @endif
        </div>
    </div>
</div>
