<div class="panel panel-basic">
    <div class="panel-body">

        <div class="bottom-gutter">
            @if(!$order->is_view_only)
                <a href="#edit-order-modal" data-toggle="modal" class="pull-right btn btn-info btn-xs btn-outline"><i class="fa fa-pencil"></i> Edit</a>
            @endif
            <div class="panel-sub-title">Invoice</div>
        </div>

        <div style="margin:10px;">
            <div class="row bottom-gutter">
                <div class="col-sm-6">
                    <strong>Billing Address</strong><br>
                    <ul class="fa-ul" style="margin-left:21px;">
                        @if ($order->billingaddress1)
                        <li><i class="fa fa-li fa-home"></i>
                             {{ $order->billing_first_name . ' ' . $order->billing_last_name }}
                            <br>
                            @if($order->billing_organization_name)
                                {{ $order->billing_organization_name }}<br>
                            @endif

                            {!! nl2br(address_format(
                                $order->billingaddress1,
                                $order->billingaddress2,
                                $order->billingcity,
                                $order->billingstate,
                                $order->billingzip,
                                null
                            )) !!}
                        </li>
                        @elseif(trim($order->billing_first_name))
                            <li class="text-muted"><i class="fa fa-li fa-home"></i>
                                {{ $order->billing_first_name . ' ' . $order->billing_last_name }}</li>
                        @else
                            <li class="text-muted"><i class="fa fa-li fa-home"></i> N/A</li>
                        @endif

                        @if ($order->billingcountry)
                            <li><i class="fa fa-li"><img src="{{ flag($order->billingcountry) }}" style="margin-right:3px; width:16px; height:16px; vertical-align:middle;"></i>
                                {{ cart_countries()[$order->billingcountry] }}
                            </li>
                        @else
                            <li class="text-muted"><i class="fa fa-li fa-globe"></i> N/A</li>
                        @endif

                        @if ($order->billingemail)
                        <li><i class="fa fa-li fa-envelope-o"></i>
                            <a href="mailto:{{ $order->billingemail }}">{{ $order->billingemail }}</a>
                        </li>
                        @else
                            <li class="text-muted"><i class="fa fa-li fa-envelope-o"></i> N/A</li>
                        @endif

                        @if ($order->billingphone)
                        <li><i class="fa fa-li fa-phone"></i>
                            <a href="tel:{{ $order->billingphone }}">{{ $order->billingphone }}</a>
                        </li>
                        @else
                        <li class="text-muted"><i class="fa fa-li fa-phone"></i> N/A</li>
                        @endif
                    </ul>
                </div>
                @if (feature('shipping'))
                <div class="col-sm-6">
                    <strong>Shipping Address</strong><br>
                    <ul class="fa-ul" style="margin-left:21px;">
                        @if ($order->shipaddress1)
                        <li><i class="fa fa-li fa-home"></i>
                            {{ $order->shipping_first_name . ' ' . $order->shipping_last_name }}<br>
                            @if($order->shipping_organization_name)
                              {{ $order->shipping_organization_name }} <br>
                            @endif
                            {!! nl2br(address_format(
                                $order->shipaddress1,
                                $order->shipaddress2,
                                $order->shipcity,
                                $order->shipstate,
                                $order->shipzip,
                                null
                            )) !!}
                        </li>
                        @elseif(trim($order->shipping_first_name))
                            <li class="text-muted"><i class="fa fa-li fa-home"></i>
                               {{ $order->shipping_first_name . ' ' . $order->shipping_last_name }}
                            </li>
                        @else
                            <li class="text-muted"><i class="fa fa-li fa-home"></i> N/A</li>
                        @endif

                        @if ($order->shipcountry)
                            <li><i class="fa fa-li">
                                <img src="{{ flag($order->shipcountry) }}" style="margin-right:3px; width:16px; height:16px; vertical-align:middle;"></i>
                                {{ cart_countries()[$order->shipcountry] }}
                            </li>
                        @else
                            <li class="text-muted"><i class="fa fa-li fa-globe"></i> N/A</li>
                        @endif

                        @if ($order->shipemail)
                            <li><i class="fa fa-li fa-envelope-o"></i>
                                <a href="mailto:{{ $order->shipemail }}">{{ $order->shipemail }}</a>
                            </li>
                        @else
                            <li class="text-muted"><i class="fa fa-li fa-envelope-o"></i> N/A</li>
                        @endif

                        @if ($order->shipphone)
                            <li><i class="fa fa-li fa-phone"></i>
                                <a href="tel:{{ $order->shipphone }}">{{ $order->shipphone }}</a>
                            </li>
                        @else
                            <li class="text-muted"><i class="fa fa-li fa-phone"></i> N/A</li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>

            @if(feature('givecloud_pro'))
                <div class="row bottom-gutter">
                    <div class="col-sm-6">
                        <strong>Special Notes</strong><br>
                            @if($order->comments)
                                {{ $order->comments }}
                            @else
                                <small class="text-muted">None Provided</small>
                           @endif
                    </div>
                    @if($order->customer_notes)
                        <div class="col-sm-6">
                            <strong>Note to Customer</strong><br>
                            {!! $order->customer_notes !!}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="table-responsive">
                    <table class="table table-invoice">
                        <thead>
                        <tr>
                            <th>Item Name</th>
                            <th width="50" style="text-align:center;">Qty</th>
                            <th width="80" style="text-align:right;">Price</th>
                            <th width="80" style="text-align:right;">Total ({{ $order->currency->unique_symbol }})</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td align="left" style="{{ $item->is_locked ? 'padding-left:40px;' : '' }}">

                                <!-- thumbnail -->
                                <div style="float:left; text-align:right; width:55px;">
                                    <a @if ($item->admin_link) href="{{  $item->admin_link }}" @endif style="display:inline-block;">
                                        @if ($item->is_fundraising_form_upgrade)
                                        <div class="flex items-center justify-center bg-transparent border-none text-yellow-300 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="currentColor" class="w-6 h-8">
                                                <path d="M381.2 172.8C377.1 164.9 368.9 160 360 160h-156.6l50.84-127.1c2.969-7.375 2.062-15.78-2.406-22.38S239.1 0 232 0h-176C43.97 0 33.81 8.906 32.22 20.84l-32 240C-.7179 267.7 1.376 274.6 5.938 279.8C10.5 285 17.09 288 24 288h146.3l-41.78 194.1c-2.406 11.22 3.469 22.56 14 27.09C145.6 511.4 148.8 512 152 512c7.719 0 15.22-3.75 19.81-10.44l208-304C384.8 190.2 385.4 180.7 381.2 172.8z"/>
                                            </svg>
                                        </div>
                                        @else
                                            <div class="avatar-{{ $item->is_locked ? 'lg' : 'xl' }}" style="background-image:url('{{ $item->image_thumb }}');"></div>
                                        @endif
                                    </a>
                                </div>

                                <!-- details -->
                                <div style="margin-left:65px;">
                                    @if (feature('edit_order_items') && !$order->is_view_only && !$order->isForFundraisingForm() && (count($item->fields) > 0 || $item->variant))
                                    <div class="btn-group pull-right">
                                        <button type="button" class="btn btn-xs btn-outline btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-pencil"></i> Edit &nbsp;<span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu pull-right">
                                            @if (count($item->fields) > 0)
                                            <li><a href="#" class="change-custom-fields" data-item-id="{{ $item->id }}"><i class="fa fa-fw fa-pencil"></i> Change Custom Fields</a></li>
                                            @endif
                                            @if ($item->variant)
                                            <li><a href="#" class="change-product" data-item-id="{{ $item->id }}"><i class="fa fa-fw fa-pencil"></i> Change Product</a></li>
                                            @endif
                                            @if (sys_get('gift_aid') === 1 && data_get($item, 'variant.product.is_tax_receiptable') === 1)
                                            <li><a href="#" class="change-gift-aid-eligibility" data-item-id="{{ $item->id }}" data-gift-aid-eligible="{{ $item->gift_aid ? 1 : 0 }}"><i class="fa fa-fw fa-pencil"></i> Change Gift Aid Eligibility</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                    @endif

                                        <!-- product -->
                                    @if ($item->variant && $item->is_locked && $item->lockedToItem->upgraded_to_recurring)

                                        <span class="inline-flex items-center mb-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Monthly Upgrade</span>

                                    @elseif ($item->variant)
                                        <strong style="font-size:14px;">
                                            @if ($item->admin_link)
                                                <a href="{{ $item->admin_link }}">{{ $item->variant->product->name }}</a>
                                            @else
                                                {{ $item->variant->product->name }}
                                            @endif
                                        </strong>
                                        {!! trim($item->variant->variantname) !== '' ? '(' . $item->variant->variantname . ')' : '' !!}
                                        <span class="code">{{ $item->code }}<br />
                                            @if(is_numeric($item->variant->weight) && $item->variant->weight > 0)
                                                (Weight: {{ $item->variant->weight }}lbs)
                                            @endif
                                        </span>
                                    @else
                                        <strong style="font-size:14px;">
                                            @if ($item->admin_link)
                                                <a href="{{ $item->admin_link }}">{{  $item->description }}</a>
                                            @else
                                                {!!  $item->description !!}
                                            @endif
                                        </strong>
                                        <span class="code">{{ $item->code }}</span>
                                    @endif

                                    @if($item->promocode)
                                        <div class="pc"><span class="code">PROMO: <strong>{{ $item->promocode }}</strong></span><span class="desc"> {{ optional($item->promo)->description }}</span></div>
                                    @endif

                                    @if($item->is_recurring)
                                        <div class="recurring_desc">{{ $item->payment_string }}</div>
                                        @if($item->dcc_eligible && $item->dcc_recurring_amount > 0)
                                            <div class="text-muted">Includes {{ money($item->dcc_recurring_amount, $order->currency) }} for {{ sys_get('dcc_label') }}</div>
                                        @endif
                                    @endif

                                    @if ($item->gl_code && ! ($item->is_locked && $item->lockedToItem->upgraded_to_recurring))
                                        <span class="code">GL: <strong>{{ $item->gl_code }}</strong></span>
                                    @endif

                                    @if($item->tribute && $item->tribute->userCan('view'))
                                        For: {{ $item->tribute->name }} ({{ $item->tribute->tributeType->label }})
                                        @if($item->tribute->notify === 'email')
                                            <br>Send email to: {{ $item->tribute->notify_name }})
                                            <br>{{ $item->tribute->notify_email }})
                                        @elseif($item->tribute->notify === 'letter')
                                            <br>Send letter to {{ $item->tribute->notify_name }})
                                            <br>{!! nl2br(e(address_format($item->tribute->notify_address, null, $item->tribute->notify_city, $item->tribute->notify_state, $item->tribute->notify_zip, $item->tribute->notify_country))) !!}
                                        @else
                                            <br>No notification.
                                        @endif
                                    @endif

                                    @foreach($item->fields as $field)
                                        <div class="custom_field_desc">{{ $field->name }}:&nbsp;<strong>{{ $field->value_formatted }}</strong></div>
                                    @endforeach

                                    <!-- honor roll comments -->
                                    @if ($item->public_message)
                                    <div class="top-gutter-sm">
                                        &ldquo;{{ $item->public_message }}&rdquo;<br>
                                        @if($order->is_anonymous)
                                            <small class="text-muted">- Anonymous</small>
                                        @else
                                            <small class="text-muted">- {{ optional($order->member)->display_name }}</small>
                                        @endif
                                    </div>
                                    @endif

                                    <!-- gift aid -->
                                    @if($item->gift_aid)
                                        <div class="top-gutter-sm"><i class="fa fa-check-square-o"></i> Gift Aid</div>
                                    @endif

                                    <!-- original variant -->
                                    @if ($item->variant && $item->original_variant_id !== $item->variant->id && $item->original_variant)
                                        @php($item->load('originalVariant.product'))
                                        <div class="text-muted" style="font-size:10px; font-style:italic; margin:7px 0px;">
                                            Original Item: {{ $item->originalVariant->product->name ?? '' }}{{ $item->originalVariant->variantname ? ' - ' . $item->originalVariant->variantname : '' }} ({{ $item->originalVariant->product->code }})
                                        </div>
                                    @endif

                                        <!-- buttons -->
                                    <div style="margin-top:10px">
                                        @if($item->recurringPaymentProfile)
                                            <a href="/jpanel/recurring_payments/{{ $item->recurringPaymentProfile->profile_id }}" class="btn btn-xs btn-info">
                                                <i class="fa fa-refresh"></i> View Recurring Payment ({{ $item->recurringPaymentProfile->payment_string }})
                                            </a>
                                        @endif
                                        @if($item->tribute && $item->tribute->userCan('view'))
                                            <a href="#" class="btn btn-xs btn-info ds-tribute" data-tribute-id="{{ $item->tribute->id }}"><i class="fa fa-gift"></i> View Tribute</a>
                                        @endif
                                        @if(!$order->is_view_only && $item->variant && $item->variant->product->allow_check_in == 1)
                                            <a href="{{ route('backend.orders.checkin', ['o' => $order->id, 'i' => $item->id]) }}" class="btn btn-info btn-xs"><i class="fa fa-qrcode"></i> Check-In</a>
                                        @endif
                                        @if(!$order->is_view_only && $item->variant && $item->variant->file)
                                            <a href="{{ route('backend.orders.reprocess_downloads', $order) }}" class="btn btn-info btn-xs"><i class="fa fa-envelope"></i> Send Email</a>
                                        @endif
                                        @if($item->fundraisingPage)
                                            <a href="{{ $item->fundraisingPage->absolute_url }}" target="_blank" class="btn btn-info btn-outline btn-xs"><i class="fa fa-users"></i> {{ $item->fundraisingPage->title }}</a>
                                        @endif
                                        @if(optional($item->variant)->membership && !$item->groupAccount)
                                            <a href="{{ route('backend.orders.applyGroup', $item) }}" class="btn btn-info btn-outline btn-xs"><i class="fa fa-plus"></i> Add to "{{ $item->variant->membership->name }}"</a>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- a bundled sub-item -->
                            @if ($item->is_locked)
                                <td class="text-center text-muted">{{ $item->qty }}</td>
                                <td class="text-right text-muted">{{ money($item->price, $order->currency) }}</td>
                                <td class="text-right text-muted">-</td>

                            <!-- an item that has bundled items attached -->
                            @elseif ($item->lockedItems->count() > 0)
                                <td class="text-center">{{ $item->qty }}</td>
                                <td style="text-align:right;">{{ money($item->locked_variants_price, $order->currency) }}</td>
                                <td style="text-align:right;">{{ money($item->locked_variants_total, $order->currency) }}</td>

                            <!-- a standard line-item -->
                            @else
                                <td class="text-center">{{ $item->qty }}</td>
                                <td style="text-align:right;">{{ money($item->price, $order->currency) }}</td>
                                <td style="text-align:right;">{{ money($item->total, $order->currency) }}</td>
                            @endif
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td rowspan="6"></td>
                            <td colspan="2">Subtotal</td>
                            <td style="text-align:right;">{{ money($order->subtotal, $order->currency) }}</td>
                        </tr>
                        @if($order->shippable_items > 0)
                        <tr>
                            <td colspan="2">Shipping
                                @if($order->shipping_method_name)
                                <small> {{ $order->shipping_method_name }}</small>
                                @endif
                            </td>
                            <td style="text-align:right;">{{ money($order->shipping_amount, $order->currency) }}</td>
                        </tr>
                        @endif
                        @if($order->taxtotal)
                        <tr>
                            <td colspan="2"><a href="#taxes-modal" data-toggle="modal">Taxes</a></td>
                            <td style="text-align:right;"><a href="#taxes-modal" data-toggle="modal">{{ money($order->taxtotal, $order->currency) }}</a></td>
                        </tr>
                        @endif
                        @if($order->dcc_total_amount)
                        <tr>
                            <td colspan="2">{{ sys_get('dcc_label') }}</td>
                            <td style="text-align:right;">{{ money($order->dcc_total_amount, $order->currency) }}</a></td>
                        </tr>
                        @endif
                        <tr class="text-bold">
                            <td colspan="2">Total</td>
                            <td style="text-align:right;">{{ money($order->totalamount, $order->currency) }}</td>
                        </tr>
                        @if($order->refunded_at)
                            @foreach(data_get($order, 'successfulPayments.0.successfulRefunds') as $refund)
                                <tr class="text-bold danger">
                                    <td colspan="2">
                                        Refund<br>
                                        <small>on {{ toLocalFormat($refund->created_at, 'M j, Y') }} by {{ optional($refund->refundedBy)->full_name }}</small>
                                    </td>
                                    <td style="text-align:right;">{{ money(-$refund->amount, $refund->currency) }}</td>
                                </tr>
                            @endforeach
                            <tr class="text-bold">
                                <td colspan="2">Balance</td>
                                <td style="text-align:right;">{{ money($order->balance_amt, $order->currency) }}</td>
                            </tr>
                        @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
