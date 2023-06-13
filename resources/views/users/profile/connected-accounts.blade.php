    <div class="non-bootstrap-hidden sm:block">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Connected accounts</h3>
                    <p class="mt-1 text-sm leading-5 text-gray-600">
                        @if($user->socialIdentities->count() > 0)
                            You have {{ $user->socialIdentities->count() }} connected {{ \Illuminate\Support\Str::plural('account', $user->socialIdentities->count()) }}.
                        @else
                            You don't have any connected accounts.
                        @endif
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <ul role="list" class="divide-y divide-gray-200">
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <svg class="w-8 h-8 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                                            <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"/>
                                            <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"/>
                                            <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z"/>
                                            <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z"/>
                                        </g>
                                    </svg>
                                    <div class="flex-column">
                                        <div class="ml-2 flex-1 text-md">
                                          Google
                                        </div>
                                        <div class="ml-2 flex-1 text-gray-600">
                                            @if($connectedAccounts->has('google'))
                                                {{ $connectedAccounts->get('google')->updated_at->diffForHumans() }}
                                            @else
                                                Not connected
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 shrink-0">
                                    @if($connectedAccounts->has('google'))
                                    <a href="{{ route('backend.socialite.revoke', 'google') }}" class="font-medium text-red-500 hover:text-red-600">
                                        Revoke
                                    </a>
                                    @else
                                        <a href="{{ route('backend.socialite.redirect', 'google') }}" class="hover:text-gray-500 inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            Connect
                                        </a>
                                    @endif
                                </div>
                            </li>
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <svg class="w-8 h-8 mr-2" fill="currentColor" viewBox="0 0 23 23">
                                        <path fill="#f3f3f3" d="M0 0h23v23H0z"/><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/>
                                    </svg>
                                    <div class="flex-column">
                                        <div class="ml-2 flex-1 text-md">
                                            Microsoft
                                        </div>
                                        <div class="ml-2 flex-1 text-gray-600">
                                            @if($connectedAccounts->has('microsoft'))
                                                Last used {{ $connectedAccounts->get('microsoft')->updated_at->diffForHumans() }}
                                            @else
                                                Not connected
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 shrink-0">
                                    @if($connectedAccounts->has('microsoft'))
                                        <a href="{{ route('backend.socialite.revoke', 'microsoft') }}" class="font-medium text-red-500 hover:text-red-600">
                                            Revoke
                                        </a>
                                    @else
                                    <a href="{{ route('backend.socialite.redirect', 'microsoft') }}" class="hover:text-gray-500 inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        Connect
                                    </a>
                                    @endif
                                </div>
                            </li>
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <svg class="w-8 h-8 mr-2"  xmlns="http://www.w3.org/2000/svg"viewBox="0 0 40 40">
                                        <defs>
                                            <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="-277.375" y1="406.6018" x2="-277.375" y2="407.5726" gradientTransform="matrix(40 0 0 -39.7778 11115.001 16212.334)">
                                                <stop offset="0" style="stop-color:#0062E0"/>
                                                <stop offset="1" style="stop-color:#19AFFF"/>
                                            </linearGradient>
                                        </defs>
                                        <path style="fill:url(#SVGID_1_)" d="M16.7,39.8C7.2,38.1,0,29.9,0,20C0,9,9,0,20,0s20,9,20,20c0,9.9-7.2,18.1-16.7,19.8l-1.1-0.9h-4.4L16.7,39.8z"/>
                                        <path fill="#fff" d="M27.8,25.6l0.9-5.6h-5.3v-3.9c0-1.6,0.6-2.8,3-2.8h2.6V8.2c-1.4-0.2-3-0.4-4.4-0.4c-4.6,0-7.8,2.8-7.8,7.8V20  h-5v5.6h5v14.1c1.1,0.2,2.2,0.3,3.3,0.3c1.1,0,2.2-0.1,3.3-0.3V25.6H27.8z"/>
                                    </svg>
                                    <div class="flex-column">
                                        <div class="ml-2 flex-1 text-md">
                                            Facebook
                                        </div>
                                        <div class="ml-2 flex-1 text-gray-600">
                                            @if($connectedAccounts->has('facebook'))
                                                Last used {{ $connectedAccounts->get('facebook')->updated_at->diffForHumans() }}
                                            @else
                                                Not connected
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 shrink-0">
                                    @if($connectedAccounts->has('facebook'))
                                        <a href="{{ route('backend.socialite.revoke', 'facebook') }}" class="font-medium text-red-500 hover:text-red-600">
                                            Revoke
                                        </a>
                                    @else
                                        <a href="{{ route('backend.socialite.redirect', 'facebook') }}" class="hover:text-gray-500 inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            Connect
                                        </a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
