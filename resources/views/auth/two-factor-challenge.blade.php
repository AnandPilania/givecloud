
@extends('layouts.guest')

@section('title', 'Login')
@section('body_classes', 'login-screen')

@section('content')
    <x-guest.container title="Two-Factor Challenge">

        <div x-data="{ recovery: false }" x-cloak>
            <form role="form" method="post" action="{{ route('two-factor.login') }}" class="w-full">
                @csrf

                <div x-show="! recovery">
                    <div class="mb-4 text-sm text-gray-600">
                        Please confirm access to your account by entering the authentication code provided by your authenticator application.
                    </div>
                    <div class="mt-4" x-show="! recovery">
                        <label for="code" class="block text-sm font-medium leading-5 text-gray-700">Code</label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input id="code" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
                                        x-on:click="
                                            recovery = true;
                                            $nextTick(function() { $refs.recovery_code.focus() })
                                        ">
                            Use a recovery code
                        </button>
                    </div>
                </div>

                <div x-show="recovery">
                    <div class="mb-4 text-sm text-gray-600">
                        Please confirm access to your account by entering one of your emergency recovery codes.
                    </div>
                    <div class="mt-4">
                        <label for="recovery_code" class="block text-sm font-medium leading-5 text-gray-700">Recovery Code</label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input id="recovery_code"class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" type="text" inputmode="numeric" name="recovery_code" autofocus x-ref="recovery_code" autocomplete="one-time-code" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
                                        x-on:click="
                                            recovery = false;
                                            $nextTick(function() { $refs.code.focus() })
                                        ">
                            Use an authentication code
                        </button>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <span class="rounded-md shadow-sm">
                        <button type="submit" class="py-3 inline-flex items-center px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                            Continue <x-icons.arrow-right class="inline ml-2" />
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </x-guest.container>

@endsection
