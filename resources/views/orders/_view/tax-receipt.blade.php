@if (sys_get('tax_receipt_pdfs') && user()->can('taxreceipt.view'))
<div class="panel panel-basic">
    <div class="panel-body">

        <div class="bottom-gutter-sm">
            <div class="panel-sub-title">Tax Receipt</div>
        </div>

        <div class="row">
            @if($order->taxReceipt)
                <div class="col-sm-8 stat">
                    <div class="stat-value-sm"><a href="/jpanel/tax_receipt/{{ $order->taxReceipt->id }}/pdf" target="_blank">{{ ($order->taxReceipt) ? $order->taxReceipt->number : 'N/A' }}</a></div>
                    <div class="stat-label">Tax Receipt</diV>
                </div>
                <div class="col-sm-4 stat">
                    <div class="stat-value-sm">{{ money($order->taxReceipt->amount, $order->taxReceipt->currency_code) }}</div>
                    <div class="stat-label">Amount</diV>
                </div>
            @elseif(!$order->receiptable_amount)
                <div class="col-sm-12 stat text-muted">
                    <div class="stat-value-sm"><i class="fa fa-exclamation-circle"></i> No Receiptable Amount</div>
                </div>
            @elseif(!$order->is_view_only && user()->can('taxreceipt.edit') && !$order->is_refunded)
                <div class="col-sm-12 stat">
                    <a href="{{ route('backend.orders.generate_tax_receipt', $order) }}" class="btn btn-sm btn-info"><i class="fa fa-fw fa-refresh"></i> Generate Tax Receipt</a>
                </div>
            @else
                <div class="col-sm-12 stat text-muted">
                    <div class="stat-value-sm"><i class="fa fa-exclamation-circle"></i> No Tax Receipt Issued</div>
                    <div class="stat-label">Tax Receipt</diV>
                </div>
            @endif
        </div>
    </div>
</div>
@endif
