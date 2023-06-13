@if ($user->is_account_admin)
    <div class="non-bootstrap-hidden sm:block">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">API Key</h3>
                    <p class="mt-1 text-sm leading-5 text-gray-600">
                        For more information on how your API Key can be used to integrate Givecloud with other services
                        please contact <a href="mailto:{{ config('mail.support.address') }}">{{ config('mail.support.address') }}</a>.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <p class="mb-4 text-sm leading-5 text-gray-600">
                            Your API key is only visible the first time you generate it. After that, your Profile redacts the API key.
                            If you lose your API key, you can't recover it and must regenerate the key.
                        </p>
                        <p class="mb-4 text-sm leading-5 font-bold text-yellow-400">
                            Your API key should be kept confidential and only stored on your own servers. Your API key can be used to make any API call on behalf of
                            your account. Treat your secret API key as you would any other password.
                        </p>
                        <div id="userApiToken">
                            <div class="input-wrap mb-2 {{ $user->api_token ? '' : 'hide' }}">
                                <input id="inputApiToken" type="password" name="api_token" value="{{ $user->api_token ? str_repeat('•', 64) : '' }}" readonly class="password mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                            </div>
                            <button type="button" onclick="j.user.regenerateKey({{ $user->id }})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gcb-700 bg-gcb-200 focus:outline-none focus:border-gcb-300 focus:ring focus:ring-gcb-200 disabled:opacity-50 transition ease-in-out duration-150 font-bold">
                                {{ $user->api_token ? 'Regenerate' : 'Generate' }} key
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
