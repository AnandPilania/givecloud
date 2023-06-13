
@extends('layouts.guest')

@section('title', 'Reset Password')
@section('body_classes', 'login-screen')

@section('content')
    <x-guest.container title="Reset Password">

        <p class="mb-6">Provide your email address below and we'll send you instructions on how to reset your password.</p>

        @if (session('status'))
            <x-alerts.success class="mb-5" :title="session('status')" />
        @endif

        @if ($errors->any())
            <x-alerts.error class="mb-5" :title="$errors->all()[0]" />
        @endif

        <form role="form" name="login" method="post" action="{{ route('password.request') }}" class="w-full">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium leading-5 text-gray-700">
                    Email address
                </label>
                <div class="mt-1 rounded-md shadow-sm">
                    <input id="email" name="email" type="email" required autofocus class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                </div>
            </div>

            <div class="mt-12 text-center">
                <span class="rounded-md shadow-sm">
                    <button type="submit" class="py-3 inline-flex items-center px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                        <span>Send <span class="sr-only sm:not-sr-only">Password Reset</span> Link</span> <x-icons.arrow-right class="inline ml-2" />
                    </button>
                </span>
            </div>

            <div class="text-sm leading-5 mt-12 text-center">
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                Back to Login
                </a>
            </div>

        </form>
    </x-guest.container>

@endsection
