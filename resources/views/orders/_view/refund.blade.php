@if($order->refunded_at)
    @foreach(data_get($order, 'successfulPayments.0.successfulRefunds') as $refund)
        <div class="alert alert-danger">
            Refunded <strong>{{ money($refund->amount, $refund->currency) }}</strong> on <strong> {{ toLocalFormat($order->refunded_at, 'M j, Y') }}</strong><br>
            <small>{{ optional($refund->refundedBy)->full_name }} (Txn ID: {{ $refund->reference_number }})</small>
        </div>
   @endforeach
@endif
