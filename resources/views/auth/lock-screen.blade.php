
@extends('layouts.guest')

@section('title', 'Lock Screen')
@section('body_classes', 'login-screen')

@section('content')
    <x-guest.container>
        <p class="mt-5 text-center">
            <i class="fa fa-lock fa-5x"></i>
        </p>

        <p class="mb-5 pt-2 pb-5 text-4xl font-medium text-center">
            {{ sys_get('site_password_message', 'Site Locked') }}
        </p>

        <form role="form" name="unlock" method="post" action="{{ route('backend.session.unlock_site') }}" class="w-full">
            @csrf
            <div>
                <label for="inputSitePassword" class="block text-sm font-medium leading-5 text-gray-700">
                    Your Password
                </label>
                <div class="mt-1 rounded-md shadow-sm">
                    <input id="inputSitePassword" name="site_password" type="password" required autofocus class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                </div>
            </div>
            <div class="mt-12 text-center">
                <span class="rounded-md shadow-sm">
                    <button type="submit" class="py-3 inline-flex items-center px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                        <i class="fa fa-sign-in fa-2x mr-2"></i> Continue
                    </button>
                </span>
            </div>
        </form>
    </x-guest.container>
@endsection
