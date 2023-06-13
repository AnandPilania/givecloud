<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="pinterest" content="nopin">
    @include('layouts.app-icons')

    <title>@yield('title', $pageTitle ?? '') | {{ sys_get('clientName') }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (!isDev() && app()->bound('bugsnag'))
    <script src="//d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
    <script>Bugsnag.start(<?= dangerouslyUseHTML(json_encode([
        'apiKey' => config('services.bugsnag.js_api_key'),
        'releaseStage' => site('version'),
        'user' => [
            'id' => sys_get('ds_account_name'),
            'name' => sys_get('ds_account_name'),
        ],
    ])); ?>);</script>
    @endif

    <!-- Google Fonts -->
    @php
    $fonts = collect([
        'Nunito+Sans:wght@200;300;400;600;700;800;900',
        'Source+Code+Pro:wght@300;400;700',
        'Open+Sans:ital,wght@0,300;0,400;0,700;1,400;1,700',
        'Poppins:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&display=swap'
    ]);
    @endphp
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $fonts->implode('&family=') }}&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    @if($appSource == 'laravel' )
        <link rel="stylesheet" href="{{ jpanel_asset_url('dist/css/vendor.css') }}">
        <link rel="stylesheet" href="{{ jpanel_asset_url('dist/css/app.css') }}">
    @endif
    <link rel="stylesheet" href="{{ jpanel_asset_url('apps/admin/css/app.css') }}">
    @if($appSource == 'laravel' )
        <link rel="stylesheet" href="{{ jpanel_asset_url('css/jpanel.css') }}">
    @endif
    <link rel="stylesheet" href="{{ jpanel_asset_url('dist/css/tailwind.css') }}">

    <!-- Google Maps & Charts -->
    <script charset="utf-8" src="//maps.googleapis.com/maps/api/js?key={{ config('services.google-maps.api_key') }}"></script>
    <script charset="utf-8" src="https://www.google.com/jsapi"></script>

    <!-- Core JS -->
    <script charset="utf-8" src="https://cdn.givecloud.co/npm/jquery@3.3.1/dist/jquery.min.js"></script>

    <!-- Google Analytics -->
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      ga('create', 'UA-21510149-17', 'auto');
      ga('send', 'pageview');
    </script>

    <style>
        body {
            background-color: #f8f8f8;
        }

        .page-header {
            margin-top: 30px;
        }
    </style>

    @yield('head')

    <script>
    window.adminSpaData = @json($adminSpaData);
    </script>
</head>
<body class="@yield('body_classes', $body_classes ?? '') antialiased">
    <div id="app"></div>
    <script>
        (function() {
            var spaContentReadyPromise = new Promise(function(resolve) {
                window.onSpaContentReady = function() {
                    jQuery(resolve);
                };
            });

            window.spaContentReady = spaContentReadyPromise.then.bind(spaContentReadyPromise);
        })();
    </script>

    <div id="mainContent" class="py-0 px-5 md:px-7">
        @yield('content')
    </div>

    @include('_settings')

    <script charset="utf-8" src="{{ jpanel_asset_url('dist/js/vendor.js') }}"></script>
    <script charset="utf-8" src="{{ jpanel_asset_url('dist/js/app.js') }}"></script>
    <script charset="utf-8" src="{{ jpanel_asset_url('apps/admin/js/app.js') }}"></script>

    @include('_help-widget')

    @if (site()->client->customer_id)
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
    @endif

    <script>
        spaContentReady(j.init);

        window.spaContentReadyCheckId = setInterval(function() {
            var $spaContent = $('#spaContent');

            if ($spaContent.length) {
                clearInterval(window.spaContentReadyCheckId);

                $('#mainContent').appendTo($spaContent).css({
                    width: 'auto',
                    height: 'auto',
                    overflow: 'visible',
                });

                window.onSpaContentReady();
            }
        }, 200);
    </script>

    @yield('scripts')

</body>
</html>
