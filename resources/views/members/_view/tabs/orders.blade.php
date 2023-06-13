<div role="tabpanel" class="tab-pane fade in" id="orders">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fas fa-cart-arrow-down fa-fw"></i> Contributions
        </div>

        <div class="panel-body">
            <div class="table-responsive">
                <table id="account-orders" class="table table-striped table-hover responsive">
                    <thead>
                        <tr>
                            <th colspan="2">Contribution No.</th>
                            <th>Bill To</th>
                            <th>Email</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($member->orders()->orderBy('ordered_at','desc')->get() as $order)
                            <tr class="
                                @if ($order->iscomplete) success @endif
                                @if ($order->is_refunded) text-muted @endif
                                @if ($order->is_unsynced) text-danger danger text-bold @endif">
                                <td width="16">
                                    <a href="{{ route('backend.orders.edit', $order) }}">
                                        <i class="fa fas fa-search"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ $order->invoicenumber }}
                                    @if ($order->is_test == 1)
                                        <span class="pull-right label label-xs label-warning">TEST</span>
                                    @endif
                                </td>
                                <td>{{ $order->billing_first_name }} {{ $order->billing_last_name }}</td>
                                <td>
                                    <a href="mailto:{{ $order->billingemail }}">
                                        {{ $order->billingemail }}
                                    </a>
                                </td>
                                <td align="center">
                                    {{ $order->total_qty }}
                                </td>
                                <td align="right">
                                    @if ($order->is_refunded)
                                        <i class="fa fas fa-reply"></i>
                                    @endif
                                    {{ money($order->totalamount, $order->currency_code)->format('0,000.00 $$$') }}
                                </td>
                                <td data-order="{{ toLocalFormat($order->ordered_at,'U') }}">
                                    {{ toLocalFormat($order->ordered_at, 'fdatetime') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
