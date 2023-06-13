
@extends('layouts.guest')

@section('title', 'Reset Password')
@section('body_classes', 'login-screen')

@section('content')
    <x-guest.container title="Reset Password">

        @if (session('status'))
            <x-alerts.success class="mb-5" :title="session('status')" />
        @endif

        @if ($errors->any())
            <x-alerts.error class="mb-5" :title="$errors->all()[0]" />
        @endif

        <form role="form" method="POST" action="{{ route('password.update') }}" class="w-full">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-sm font-medium leading-5 text-gray-700">
                    Email address
                </label>
                <div class="mt-1 rounded-md shadow-sm">
                    <input id="email" name="email" type="email" required value="{{ $request->input('email') }}" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5"/>
                </div>
            </div>

            <div class="mt-6">
                <label for="password" class="block text-sm font-medium leading-5 text-gray-700">
                    New Password
                </label>
                <div class="mt-1 rounded-md shadow-sm">
                    <input id="password" name="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" autofocus />
                </div>
            </div>

            <div class="mt-6">
                <label for="password_confirmation" class="block text-sm font-medium leading-5 text-gray-700">
                    Confirm New Password
                </label>
                <div class="mt-1 rounded-md shadow-sm">
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                </div>
            </div>

            <div class="mt-12 text-center">
                <span class="rounded-md shadow-sm">
                    <button type="submit" class="py-3 inline-flex items-center px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                        <span>Reset <span class="sr-only sm:not-sr-only">Password</span></span> <x-icons.arrow-right class="inline ml-2" />
                    </button>
                </span>
            </div>
        </form>
    </x-guest.container>
@endsection
