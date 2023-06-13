@props([
    'title' => null,
])

<div class="login-container bg-white rounded-lg px-8 py-10 sm:py-16 sm:px-16 flex flex-col items-center mx-6 md:mx-0 my-6">
    <div class="mx-4 mb-3 flex flex-col sm:flex-row items-center justify-center">
        @if(partner() !== 'gc' && !empty(site()->partner->in_app_brand))
            <img class="h-8 mt-1 w-auto" src="https://cdn.givecloud.co/static/etc/givecloud-logo-full-color-rgb.svg" alt="Givecloud" />
            <x-main-nav.cobrand />
        @else
            <img class="h-10 w-auto" src="https://cdn.givecloud.co/static/etc/givecloud-logo-full-color-rgb.svg" alt="Givecloud" />
        @endif
    </div>

    @if (!empty($title))
        <h1 class="py-5 text-4xl font-medium text-center">{{ $title }}</h1>
    @endif

    {{ $slot }}
</div>
