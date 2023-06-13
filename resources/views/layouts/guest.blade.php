<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
    @include('layouts.app-icons')

    <title>@yield('title', $pageTitle ?? '') | {{ sys_get('clientName') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ jpanel_asset_url('dist/css/vendor.css') }}">
    <link rel="stylesheet" href="{{ jpanel_asset_url('dist/css/app.css') }}">
    <link rel="stylesheet" href="{{ jpanel_asset_url('css/jpanel.css') }}">
    <link rel="stylesheet" href="{{ jpanel_asset_url('dist/css/tailwind.css') }}">

    <!-- Core JS -->
    <script charset="utf-8" src="https://cdn.givecloud.co/npm/jquery@3.3.1/dist/jquery.min.js"></script>

    @yield('head')
</head>
<body class="@yield('body_classes', $body_classes ?? '') bg-gray-100 guest-template-bg font-sans subpixel-antialiased">

    @yield('header')

    <div class="min-h-screen z-10 flex items-center justify-center">
        @yield('content')
    </div>

<script charset="utf-8" src="{{ jpanel_asset_url('dist/js/vendor.js') }}"></script>
<script charset="utf-8" src="{{ jpanel_asset_url('dist/js/app.js') }}"></script>

@yield('scripts')

</body>
</html>
