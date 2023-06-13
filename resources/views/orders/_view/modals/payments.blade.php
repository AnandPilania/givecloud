<div class="modal fade modal-info" id="payments-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Payments</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table" style="margin-bottom:10px;">
                        <thead>
                        <tr>
                            <th>Time</th>
                            <th>Source</th>
                            <th>Message</th>
                            <th>Verification</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($order->payments as $payment)

                            @php
                            $row_class = '';
                            if ($payment->status === 'succeeded') {
                                $row_class = 'text-bold';
                            } elseif ($payment->status === 'failed') {
                                $row_class = 'text-danger';
                            }
                            @endphp

                        <tr class="{{ $row_class }}">
                            <td class="whitespace-nowrap">{{ toLocalFormat($payment->created_at, 'g:i:s a') }}</td>
                            <td class="whitespace-nowrap">{{ $payment->source_description }}</td>
                            <td>
                                @if($payment->status === 'succeeded')
                                Approved
                                @elseif($payment->status === 'pending')
                                Pending
                                @else
                                    @if($payment->failure_message)
                                        <div class="leading-tight">{{ $payment->failure_message }}</div>
                                    @else
                                        Failed
                                     @endif
                                @endif
                            </td>
                            <td class="whitespace-nowrap"{{ implode(', ', $payment->verification_messages) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
