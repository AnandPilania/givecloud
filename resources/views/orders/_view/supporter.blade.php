<div x-data="{ show: false }" class="bg-white rounded-lg shadow-outline-gca mb-8 relative">
    <div class="absolute right-0 btn-group">
        <button type="button" class="m-4 btn btn-xs btn-outline btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-pencil"></i> Options &nbsp;<span class="caret"></span>
        </button>
        <ul class="dropdown-menu pull-right">
            <li>
                <a href="{{ route('backend.member.edit', $order->member->id) }}">
                    <i class="fa fa-fw fa-vcard-o"></i> View Supporter
                </a>
            </li>
            @if($order->member->userCan('login'))
                <li>
                    <a href="{{ route('backend.members.login', $order->member->id) }}" target="_blank">
                        <i class="fa fa-lock fa-fw"></i> Login as {{ trim($order->member->first_name) !== '' ? $order->member->first_name : 'Supporter' }}
                    </a>
                </li>
            @endif
            <li>
                <a href="{{ route('backend.member.edit', ['id' => $order->member, '#contributions']) }}">
                    <i class="fa fa-inbox-in"></i> View Contributions
                </a>
            </li>
            <li>
                <a href="{{ route('backend.member.edit', ['id' => $order->member, '#recurring']) }}">
                    <i class="fa fa-repeat"></i> View Recurring Payments
                </a>
            </li>
            @if($order->member->userCan('merge'))
                <li>
                    <a href="#" data-toggle="modal" data-target="#mergeAccount">
                        <i class="fa fa-code-fork fa fa-flip-vertical fa-fw"></i> Merge With...
                    </a>
                </li>
            @endif
            <li>
                <a href="#" data-toggle="modal" data-target="#linkAccount">
                    <i class="fa fa-fw fa-exchange"></i> Switch Supporter</a>
            </li>
            <li>
                <a href="#modal-create-member" data-toggle="modal">
                    <i class="fa fa-fw fa-user-plus"></i> Create Supporter from Contribution
                </a>
            </li>
        </ul>
    </div>
    <div class="flex gap-4 p-6">
        <div class="flex-shrink-0">
            <span id="supporter:{{ $order->member->id }}" data-initials="{{ $order->member->initials }}" class="hidden avatar inline-flex items-center justify-center h-14 w-14 rounded-full bg-gray-500">
              <span class="font-bold text-xl leading-none text-white">{{ $order->member->initials }}</span>
            </span>
            <img class="h-14 w-14 rounded-full" src="{{ $order->member->avatar ?? $order->member->gravatar }}" alt="{{ $order->member->display_name }}"
                 onerror="this.classList.add('hidden'); document.getElementById('supporter:{{ $order->member->id }}').classList.remove('hidden')">
        </div>
        <div class="flex-grow">
            <div class="mt-2">
                <span class="font-extrabold text-2xl">{{ $order->member->display_name }}</span>
                @if($order->member->accountType)
                    <span class="ml-2 text-sm font-bold text-gray-500">{{ $order->member->accountType->name }}</span>
                @endif
            </div>

            <dl class="mt-8">
                <div class="flex flex-wrap justify-between">
                    <div class="flex flex-col">
                        <dt class="order-2 text-sm font-bold text-gray-400">First Payment</dt>
                        <dd class="order-1 text-base font-bold">{{ toLocalFormat($order->member->orders->min('ordered_at'), 'humans') }}</dd>
                    </div>
                    <div class="flex flex-col">
                        <dt class="order-2 text-sm font-bold text-gray-400">Lifetime Total</dt>
                        <dd class="order-1 text-base font-bold">{{ money($order->member->orders->sum('functional_total')) }}</dd>
                    </div>
                    <div class="flex flex-col">
                        <dt class="order-2 text-sm font-bold text-gray-400">Lifetime Contributions</dt>
                        <dd class="order-1 text-base font-bold">{{ number_format($order->member->orders->count()) }}</dd>
                    </div>
                    <div class="flex flex-col">
                        <dt class="order-2 text-sm font-bold text-gray-400">Recurring</dt>
                        <dd class="order-1 text-base font-bold">{{ money($order->member->recurringPaymentProfiles->sum('aggregate_amount')) }}</dd>
                    </div>
                </div>
            </dl>
        </div>
    </div>

    <div class="overflow-hidden max-h-0 transition-all ease-in-out duration-300" x-ref="row" :style="show && 'max-height:' + $refs.row.scrollHeight + 'px'">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <div class="w-14"></div>
            </div>
            <div class="flex-grow">
                <div class="px-6 pb-6">
                    <dl >
                        <div class="flex flex-wrap justify-between">
                            <div class="flex flex-col">
                                <dt class="order-2 text-sm font-bold text-gray-400">Billing Address</dt>
                                <dd class="order-1 text-base font-bold">{!! nl2br($order->member->display_bill_address) !!} </dd>
                            </div>
                            <div class="flex flex-col">
                                <dt class="order-2 text-sm font-bold text-gray-400">Email</dt>
                                <dd class="order-1 text-base font-bold">{{ $order->member->email }}</dd>
                            </div>

                            <div class="flex flex-col">
                                @if($order->member->bill_phone)
                                <dt class="order-2 text-sm font-bold text-gray-400">Phone</dt>
                                <dd class="order-1 text-base font-bold">{{ $order->member->bill_phone ?? 'N/D' }}</dd>
                                @endif
                            </div>
                        </div>
                    </dl>

                    @if (dpo_is_enabled() && $order->member->donor_id)
                        <span class="inline-flex mt-6  items-center rounded-full bg-blue-100 px-3 py-1 font-semibold text-sm text-brand-blue">
                            DonorPerfect  Donor ID: <span class="ml-1 font-black">{{ $order->member->donor_id }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

    </div>
    <div class="p-1 bg-gray-100 text-center rounded-b-lg cursor-pointer" @click="show = !show">
        <div class="inline-block transition-all duration-300 ease-in-out transform" :class="{ 'rotate-180': show }">
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </div>
</div>
