@if($order->is_spam)
    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Spam/Fraud</span>
@endif

@if($order->is_refunded)
    <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-medium text-black">Refunded</span>
@endif

@if($order->is_partially_refunded)
    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Partially Refunded</span>
@endif

@if($order->warning_count > 0)
    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Risk Warning</span>
@endif

@if($order->is_fulfillable && ! $order->iscomplete)
    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Unfulfilled</span>
@endif

@if (dpo_is_enabled() && $order->is_unsynced)
    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Unsynced</span>
@endif

@if($order->refunded_amt <= 0 && $order->warning_count <= 0 && $order->iscomplete && !$order->is_unsynced)
    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">OK</span>
@endif
