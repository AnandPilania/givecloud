@if(isGivecloudPro())
    @php
        $items = $contribution->order->items ?? collect(data_get($contribution, 'transactions.*.recurringPaymentProfile.order_item', []));
    @endphp
    <div class="avatar-group">
        @foreach($items->unique('productinventoryid')->take(4) as $item)
            <div class="avatar-xl" style="background-image:url('{{ $item->image_thumb }}');"></div>
        @endforeach
        @if($items->count() > $items->unique('productinventoryid')->count())
            @foreach($items->take(4 - $items->unique('productinventoryid')->count()) as $item)
                <div class="avatar-xl" style="background-image:url('{{ $item->image_thumb }}');"></div>
            @endforeach
        @endif;
        <div class="avatar-badge">{{ $items->count() }}</div>
    </div>
@else
    <div class="flex items-center justify-center">
        <div class="h-10 w-10 flex-shrink-0">
            @if($contribution->display_name === 'Anonymous Donor')
                <span id="order:{{ $contribution->id  }}"  class="inline-block h-10 w-10} overflow-hidden rounded-full bg-gray-100">
                    <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </span>
            @else
                <span id="order:{{ $contribution->id  }}" data-initials="{{ \Illuminate\Support\Str::initials($contribution->display_name) }}" class="hidden avatar inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-500">
                  <span class="font-bold leading-none text-white">{{ \Illuminate\Support\Str::initials($contribution->display_name) }}</span>
                </span>
            @endif
            <img class="h-10 w-10 rounded-full" src="{{ optional($contribution->member)->avatar ?? gravatar($contribution->email ?? '') }}" alt="{{ $contribution->display_name }}"
                 onerror="this.classList.add('hidden'); document.getElementById('order:{{ $contribution->id }}').classList.remove('hidden')">
        </div>
    </div>
@endif

