<div class="@if($contribution->total_refunded > 0) text-gray-500 @else text-gray-900 @endif font-bold">
    {{ money(($contribution->total - $contribution->total_refunded - $contribution->dcc_amount) * $contribution->functional_exchange_rate, $contribution->functional_currency_code)}} {{ $contribution->functional_currency_code }}
</div>
@php
    $items = $contribution->order->items ?? collect(data_get($contribution, 'transactions.*.recurringPaymentProfile.order_item', []));
@endphp

<div class="text-gray-500">
    @if($items->filter(fn ($item) => $item->gl_code )->count() >1)
        {{ $items->filter(fn ($item) => $item->gl_code )->count() }} Funds
    @elseif($items->first()->gl_code)
        {{ $items->first()->gl_code }}
    @endif
</div>
