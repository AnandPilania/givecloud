
@extends('layouts.guest')

@section('title', 'Two-Factor Authentication')
@section('body_classes', 'two-factor-nagger')

@section('content')
    <x-guest.container>
        <div id="two-factor-authentication-profile-app" data-enabled="false">
            <div class="mt-3 text-sm text-gray-600 text-center">
                <h3>Two-Factor Authentication</h3>
                <p>
                    When two factor authentication is enabled, you will be prompted for a secure, random token during authentication.
                    You may retrieve this token from an authenticator app (e.x. 1Password, LastPass, Authy, Google Authenticator).
                </p>
            </div>
            <template v-if="enabled">
                <div>
                    <template v-if="showingQrCode && showingRecoveryCodes">
                        <div>
                            <div class="mt-8 text-sm text-gray-600">
                                <div class="flex">
                                    <div class="mr-4 shrink-0 self-center">
                                        <img width="300" src="https://cdn.givecloud.co/static/etc/gc-2fa-google.gif" alt="">
                                    </div>
                                    <div class="my-5">
                                        <h4 class="text-lg font-bold">Recommendation: Google Authenticator</h4>
                                        <p class="mt-1">
                                            To get started make sure you have Google Authenticator installed on your Apple or Android device.
                                        </p>
                                        <p class="mt-3">
                                            <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" class="inline-block mr-1">
                                                <img class="h-12" src="{{ jpanel_asset_url('images/google-play-store.png') }}" alt="Get it on Google Play">
                                            </a>
                                            <a href="https://apps.apple.com/app/google-authenticator/id388497605" class="inline-block">
                                                <img class="h-12" src="{{ jpanel_asset_url('images/apple-app-store.png') }}" alt="Download on the App Store">
                                            </a>
                                        </p>
                                        <ol class="mt-4 pl-6">
                                            <li>Open Google Authenticator and tap <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">+</span></li>
                                            <li>Then, use your phone's camera to scan the barcode</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-10 flex">
                                <div class="mr-12 w-72">
                                    <p class="font-semibold">
                                        Scan the following QR code using your authenticator app.
                                    </p>
                                    <div class="mt-4 dark:p-4 dark:w-56 dark:bg-white" v-html="twoFactorQrCodeSvg"></div>
                                </div>
                                <div>
                                    <div class="max-w-xl text-sm text-gray-600">
                                        <p class="font-semibold">
                                            Store these recovery codes in a secure password manager. They can be used to recover access to your account.
                                        </p>
                                    </div>
                                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                                        <template >
                                            <div v-for="code in twoFactorRecoveryCodes" v-text="code"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-12 text-center">
                    <span class="rounded-md shadow-sm">
                        <a href="{{ $redirect_to }}" class="py-3 inline-flex items-center px-6 hover:no-underline border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 hover:text-white focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                            Continue <x-icons.arrow-right class="inline ml-2" />
                        </a>
                    </span>
                </div>
            </template>
            <template v-if="!enabled">
                <div class="mt-12 text-center">
                    <span class="rounded-md shadow-sm">
                        <button @click="enable()" type="button" class="py-3 mr-3 inline-flex items-center px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                            Enable
                        </button>
                    </span>
                    @if (sys_get('two_factor_authentication') !== 'force')
                        <span class="rounded-md shadow-sm">
                            <a href="{{ $redirect_to }}" class="py-3 inline-flex items-center px-6 hover:no-underline border border-gray-300 text-lg font-medium rounded-full text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-gray-50 transition duration-150 ease-in-out">
                                No thanks, I'll do it later <x-icons.arrow-right class="inline ml-2" />
                            </a>
                        </span>
                    @endif
                </div>
            </template>
        </div>
    </x-guest.container>

@endsection
