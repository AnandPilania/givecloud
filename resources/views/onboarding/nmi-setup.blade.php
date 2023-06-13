
@extends('layouts.guest')

@section('title', 'Network Merchants Setup')
@section('body_classes', 'font-sans bg-gray-200')

@section('content')

        @if ($screen === 'setup')

            <x-guest.container title="Network Merchants Setup">

                <div class="mt-2 text-sm leading-5 text-gray-500 text-center">
                    <p>
                        This is a one-time setup link that expires 48-hours after being issued. It's provided to select trusted partners
                        as a method of securely configuring services on behalf of clients.
                    </p>
                </div>

                @if ($errors->any())
                    <x-alerts.error class="w-full mt-4" title="Some information is missing">
                        <ul class="m-0 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alerts.error>
                @endif

                <form class="mt-8 mb-6" action="{{ request()->url() }}" method="post">
                    @csrf

                    <div class="mt-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-3">
                        <label for="inputCredential2" class="block text-sm font-bold leading-5 text-gray-700 sm:mt-px sm:pt-2">
                            API Key
                        </label>
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <div class="max-w-xs rounded-md shadow-sm">
                                <input id="inputCredential3" name="credential3" value="{{ old('credential3') }}"
                                    class="form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:py-3">
                        <label for="inputIsAchAllowed" class="block text-sm font-bold leading-5 text-gray-700 sm:mt-px sm:pt-2">
                            Enable ACH
                        </label>
                        <div class="mt-1 sm:mt-0 sm:col-span-2 sm:pt-2">
                            <input id="inputIsAchAllowed" type="checkbox" name="is_ach_allowed" value="1" @if(old('is_ach_allowed')) checked @endif
                                class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out rounded focus:ring focus:ring-indigo-200">
                        </div>
                    </div>

                    <div class="mt-12 text-center">
                        <span class="rounded-md shadow-sm">
                            <button type="submit" class="py-3 inline-flex items-center px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                                <span>Save <span class="sr-only sm:not-sr-only">Configuration</span></span> <x-icons.arrow-right class="inline ml-2" />
                            </button>
                        </span>
                    </div>

                </form>

            </x-guest.container>

        @elseif ($screen === 'done')

            <x-guest.container title="Thank You">

                <div class="text-sm leading-5 text-gray-500 text-center">
                    <p class="my-4">
                        This configuration has been saved.
                    </p>
                    <p class="font-light">
                        You can now close this window.
                    </p>
                </div>

            </x-guest.container>

        @else

            <x-guest.container title="Invalid or expired link">

                <div class="text-sm leading-5 text-gray-500 text-center">
                    <p class="my-4">
                        One-time setup links expire 48-hours after being issued. They are provided to select trusted partners
                        as a method of securely configuring services on behalf of clients.
                    </p>
                    <p class="font-bold">
                        You can contact Givecloud support to obtain a another setup link.
                    </p>
                </div>
            </x-guest.container>

        @endif

@endsection
