@if(is_a($row->ledgerable, \Ds\Models\Transaction::class))
    <div>
        <a href="{{ route('backend.recurring_payments.show', $row->ledgerable->recurringPaymentProfile->profile_id) }}">
            # {{ $row->ledgerable->recurringPaymentProfile->profile_id }}
        </a>
    </div>
    <div class="text-gray-500">Recurring Payment</div>
@else
    <div>
        <a href="{{ route('backend.orders.edit', $row->ledgerable->id) }}">#{{ $row->ledgerable->invoicenumber }}</a>
    </div>
    <div class="text-gray-500">Contribution</div>
@endif
