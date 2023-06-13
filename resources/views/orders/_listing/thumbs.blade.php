@if(isGivecloudPro())
    <div class="avatar-group">
        @foreach($order->items->take(4) as $item)
            <div class="avatar-xl" style="background-image:url('{{ $item->image_thumb }}');"></div>
        @endforeach
        <div class="avatar-badge">{{ $order->items->count() }}</div>
    </div>
@else
    <div class="flex items-center">
        <div class="h-10 w-10 flex-shrink-0">
            <a href="{{ route('backend.orders.edit', $order->id) }}">
                @if($order->display_name === 'Anonymous Donor')
                    <span id="order:{{ $order->id  }}"  class="inline-block h-10 w-10 overflow-hidden rounded-full bg-gray-100">
                    <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </span>
                @else
                    <span id="order:{{ $order->id  }}" data-initials="{{ \Illuminate\Support\Str::initials($order->display_name) }}" class="hidden avatar inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-500">
                  <span class="font-bold leading-none text-white">{{ \Illuminate\Support\Str::initials($order->display_name) }}</span>
                </span>
                @endif
                <img class="h-10 w-10 rounded-full" src="{{ optional($order->member)->avatar ?? gravatar($order->email ?? '') }}" alt="{{ $order->display_name }}"
                     onerror="this.classList.add('hidden'); document.getElementById('order:{{ $order->id }}').classList.remove('hidden')">
            </a>
        </div>
@endif

