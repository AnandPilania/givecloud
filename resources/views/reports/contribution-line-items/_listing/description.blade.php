@if ($row->sponsorship)
    <div class="text-gray-900">
        <a href="{{ route('backend.sponsorship.view', $row->sponsorship_id)  }}">
            {{ $row->sponsorship->display_name }}
        </a>
    </div>
    <div class="text-gray-500">
        Sponsorship
    </div>
@elseif ($membership = data_get($row, 'item.variant.membership'))
    <div class="text-gray-900">
        <a href="{{ route('backend.memberships.edit', $membership->getKey())  }}">
            {{ $membership->name }}
        </a>
    </div>
    <div class="text-gray-500">
        Membership
    </div>
@elseif (in_array($row->type, [\Ds\Enums\LedgerEntryType::LINE_ITEM, \Ds\Enums\LedgerEntryType::DCC]) && $row->item->description)
    <div class="text-gray-900">
        {{ $row->item->variant->variantname }}
    </div>
    <div class="text-gray-500">
        {{ $row->item->variant->product->name }}
    </div>
@elseif ($row->type === \Ds\Enums\LedgerEntryType::TAX)
    <div class="text-gray-900">
        {{ $row->order->items->map(function ($item) {
                return $item->taxes->implode('description');
            })->implode(', ') }}
    </div>
    <div class="text-gray-500">
        Taxes
    </div>
@elseif ($row->type === \Ds\Enums\LedgerEntryType::SHIPPING)
    <div class="text-gray-900">
       {{ $row->order->shipping_method_name }}
    </div>
    <div class="text-gray-500">
        Shipping
    </div>
@else
    <div class="text-gray-500">N/D</div>
@endif
