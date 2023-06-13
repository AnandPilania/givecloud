<div class="@if($contribution->total_refunded > 0) text-gray-500 @else text-gray-900 @endif @if(isGivecloudPro()) font-bold @endif">
    {{ money($contribution->total, $contribution->currency_code)}} <span class="text-gray-500 font-normal text-xs">{{ $contribution->currency_code }}</span>
    @if(isGivecloudExpress())
        <span class="text-gray-500 font-normal text-xs">({{ money($contribution->dcc_amount, $contribution->currency_code)}} {{ $contribution->currency_code }})</span>
    @endif
</div>
<div class="text-gray-500">
    @if($contribution->payment_type === 'card')
        {{ ucfirst($contribution->payment_card_brand) }}
    @else
        {{ ucfirst($contribution->payment_type) }}
    @endif
    {{ $contribution->payment_card_last4 ?? '' }}
</div>
