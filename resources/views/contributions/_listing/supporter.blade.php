<div class="font-medium text-gray-900 font-bold">{{ $contribution->display_name ?: 'Anonymous Donor' }}</div>
<div class="text-gray-500 text-sm">Contribution #{{ $contribution->order ? $contribution->order->client_uuid : $contribution->transactions[0]->transaction_id }}
    @if($contribution->is_test)
        <span class="ml-2 inline-flex items-center rounded-full border border-yellow-800 px-2 py-0 text-xs font-medium text-yellow-800">Test</span>
    @endif
</div>
