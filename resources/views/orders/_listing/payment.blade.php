<div class="@if($order->refunded_amt > 0) text-gray-500 @else text-gray-900 @endif @if(isGivecloudPro()) font-bold @endif">
    {{ money($order->totalamount, $order->currency)}}
    @if(isGivecloudExpress())
        <span class="text-gray-500 font-normal text-xs">({{ money($order->dcc_total_amount, $order->currency)}})</span>
    @endif
</div>
<div class="text-gray-500">
    {{ $order->payment_type_formatted }}
    @if (in_array($order->payment_type_formatted, ['Visa', 'MasterCard', 'Amex', 'Discover']))
        {{ $order->billingcardlastfour }}
    @endif
</div>
