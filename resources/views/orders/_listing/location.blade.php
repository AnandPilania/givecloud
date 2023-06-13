<div class="flex items-center">
    <img class="mr-2 w-4 h-4" src="{{ flag($order->billingcountry) }}" alt="{{ $order->billingcountry  }}" />
    <div class="text-gray-900 font-bold">{{ trim(ucwords($order->billingcity) . ', ' . $order->billingstate, ', ') }}</div>
</div>

<div class="mt-1 text-gray-500">{{ cart_countries()[$order->billingcountry]}}</div>
