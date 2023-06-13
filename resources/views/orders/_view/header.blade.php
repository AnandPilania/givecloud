<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix" style="border-bottom:none;">
            <span class="page-header-text">{{ $title }}</span>

            <div class="visible-xs-block"></div>

            @if($order->is_paid && $order->userCan(['edit','fullfill','refund']))
            <div class="pull-right">
                <div class="btn-group" role="group" aria-label="...">
                    @if($order->is_refundable && $order->userCan('refund'))
                        @if ($order->totalamount > 0)
                            <button type="button" data-target="#refund-modal" data-toggle="modal" class="btn btn-default" title="Refund" data-popover-bottom="<strong>Refund</strong><br>Perform a full or partial refund of this contribution."><i class="fa fa-fw fa-reply text-danger"></i></button>
                        @else
                            <a href="javascript:void(0);" data-popover-bottom="<strong>Refund</strong><br>Perform a full or partial refund of this contribution." onclick="$.alert('You cannot refund this contribution as there was nothing charged to the customer at the time of checkout.', 'danger', 'fa-reply');" class="btn btn-default" title="Refund"><i class="fa fa-fw fa-reply text-danger"></i></a>
                        @endif
                    @endif

                    @if($order->userCan('edit'))
                        <a href="#delete-order-modal" data-popover-bottom="<strong>Delete</strong><br>Completely and permanently delete this contribution and anything it my have created (tax receipts, recurring payments, etc)." class="btn btn-default" data-toggle="modal"><i class="text-danger fa fa-trash"></i></a>
                    @endif

                    @if (feature('givecloud_pro') && $order->userCan(['edit','fullfill']))
                        @if($order->is_fulfillable)
                        <a href="{{ route('backend.orders.packing_slip', ['id' => $order->getKey()]) }}" target="_blank" class="btn btn-default" data-popover-bottom="<strong>Print Packing Slip</strong><br>Print a packing slip used to pick, pack and fulfill a physical contribution. This is different from an invoice."><i class="fa fa-print fa-fw"></i></a>
                        @endif

                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-popover-bottom="<strong>Re-Send Email Notifications</strong><br>Choose who you'd like to renotify about this contribution via email.">
                            <i class="fa fa-envelope-o fa-fw"></i>  <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            <li>
                                <a href="{{ route('backend.orders.reprocess_product_specific_emails', ['o' => $order->invoicenumber, 'i' => $order->getKey()]) }}">
                                    <i class="fa fa-envelope fa-fw"></i> Renotify Supporter Only
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('backend.orders.notify_site_owner', ['o' => $order->invoicenumber, 'i' => $order->id]) }}">
                                    <i class="fa fa-envelope fa-fw"></i> Renotify Staff Only
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    @if (feature('givecloud_pro'))
                        <a href="{{ secure_site_url(route('order_review', $order->invoicenumber, false)) }}" target="_blank" class="btn btn-default" data-popover-bottom="<strong>View Customer Receipt</strong><br>View the link that the customer/donor see's when reviewing/tracking their payment. (opens in a new window)"><i class="fa fa-fw fa-external-link"></i></a>
                    @endif
                </div>

                @if ($order->user_can_fulfill)
                    @if(!$order->iscomplete)
                        <a href="{{ route('backend.orders.complete', $order) }}" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm hiddem-md"> Mark Fulfilled</span></a>
                    @else
                        <a href="{{ route('backend.orders.incomplete', $order) }}" class="btn btn-success btn-outline"><i class="fa fa-check-square-o"></i><span class="hidden-xs hidden-sm hiddem-md"> Fulfilled</span></a>
                    @endif
                @endif
            </div>
            @endif

            @if($order->confirmationdatetime)
                <div class="text-secondary">
                    @if($order->is_pos)
                        <div class="pull-right"><i class="fa fa-calculator"></i> POS entry by {{ $order->createdBy->full_name }}</div>
                    @endif

                    Via {{ $order->source }}
                    @if ($order->kiosk)
                        ({{$order->kiosk->name}})
                    @endif
                    on
                    @if ($order->ordered_at)
                        {{ toLocalFormat($order->ordered_at, 'l, F j, Y')}} ({{ toLocalFormat($order->ordered_at, 'humans') }})
                    @else
                        {{ toLocalFormat($order->started_at, 'l, F j, Y') }} ({{ toLocalFormat($order->started_at, 'humans') }})
                    @endif
                </div>
            @endif
        </h1>
    </div>
</div>
