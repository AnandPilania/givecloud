
@extends('layouts.guest')

@section('title', 'Suspended')
@section('body_classes', 'login-screen')

@section('content')
    <x-guest.container>
        @if (site()->isTrial() && site()->trialHasExpired())
            <p class="mt-8 mb-3 text-4xl font-medium text-center">
                How’d You Like Your Trial?
            </p>
            <p class="text-lg text-center mb-3">
                If you're ready to buy, you can <a href="/jpanel/settings/billing">choose a plan here.</a>
            </p>
            <p class="text-md text-center">
                Or if you need more time, set up a call with your success advocate by
                <a href="https://calendly.com/givecloud-sales/20min" target="_blank">clicking here</a> or you can
                <a href="javascript:Intercom('showNewMessage', 'I\'m ready to buy or I need more time');">start a live chat</a>.
            </p>
        @else
            <p class="mt-8 mb-4 text-4xl font-medium text-center">
                Your Account Is Temporarily Offline
            </p>
            <p class="text-lg text-center mb-3">
                There’s likely a problem with your payment method or subscription. To fix this, <a href="/jpanel/settings/billing">edit your billing details</a>.
            </p>
            <p class="text-md text-center">
                Alternatively, you can set up a time with your success advocate by
                <a href="https://calendly.com/givecloud-sales/20min" target="_blank">clicking here</a> or
                <a href="javascript:Intercom('showNewMessage', 'Help there\'s a problem with my subscription or payment method');">start a live chat</a>.
            </p>
        @endif
    </x-guest.container>
@endsection

@section('scripts')
<script>window.intercomSettings = {app_id: 'cs01jxl6'};</script>
<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/cs01jxl6';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
@endsection
