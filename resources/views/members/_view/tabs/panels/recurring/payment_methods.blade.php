<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fas fa-credit-card fa-fw"></i> Saved Payment Methods
        @if (feature('add_from_vault') && $member->id)
            <a
                href="#"
                data-toggle="modal"
                data-target="#add-method-from-vault"
                class="pull-right btn btn-info btn-xs">
                <i class="fa fas fa-plus"></i> Add From Vault
            </a>
        @endif
    </div>

    <div class="panel-body">
        <div class="row">
            @foreach ($member->paymentMethods()->active()->get() as $payment_method)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="thumbnail text-center" style="padding:20px 0px;">
                        <i class="fa fas fa-4x {{ fa_payment_icon($payment_method->account_type) }}"></i><br>
                        <strong>{{ $payment_method->account_number }}</strong><br>
                        @if ($payment_method->cc_expiry)
                            @if ($payment_method->is_expired)
                                <small class="text-danger">
                                    <i class="fa fas fa-exclamation-triangle"></i>
                                    Expired {{ $payment_method->cc_expiry->format('M, Y') }}
                                </small>
                            @else
                                <small>Expires {{ $payment_method->cc_expiry->format('M, Y') }}</small>
                            @endif
                        @else
                            <small>{{ $payment_method->account_type }}</small>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
