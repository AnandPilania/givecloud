@extends('layouts.app')
@section('title', 'Contributions')

@section('content')
    <div class="toastify hide">
        @inject('flash', 'flash')
        {{ $flash->output() }}
    </div>

    @include('orders._view.header')
    @include('orders._view.alerts')

    <div class="flex flex-wrap xl:flex-nowrap xl:space-x-10">
        <div class="w-full">
            @if($order->member)
                @include('orders._view.supporter')
            @elseif($order->confirmationdatetime)
                @include('orders._view.no-supporter')
            @endif

            @include('orders._view.invoice')
        </div>

        <div class="max-w-sm xl:w-4/12">
            @include('orders._view.refund')
            @include('orders._view.payment')
            @include('orders._view.warnings')
            @include('orders._view.tracking')
            @include('orders._view.tax-receipt')
            @include('orders._view.donor-perfect')
            @include('orders._view.salesforce')
            @include('orders._view.double-the-donation')
        </div>
    </div>

    @include('orders._view.modals.link-supporter')
    @include('orders._view.modals.create-supporter')
    @include('orders._view.modals.edit')
    @include('orders._view.modals.refund')
    @include('orders._view.modals.update-dp-data')
    @include('orders._view.modals.sync-dp')
    @include('orders._view.modals.update-item')
    @include('orders._view.modals.update-item-fields')
    @include('orders._view.modals.update-giftaid-eligibiity')
    @include('orders._view.modals.delete-order')
    @include('orders._view.modals.mark-as-spam')
    @include('orders._view.modals.payments')
    @include('orders._view.modals.taxes')

    <script>
        spaContentReady(function() {

            @if (sys_get('double_the_donation_enabled') && $order->doublethedonation_registered)
            axios.get('/jpanel/api/v1/double-the-donation/{{ $order->id }}/status')
                .then(function(res) {
                    $('[data-dtd="fetching"]').hide();

                    if (! res.data.data.company_name) {
                        $('[data-dtd="no-match"]').removeClass('hide');
                    }
                    else {
                        $('[data-dtd="loaded"]').removeClass('hide');
                    }

                    $('[data-dtd="status"]').html(res.data.data.status_label || 'N/D');
                    $('[data-dtd="company-name"]').html(res.data.data.company_name || 'N/D');
                });
            @endif

            $('input[name=refund_type]').on('change', function (ev) {
                if (($(ev.target).val() == 'custom')) {
                    $('#custom-refund-amount').css('display', 'block');
                    $('#full-refund-option').css('display', 'none');
                    $('#custom-refund-amount input').val('').focus();
                } else {
                    $('#custom-refund-amount').css('display', 'none' );
                    $('#full-refund-option').css('display', 'block');
                }
            });

            $('.change-product').on('click', function(ev){
                ev.preventDefault();

                var item_id    = $(ev.target).data('item-id');
                var $modal     = $('#update-item');
                $modal.find('input[name=item_id]').val(item_id);

                $modal.modal();
            });

            $('.change-gift-aid-eligibility').on('click', function(ev){
                ev.preventDefault();

                var $el = $(ev.target);
                var item_id = $el.data('item-id');
                var gift_aid_eligible = $el.data('gift-aid-eligible');
                var $modal = $('#update-gift-aid_eligibility');
                var $form = $modal.find('form');
                var $submitBtn = Ladda.create($form.find('button[type=submit]')[0]);
                var $cancelBtn = $form.find('button[type=button]');
                $modal.find('input[name=item_id]').val(item_id);
                $modal.find('select[name=gift_aid_eligible]').val(gift_aid_eligible);

                $modal.modal();

                $form.on('submit', function(event) {
                    $submitBtn.start();
                    $cancelBtn.prop('disabled', true);
                });
            });

            $('.change-custom-fields').on('click', function(ev){
                ev.preventDefault();

                var item_id = $(ev.target).data('item-id');
                var $modal = $('#update-item-fields');
                var $container = $('#update-item-fields-container');

                $modal.on('show.bs.modal', function(){
                    $container.html('<div class="text-center text-muted" style="margin:50px;"><i class="fa fa-4x fa-spin fa-circle-o-notch"></i></div>');
                    $container.load('<?= e(route('backend.orders.getItemFields', $order)); ?>?item_id='+item_id);
                });

                $modal.modal();
            });
        });
    </script>

@endsection
