        <div x-data="{ monthly: true  }" class="max-w-7xl mx-auto pb-24 px-4 sm:px-6 lg:px-8">
            <a id="plans"></a>
            <!-- Toggle -->
            <div class="relative mt-12 flex justify-center sm:mt-16">
                <div class="bg-indigo-700 p-0.5 rounded-lg flex">
                    <button @click="monthly = true" type="button"
                            :class="monthly ? 'bg-white hover:bg-indigo-50 border-indigo-700 shadow-sm text-indigo-700' : 'text-indigo-200 hover:bg-indigo-800'"
                            class="relative py-2 px-6 border-transparent rounded-md text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-700 focus:ring-white focus:z-10">
                        Monthly billing
                    </button>
                    <button @click="monthly = false" type="button"
                            :class="! monthly ? 'bg-white hover:bg-indigo-50 border-indigo-700 shadow-sm text-indigo-700' : 'text-indigo-200 hover:bg-indigo-800'"
                            class="ml-0.5 relative py-2 px-6 border border-transparent rounded-md text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-700 focus:ring-white focus:z-10">
                        Annual billing
                    </button>
                </div>
            </div>

            <div class="m-auto text-center mt-8 text-lg">
                <p x-show="!monthly"><strong>Annual billing</strong> refers to your subscription fees, please note you will be charged platform fees on a monthly basis.</p>
            </div>

            <!-- Tiers -->
            <div class="mt-24 space-y-12 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-x-8">
                @foreach($plans as $plan)
                <div class="relative p-8 bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900">{{ $plan->name }}</h3>

                        @if($plan->most_popular)
                            <p class="absolute top-0 py-1.5 px-4 bg-indigo-500 rounded-full text-xs font-semibold uppercase tracking-wide text-white -translate-y-1/2">Most popular</p>
                        @endif

                        <p class="mt-4 flex items-baseline text-gray-900">
                            <span class="text-5xl font-extrabold tracking-tight">
                                @if($plan->hasPrice())
                                    <span x-show="monthly" >${{ $plan->asChargebeeMonthlyPlan()->price / 100 }}</span>
                                    <span x-show="! monthly" >${{ $plan->asChargebeeAnnualPlan()->price / 100 }}</span>
                                @else
                                    Let's talk
                                @endif
                            </span>
                            @if($plan->hasPrice())
                                <span x-show="monthly" class="ml-1 text-xl font-semibold">/month</span>
                                <span x-show="!monthly" class="ml-1 text-xl font-semibold">/year</span>
                            @endif
                        </p>

                        <ul role="list" class="mt-6 space-y-6">
                            @foreach($plan->features() as $feature)
                                <li class="flex">
                                    <svg class="shrink-0 w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="ml-3 text-gray-500">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if($plan->tier)
                        <a  class="mt-8 block w-full py-3 px-6 border border-transparent rounded-md text-center font-medium"
                            :href="monthly ? '{{ addslashes($plan->checkoutLink()) }}' : '{{ addslashes($plan->checkoutLink(false)) }}'"
                            :class="'{{ $plan->most_popular }}' ? 'bg-indigo-500 text-white hover:bg-indigo-600 hover:text-white ' : 'bg-indigo-50 text-indigo-700 hover:text-indigo-700 hover:bg-indigo-100'"
                        > Subscribe now
                        </a>
                    @else
                        <a href="javascript:Intercom('showNewMessage','I would information about the Entreprise-tier plans');"
                           class="bg-indigo-50 text-indigo-700 hover:text-indigo-700 hover:bg-indigo-100 mt-8 block w-full py-3 px-6 border border-transparent rounded-md text-center font-medium">Let's talk</a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>


