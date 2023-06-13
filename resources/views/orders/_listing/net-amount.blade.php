<div class="@if($order->refunded_amt > 0) text-gray-500 @else text-gray-900 @endif font-bold">
    {{ money($order->subtotal, $order->currency)}}
</div>
<div class="text-gray-500">
    @if($order->items->filter(fn ($item) => $item->gl_code )->count() >1)
        {{ $order->items->filter(fn ($item) => $item->gl_code )->count() }} Funds
    @elseif($order->items->first()->gl_code)
        {{ $order->items->first()->gl_code }}
    @endif
</div>
