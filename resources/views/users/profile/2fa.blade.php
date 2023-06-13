<div class="mt-10 sm:mt-0">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900"><i class="fa fa-lock"></i> Two Factor Authentication</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">
                    Add additional security to your account using two factor authentication.
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div id="two-factor-authentication-profile-app" data-enabled="{{ (bool) $user->two_factor_secret }}" class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6 v-cloak">
                    <h3 class="mt-1 text-lg font-medium text-gray-900">
                        <template v-if="enabled">
                            You have enabled two factor authentication.
                        </template>
                        <template v-else>
                            You have not enabled two factor authentication.
                        </template>
                    </h3>
                    <div class="mt-3 text-sm text-gray-600">
                        <p>
                            When two factor authentication is enabled, you will be prompted for a secure, random token during authentication.
                            You may retrieve this token from an authenticator app (e.x. 1Password, LastPass, Authy, Google Authenticator).
                        </p>
                    </div>
                    <template v-if="enabled">
                        <div v-if="showingQrCode">
                            <div class="mt-4 text-sm text-gray-600">
                                <div class="flex">
                                    <div class="mr-4 shrink-0 self-center">
                                        <img width="300" src="https://cdn.givecloud.co/static/etc/gc-2fa-google.gif" alt="">
                                    </div>
                                    <div>
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
                                            <li>Then, use your device's camera to scan the barcode</li>
                                        </ol>
                                    </div>
                                </div>
                                <p class="mt-6 font-semibold">
                                    Two factor authentication is now enabled. Scan the following QR code using your authenticator app.
                                </p>
                            </div>
                            <div class="mt-4 dark:p-4 dark:w-56 dark:bg-white" v-html="twoFactorQrCodeSvg"></div>
                        </div>
                        <div v-if="showingRecoveryCodes">
                            <div class="mt-4 max-w-xl text-sm text-gray-600">
                                <p class="font-semibold">
                                    Store these recovery codes in a secure password manager. They can be used to recover access to your
                                    account if your two factor authentication device is lost.
                                </p>
                            </div>
                            <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                                <div v-for="code in twoFactorRecoveryCodes">${ code }</div>
                            </div>
                        </div>
                        <div class="mt-5">
                            <button v-if="showingRecoveryCodes" @click="regenerateRecoveryCodes()" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                Regenerate Recovery Codes
                            </button>
                            <button v-else @click="showRecoveryCodes()" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                Show Recovery Codes
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mt-5">
                            <button @click="enable()" type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gcb-700 bg-gcb-200 focus:outline-none focus:border-gcb-300 focus:ring focus:ring-gcb-200 disabled:opacity-50 transition ease-in-out duration-150 font-bold">
                                Enable
                            </button>
                        </div>
                    </template>
                </div>
                <div v-if="enabled" class="px-4 py-4 bg-yellow-50 sm:px-6">
                    <p class="mt-1 mb-2 text-sm font-medium italic text-gray-900">
                        You can turn off Two-Factor Authentication if you would no longer like to use it for this account.
                    </p>
                    <button @click="disable()" type="button" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 transition ease-in-out duration-150">
                        Disable
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
