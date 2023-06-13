<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-sm">
                <table class="min-w-full divide-y divide-gray-300">

                    <thead class="bg-gray-50">
                    <tr class="divide-x divide-gray-200">
                        <th scope="col" class="whitespace-nowrap py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Gateway</th>
                        @foreach($paymentMethods as $method => $total)
                        <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900">
                            {{ $method }}</th>
                        @endforeach
                        <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Total</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($totals as $gatewayKey => $gatewayData)
                    <tr class="divide-x divide-gray-200">
                        <td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                            {{ $gatewayNamesMap->get($gatewayKey) }}
                        </td>
                        @foreach($paymentMethods as $paymentMethod => $total)
                            <td class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-500">
                                @if($gatewayData->has($paymentMethod))
                                    {{ money($gatewayData->get($paymentMethod)) }}
                                @endif
                            </td>
                        @endforeach
                        <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-900">
                            {{ money($gatewayData->sum()) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 divide-x divide-gray-200">
                        <td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm text-bold text-gray-900 sm:pl-6">
                            Total
                        </td>
                        @foreach($paymentMethods as $total)

                        <td class="whitespace-nowrap px-2 py-2 text-sm text-bold text-gray-900">
                            @if($total > 0)
                                {{ money($total) }}
                            @endif
                        </td>
                        @endforeach
                        <td class="whitespace-nowrap px-2 py-2 text-sm text-bold text-gray-900">
                            {{ money(array_sum($paymentMethods)) }}
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
