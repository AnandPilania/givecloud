
@extends('layouts.guest')

@section('title', 'Login')
@section('body_classes', 'login-screen')

@section('content')
    <x-guest.container title="Log In">

        <form role="form" name="login" method="post" action="{{ route('login') }}" class="w-full">
            @csrf

            @if (request()->has('f'))
                <x-alerts.error title="Login failed" class="mb-5" />
            @elseif (request()->has('t'))
                <x-alerts.warning title="Your session has expired" class="mb-5" />
            @endif

            @if (session('status'))
                <x-alerts.success class="mb-5" :title="session('status')" />
            @endif

            @if (session('_flashMessages.success'))
                <x-alerts.success class="mb-5" :title="session('_flashMessages.success')" />
            @endif

            @if (session('_flashMessages.error'))
                <x-alerts.error class="mb-5" :title="session('_flashMessages.error')" />
            @endif

            @if ($errors->any())
                <x-alerts.error class="mb-5" :title="$errors->all()[0]" />
            @endif

            <div>
                <label for="email" class="block text-sm font-medium leading-5 text-gray-700">
                    Email address
                </label>
                <div class="mt-1 rounded-md shadow-sm">
                    <input id="email" name="email" type="email" value="{{ old('email', request('email')) }}" required autocomplete="email" {{ request('email') ? '' : 'autofocus' }}  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                </div>
            </div>

            <div class="mt-6">
                <label for="password" class="block text-sm font-medium leading-5 text-gray-700">
                    Password
                </label>
                <div class="mt-1 rounded-md shadow-sm">
                    <input id="password" name="password" type="password" {{ request('email') ? 'autofocus' : '' }} required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between">
                <div class="flex items-center">
                    <input id="inputRemember" name="remember" type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out rounded focus:ring focus:ring-indigo-200" {{ old('remember') ? 'checked' : '' }}>
                    <label for="inputRemember" class="ml-2 mb-0 block text-sm leading-5 text-gray-900">Remember me</label>
                </div>

                <div class="text-sm leading-5 pt-4 sm:pt-0">
                    <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                    Forgot your password?
                    </a>
                </div>
            </div>

            <div class="mt-12 text-center">
                <span class="rounded-md shadow-sm">
                    <button type="submit" class="w-52 inline-flex items-center justify-center py-3 px-6 border border-transparent text-lg font-medium rounded-full text-white bg-brand-pink hover:bg-pink-500 focus:outline-none focus:border-brand-pink focus:ring focus:ring-pink-200 active:bg-pink-700 transition duration-150 ease-in-out">
                        <span>Continue</span>
                        <x-icons.arrow-right class="inline ml-2" />
                    </button>
                </span>
            </div>

            <div class="mt-6 text-center">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500 font-bold">
                          OR
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col space-y-3 items-center">
                <a href="{{ route('backend.socialite.redirect', ['provider' => 'google']) }}" class="w-52 hover:text-gray-500 inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                            <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"/>
                            <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"/>
                            <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z"/>
                            <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z"/>
                        </g>
                    </svg>
                    <span class="">Login with Google</span>
                </a>

                <a href="{{ route('backend.socialite.redirect', ['provider' => 'microsoft']) }}" class="w-52 hover:text-gray-500 inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 23 23">
                        <path fill="#f3f3f3" d="M0 0h23v23H0z"/><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/>
                    </svg>
                    <span class="">Login with Microsoft</span>
                </a>

                <a href="{{ route('backend.socialite.redirect', ['provider' => 'facebook']) }}" class="w-52 hover:text-gray-500 inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2"  xmlns="http://www.w3.org/2000/svg"viewBox="0 0 40 40">
                        <defs>
                            <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="-277.375" y1="406.6018" x2="-277.375" y2="407.5726" gradientTransform="matrix(40 0 0 -39.7778 11115.001 16212.334)">
                                <stop offset="0" style="stop-color:#0062E0"/>
                                <stop offset="1" style="stop-color:#19AFFF"/>
                            </linearGradient>
                        </defs>
                        <path style="fill:url(#SVGID_1_)" d="M16.7,39.8C7.2,38.1,0,29.9,0,20C0,9,9,0,20,0s20,9,20,20c0,9.9-7.2,18.1-16.7,19.8l-1.1-0.9h-4.4L16.7,39.8z"/>
                        <path fill="#fff" d="M27.8,25.6l0.9-5.6h-5.3v-3.9c0-1.6,0.6-2.8,3-2.8h2.6V8.2c-1.4-0.2-3-0.4-4.4-0.4c-4.6,0-7.8,2.8-7.8,7.8V20  h-5v5.6h5v14.1c1.1,0.2,2.2,0.3,3.3,0.3c1.1,0,2.2-0.1,3.3-0.3V25.6H27.8z"/>
                    </svg>
                    <span class="">Login with Facebook</span>
                </a>
            </div>




        </form>
    </x-guest.container>

@endsection
