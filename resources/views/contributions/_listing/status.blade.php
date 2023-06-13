@if($contribution->is_spam)
    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Spam/Fraud</span>
@endif

@if($contribution->is_refunded)
    <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-medium text-black">Refunded</span>
@endif

@if($contribution->is_partially_refunded)
    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Partially Refunded</span>
@endif

@if($contribution->has_warnings)
    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Risk Warning</span>
@endif

@if($contribution->is_fulfillable && ! $contribution->is_fulfilled && $contribution->payment_status !== 'failed')
    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Unfulfilled</span>
@endif

@if ($contribution->is_unsynced && $contribution->payment_status !== 'failed')
    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Unsynced</span>
@endif

@if ($contribution->payment_status === 'failed')
    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Failed</span>
@endif

@if($contribution->total_refunded <= 0
    && $contribution->is_fulfilled
    && $contribution->payment_status !== 'failed'
    && ! $contribution->has_warnings
    && ! $contribution->is_unsynced
    )
    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">OK</span>
@endif
