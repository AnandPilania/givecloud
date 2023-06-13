<?php if (user()->can_live_chat): ?>
    <script>
        window.intercomSettings = <?= dangerouslyUseHTML(json_encode([
            'app_id' => 'cs01jxl6',
            'name' => user()->full_name,
            'email' => user()->email,
            'user_hash' => hash_hmac('sha256', user()->email, config('services.intercom.identity_verification')),
            'created_at' => fromUtcFormat(site()->created_at, 'U'),
            'logrocketURL' => 'https://app.logrocket.com/rouoyn/givecloud/sessions?u=' . urlencode(user()->email),
            'company'              => [
                'id'            => site()->client->id,
                'name'          => site()->client->name,
                'partner_name'  => site()->partner->name,
                'created_at'    => fromUtcFormat(site()->created_at, 'U'),
                'plan'          => site()->client->tier,
                'monthly_spend' => site()->client->mrr,
                'upgraded_at'   => fromUtcFormat(site()->client->ordered_on, 'U'),
                'has_purchased' => (bool) site()->client->ordered_on,
                'support_chat'  => site()->subscription->support_chat,
                'support_phone' => site()->subscription->support_phone,
            ]
        ])) ?>;
    </script>
    <script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/cs01jxl6';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
<?php endif; ?>
