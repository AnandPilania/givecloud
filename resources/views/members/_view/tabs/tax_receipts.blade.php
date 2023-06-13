<div role="tabpanel" class="tab-pane fade in" id="taxreceipts">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-bank fa-fw"></i> Tax Receipts
        </div>

        <div class="panel-body">
            <div class="table-responsive">
                <table id="account-taxreceipts" class="table table-striped table-hover responsive">
                    <thead>
                        <tr>
                            <th colspan="2">Number</th>
                            <th>Issued To</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Issued At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member->taxReceipts()->orderBy('issued_at','desc')->get() as $receipt)
                            <tr>
                                <td width="16">
                                    <a
                                        href="{{ route('backend.tax_receipts.pdf', $receipt) }}"
                                        target="_blank"
                                        class="ds-tax-receipt"
                                        data-tax-receipt-id="{{ $receipt->getKey() }}">
                                        <i class="fa fas fa-search"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ $receipt->number }}
                                    @if ($receipt->status === 'draft')
                                        <span class="pull-right label label-xs label-default">DRAFT</span>
                                    @elseif ($receipt->status === 'void')
                                        <span class="pull-right label label-xs label-danger">VOID</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('backend.member.edit', $receipt->account_id) }}" target="_blank">
                                        {{ $receipt->name ?: $receipt->full_name }}
                                    </a>
                                </td>
                                <td>
                                    @if ($receipt->email)
                                        <a href="mailto:{{ $receipt->email }}">{{ $receipt->email }}</a>
                                    @endif
                                </td>
                                <td>
                                    <div class="stat-val">
                                        {{ number_format($receipt->amount, 2) }}
                                        <span class="text-muted">
                                            {{ $receipt->currency_code }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    {{ toLocalFormat($receipt->issued_at) }}
                                    <small class="text-muted">
                                        {{ toLocalFormat($receipt->getAttributeValue('issued_at'), 'humans') }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
