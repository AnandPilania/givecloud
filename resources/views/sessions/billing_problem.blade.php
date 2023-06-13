@extends('layouts.guest')

@section('title', 'Payment is Past Due')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .guest-template-bg {
            background: none;
            background-color: #F5FBFF;
        }
    </style>
@endsection

@section('header')
    <div class="absolute top-6 left-6">
        <img width="156" src="https://cdn.givecloud.co/static/etc/givecloud-logo-full-color-rgb.svg" alt="Givecloud">
    </div>
@endsection

@section('content')

    <div class="max-w-xs text-center">
        <h1>Billing Problem <span class="inline-block rotate-12">😅</span></h1>

        <p class="mt-6 text-base text-red-400 font-bold">It looks like your billing method on file may not be setup correctly.</p>

        <p class="mt-6 text-base font-semibold">You have a <span class="font-bold">{{ money($balance, $balance_currency_code) }}</span> balance owing for longer than 20 days.</p>

        <a href="javascript:j.openCustomerPortal('BILLING_HISTORY');"
           class="w-full inline-block text-lg font-semibold mt-6 py-2 px-4 border border-brand-blue text-brand-blue border-1 rounded-md
hover:text-brand-blue hover:bg-gcb-100">
            View All Invoices
        </a>

        <a href="javascript:j.openCustomerPortal('EDIT_PAYMENT_SOURCE');"
           class="w-full inline-block text-lg font-semibold mt-2 py-2 px-4 bg-brand-blue text-white rounded-md hover:text-white hover:bg-blue-500">
            Fix Payment Method &amp; Pay Now
        </a>

        <p class="max-w-xs mx-auto text-xs mt-8">Once the payment method is fixed and balance is paid, you'll be able to access your account again.</p>

        <p class="mt-4 text-xs">
            <a href="mailto:support@givecloud.com" class="underline hover:underline">Email Us</a>
            <span class="px-1">|</span>
            <a href="javascript:Intercom('showNewMessage', 'Help there\'s a problem with my subscription or payment method');" class="underline hover:underline">Live Chat</a>
        </p>

    </div>
@endsection

@section('scripts')
    @include('_help-widget')

    <script src="https://js.chargebee.com/v2/chargebee.js"></script>
    <script>
        (function(){
            var chargebeeInstance = Chargebee.init({
                site: @json(config('services.chargebee.site'))
            });

            chargebeeInstance.setPortalSession(function(){
                return $.get('/jpanel/settings/billing/customer_portal');
            });
        })();
    </script>
@endsection
