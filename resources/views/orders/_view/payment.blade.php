<div class="panel panel-basic">
    <div class="panel-body">

        <div class="bottom-gutter-sm">
            <div class="panel-sub-title">Payment</div>
        </div>

        @if(!$order->is_paid)

            <div class="row pointer payment-wrap" role="button" data-target="#payments-modal" data-toggle="modal">

                @if($order->response_text)
                    <div class="col-xs-12 stat text-danger">
                        <div class="stat-value-sm">
                            <strong><i class="fa fa-exclamation-triangle"></i> {{ $order->response_text }}</strong>
                        </div>
                        <div class="stat-label">Declined Response &nbsp;&nbsp;<a
                                href="https://help.givecloud.com/en/articles/1541616-failed-or-declined-payments"
                                target="_blank" rel="noreferrer"><i class="fa fa-question-circle"></i> How do I fix
                                this?</a></div>
                    </div>
                @endif

                <div class="col-xs-12 stat">
                    <div class="stat-value-sm">{{ toLocalFormat($order->started_at, 'M j, Y \a\t\ g:iA') }}</div>
                    <div class="stat-label">Started At</diV>
                </div>

            </div>

            @if ($order->userCan(['edit', 'refund']) && ! $order->is_spam)
                <div>
                    <a class="focus:no-underline hover:no-underline" href="#mark-as-spam-modal" data-toggle="modal">Mark As Spam</a>
                </div>
            @endif

        @else
            @php($pending_count = $order->payments->where('status','pending')->count())

            <div
                class="flex p-[15px] pt-0 cursor-pointer payment-wrap {{ $pending_count ? 'warning' : ''}} bg-transparent"
                role="button" data-target="#payments-modal" data-toggle="modal">

                <div class="grow min-w-[0px]">
                    <div class="stat-value font-black whitespace-nowrap text-ellipsis">
                        {{ money($order->using_application_fee_billing ? $order->subtotal : $order->totalamount, $order->currency)->format('$0,0[.]00 [$$$]') }}
                    </div>
                    @if($order->using_application_fee_billing)
                        <div class="stat-label font-bold">
                            {{ money($order->totalamount, $order->currency)->format('$0,0[.]00 [$$$]') }} Charged
                        </diV>
                    @endif
                </div>

                <div class="grow-0 fit-content text-right">
                    <div class="stat-label flex items-start mt-[10px] min-h-[22px]">
                        @if ($order->used_apple_pay || $order->used_google_pay)
                            <img
                                src="{{ jpanel_asset_url('images/payment/' . ($order->used_apple_pay ? 'apay' : 'gpay') . '.svg') }}"
                                alt="" class="h-[22px] mt-[2px] mr-1">
                        @elseif ($order->fa_icon === 'fa-cc-visa')
                            <svg width='67' height='22' class="inline-block -mr-[2px] mb-[9px]" viewBox='0 0 39 14'
                                 fill='none' xmlns='http://www.w3.org/2000/svg'>
                                <path fillRule='evenodd' clipRule='evenodd'
                                      d='M9.69057 13.1462H6.40113L3.93445 3.45895C3.81737 3.01333 3.56878 2.61939 3.20311 2.43371C2.29054 1.96711 1.28494 1.59576 0.187927 1.40847V1.03551H5.48695C6.21829 1.03551 6.7668 1.59576 6.85822 2.24642L8.13807 9.23419L11.4259 1.03551H14.6239L9.69057 13.1462ZM16.4523 13.1462H13.3457L15.9038 1.03551H19.0104L16.4523 13.1462ZM23.0296 4.3907C23.121 3.73842 23.6695 3.36546 24.3094 3.36546C25.315 3.27181 26.4104 3.4591 27.3246 3.92409L27.8731 1.3166C26.9589 0.943635 25.9533 0.756348 25.0408 0.756348C22.0256 0.756348 19.8315 2.43386 19.8315 4.76204C19.8315 6.5332 21.3856 7.46318 22.4827 8.02343C23.6695 8.58206 24.1266 8.95502 24.0352 9.51366C24.0352 10.3516 23.121 10.7246 22.2084 10.7246C21.1114 10.7246 20.0144 10.4453 19.0104 9.97865L18.4619 12.5878C19.5589 13.0527 20.7457 13.24 21.8427 13.24C25.2236 13.3321 27.3246 11.6562 27.3246 9.1407C27.3246 5.97295 23.0296 5.78728 23.0296 4.3907ZM38.1969 13.1462L35.7302 1.03551H33.0807C32.5322 1.03551 31.9837 1.40847 31.8009 1.96711L27.2332 13.1462H30.4312L31.0695 11.3767H34.9989L35.3646 13.1462H38.1969ZM33.5378 4.297L34.4504 8.86132H31.8923L33.5378 4.297Z'
                                      fill='currentColor'/>
                            </svg>
                        @else
                            <i class="-mt-[3px] mb-[7px] fa fa-2x {{ $order->fa_icon }}>"></i>
                        @endif
                    </div>
                    <div class="stat-label font-bold">
                        @if ($order->used_apple_pay || $order->used_google_pay)
                            <div class="mt-[8px]">
                                <i class="fa {{ $order->fa_icon }} -mt-[3px] align-top text-[25px]"></i>
                                {{ $order->billingcardlastfour ?? '' }}
                            </div>
                        @else
                            {{ $order->billingcardlastfour ?? ''}}
                        @endif
                    </div>
                </div>

            </div>

            @if($pending_count)
                <div
                    class="flex -mt-1 -mx-[15px] px-[15px] pt-[4px] pb-[2px] bg-[#c3b28f] text-[10px] font-bold text-white">
                    PAYMENT PENDING
                </div>
            @endif

            <div x-data="{ show: false }">
                <div class="row overflow-hidden max-h-0 transition-all ease-in-out duration-300" x-ref="row"
                     :style="show && 'max-height:' + $refs.row.scrollHeight + 'px'">

                    @if ($order->using_application_fee_billing)
                        <div class="px-[15px] py-4 border-t border-t-gray-300">
                            <div class="flex items-center mb-2">
                                <div class="grow">Amount Donated</div>
                                <div>{{ money($order->subtotal, $order->currency) }}</div>
                            </div>
                            <div class="flex items-center mb-2">
                                <div class="grow">+ Optional DCC</div>
                                <div>{{ money($order->dcc_total_amount, $order->currency) }}</div>
                            </div>
                            <div class="flex items-center mb-2 font-bold">
                                <div class="grow">Total Charged
                                    ({{ $order->payment_type_formatted }} {{ $order->billingcardlastfour ? " {$order->billingcardlastfour}" : '' }})
                                </div>
                                <div class="grow-0">
                                    <td>{{ money($order->totalamount, $order->currency) }}</div>
                            </div>
                            <div class="flex items-center mb-2 text-[#aaa]">
                                <div class="grow">Stripe Fees</div>
                                <div class="grow-0">({{ money($order->stripe_fee_amount, $order->currency_code) }})
                                </div>
                            </div>
                            <div class="flex items-center mb-2 text-[#aaa]">
                                <div class="grow">Givecloud Platform Fee</div>
                                <div class="grow-0">
                                    ({{ money($order->latestPayment->application_fee_amount, $order->currency) }})
                                </div>
                            </div>
                            <div class="flex items-center font-bold">
                                <div class="grow">Net Amount</div>
                                <div class="grow-0">{{ money($order->net_total_amount, $order->currency) }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="py-2 clearfix border-t border-t-gray-300">
                        @if($order->is_pos && $order->payment_type == 'check')
                            <div class="col-xs-6 stat">
                                <div class="stat-value-sm text-ellipsis">{{ $order->check_number }}</div>
                                <div class="stat-label">Check Number</diV>
                            </div>
                            <div class="col-xs-6 stat">
                                <div class="stat-value-sm">{{ toLocalFormat($order->check_date, 'M j, Y') }}</div>
                                <div class="stat-label">Check Date</diV>
                            </div>

                        @elseif($order->is_pos && $order->payment_type == 'cash')

                            <div class="col-xs-6 stat">
                                <div class="stat-value-sm">{{ money($order->cash_received, $order->currency) }}</div>
                                <div class="stat-label">Cash Received</diV>
                            </div>
                            <div class="col-xs-6 stat">
                                <div class="stat-value-sm">{{ money(-$order->cash_change, $order->currency) }}</div>
                                <div class="stat-label">Change Given</diV>
                            </div>

                        @elseif($order->is_pos && $order->payment_type == 'other')

                            <div class="col-xs-6 stat">
                                <div class="stat-value-sm text-ellipsis">{{ $order->payment_other_reference }}</div>
                                <div class="stat-label">Reference Number</diV>
                            </div>
                            @if($order->payment_other_note)
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-sm text-ellipsis"
                                         title="{{ $order->payment_other_note }}">{{ $order->payment_other_note }}</div>
                                    <div class="stat-label">Payment Notes</diV>
                                </div>
                            @endif

                        @elseif ($order->paymentProvider)
                            @if ($order->confirmationnumber)
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-xs text-ellipsis" title="{{ $order->confirmationnumber }}">
                                        @if (in_array($order->paymentProvider->provider, ['nmi','safesave']))
                                            <a href="https://secure.nmi.com/merchants/reports.php?Action=Details&transaction_type=ck&report_id=0&transaction={{ $order->confirmationnumber }}"
                                               target="_blank">{{ $order->confirmationnumber }}</a>
                                        @elseif ($order->paymentProvider->provider === 'paypalexpress')
                                            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id={{ $order->confirmationnumber }}"
                                               target="_blank">{{ $order->confirmationnumber }}</a>
                                        @elseif ($order->paymentProvider->provider === 'stripe')
                                            <a href="https://dashboard.stripe.com/payments/{{ $order->confirmationnumber }}"
                                               target="_blank">{{ $order->confirmationnumber }}</a>
                                        @elseif ($order->paymentProvider->provider === 'braintree')
                                            <a href="{{ sprintf(
                                                        'https://%s/merchants/%s/transactions/%s',
                                                        $order->paymentProvider->test_mode ? 'sandbox.braintreegateway.com' : 'braintreegateway.com',
                                                        $order->paymentProvider->config('merchant_id'),
                                                        $order->confirmationnumber
                                                    ) }}" target="_blank">{{ $order->confirmationnumber }}</a>

                                        @else
                                            {{ $order->confirmationnumber }}
                                        @endif
                                    </div>
                                    <div class="stat-label">Gateway Auth</diV>
                                </div>
                            @endif

                            @if($order->vault_id != null)
                                <div class="col-xs-6 stat">
                                    <div class="stat-value-xs text-ellipsis">
                                        {{ $order->vault_id ?: 'Not Available' }}
                                    </div>
                                    <div class="stat-label">Gateway Vault</diV>
                                </div>
                            @endif

                            <div class="clearfix"></div>

                            <div class="col-xs-6 stat">
                                <div
                                    class="stat-value-xs">{{ toLocalFormat($order->started_at) . ' at ' . toLocalFormat($order->started_at, 'g:iA') }}</div>
                                <div class="stat-label">Started At</diV>
                            </div>

                            <div class="col-xs-6 stat">
                                <div
                                    class="stat-value-xs">{{ toLocalFormat($order->createddatetime) . ' at ' . toLocalFormat($order->createddatetime, 'g:iA') }}</div>
                                <div class="stat-label">Completed</diV>
                            </div>

                        @endif

                        @php($failed_count = $order->payments->where('status', 'failed')->count())
                        @if ($failed_count)
                            <a class="col-xs-6 stat focus:no-underline hover:no-underline" href="#payments-modal"
                               data-toggle="modal">
                                <div class="inline-block text-danger stat-value-xs"><i
                                        class="fa fa-fw fa-exclamation-triangle"></i> {{ $failed_count  }}</div>
                                <div class="inline-block text-danger stat-label">Failed Attempts</diV>
                            </a>
                        @endif

                        @if ($order->userCan(['edit', 'refund']))
                            <div class="col-xs-12 stat">
                                <a class="focus:no-underline hover:no-underline" href="{{ route('backend.orders.refresh-payment-status', $order) }}">Refresh Payment Status</a><br>
                                @unless ($order->is_spam) <a class="focus:no-underline hover:no-underline" href="#mark-as-spam-modal" data-toggle="modal">Mark As Spam &amp; Refund</a> @endunless
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row -mb-[15px] p-1 bg-gray-100 rounded-b-lg text-center cursor-pointer" @click="show = !show">
                    <div class="inline-block transition-all duration-300 ease-in-out transform"
                         :class="{ 'rotate-180': show }">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
