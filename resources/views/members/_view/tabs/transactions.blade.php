<div role="tabpanel" class="tab-pane fade in" id="transactions">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-money fa-fw"></i> Recurring History
        </div>

        <div class="panel-body">
            <div class="table-responsive">
                <table id="memberRecurringPaymentProfiles" class="table table-striped table-hover responsive">
                    <thead>
                        <tr>
                            <th colspan="2">Date</th>
                            <th>Profile</th>
                            <th>Method</th>
                            <th class="text-center">Payment status</th>
                            <th>Payment response</th>
                            <th>Reference</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member->transactions as $transaction)
                            <tr class="
                                @if (!$transaction->is_payment_accepted) text-danger @endif
                                @if ($transaction->is_refunded) text-muted @endif">
                                <td width="16">
                                    <a href="#" class="ds-txn" data-txn-id="{{ $transaction->id }}">
                                        <i class="fa fas fa-search"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ toLocalFormat($transaction->order_time) }}
                                    <small class="text-muted">
                                        {{ toLocal($transaction->order_time)->format('g:iA') }}
                                    </small>
                                </td>
                                <td>
                                    {{ $transaction->recurringPaymentProfile->description }}
                                    <small>
                                        <a href="{{ route('backend.recurring_payments.show', $transaction->recurringPaymentProfile->profile_id) }}">
                                            {{ $transaction->recurringPaymentProfile->profile_id }}
                                        </a>
                                    </small>
                                </td>
                                <td>
                                    @if ($transaction->paymentMethod)
                                        <i class="fa fas {{ $transaction->paymentMethod->fa_icon }}"></i>
                                        {{ $transaction->paymentMethod->display_name }}
                                    @endif
                                </td>
                                <td class="text-center">{{ $transaction->payment_status }}</td>
                                <td>{{ $transaction->reason_code }}</td>
                                <td>{{ $transaction->transaction_id }}</td>
                                <td class="text-right">
                                    @if ($transaction->is_refunded)
                                        <i class="fa fas fa-reply"></i>
                                    @endif
                                    {{ money($transaction->amt, $transaction->currency_code) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
