<?php if(!$orderModel->member): ?>

    <div>
        <div class="text-center">
            <span class="fa-stack fa-4x">
                <i class="fa fa-circle fa-stack-2x text-danger"></i>
                <i class="fa fa-unlink fa-stack-1x fa-inverse"></i>
            </span>
        </div>

        <div class="text-center bottom-gutter">
            <div class="text-lg text-danger">No Supporter</div>
            <div class="text-sm top-gutter-sm">
                <a href="#modal-create-member" data-toggle="modal" class="btn btn-xs btn-info"><i class="fa fa-plus"></i> Create New</a>
            </div>
        </div>

        <div>
            <div class="section-header">Close Matches</div>
            <?php $matches = \Ds\Models\Member::findCloseMatchesTo($orderModel); ?>
            <?php if(count($matches) == 0): ?><div class="account-list">
                <div>
                    <i class="fa fa-exclamation-circle"></i> No Close Matches Found
                </div>
                <div class="text-center top-gutter">
                    <a href="#" data-toggle="modal" data-target="#linkAccount" class="btn btn-info btn-xs"><i class="fa fa-search"></i> Search Manually</a>
                </div>
            <?php else: ?>
                <div class="account-list">
                    <?php foreach($matches as $member): ?>
                        <div class="account-item">
                            <?php if(dpo_is_enabled() && $member->donor_id): ?>
                                <div class="text-sm pull-right flex items-center"><?= e($member->donor_id) ?> <img src="/jpanel/assets/images/dp-white.png" class="ml-1 -mt-[1px]" style="height:14px; width:auto;"></div>
                            <?php endif; ?>
                            <div class="text-bold"><a onclick="$.confirm('Are you sure you want to link this contribution and supporter?', function(){ window.location = '<?= e(route('backend.orders.link_member', ['id' => $orderModel->getKey(), 'member_id' => $member->id])) ?>'; }, 'warning', 'fa-link');"><i class="fa fa-link"></i> <?= e($member->display_name) ?></a></div>
                            <div class="text-sm">
                                <?php if($member->display_bill_address): ?><?= e($member->display_bill_address) ?><br><?php endif; ?>
                                <?php
                                    $meta = [];
                                    if ($member->email) $meta[] = $member->email;
                                    if ($member->bill_phone) $meta[] = $member->bill_phone;
                                ?>
                                <?= e(implode(' - ', $meta)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center top-gutter">
                    <a href="#" data-toggle="modal" data-target="#linkAccount" class="btn btn-info btn-xs"><i class="fa fa-search"></i> Find Another...</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>

    <div class="account-panel">

        <?php $show_map = ($orderModel->member->bill_zip) ? true : false ?>

        <?php if($show_map): ?>
            <div class="absolute w-full overflow-hidden"
                style="border-bottom:1px solid #999; margin:-30px -30px 0px -30px; height:200px; border-radius:8px;">
                    <img src="https://maps.googleapis.com/maps/api/staticmap?zoom=6&markers=size:mid%7Ccolor:white%7C<?= e(urlencode($orderModel->member->display_bill_address)) ?>&scale=2&size=300x600&maptype=roadmap&style=element:geometry%7Ccolor:0x464646&style=element:labels.icon%7Cvisibility:off&style=element:labels.text.fill%7Ccolor:0x757575&style=element:labels.text.stroke%7Ccolor:0x212121&style=feature:administrative%7Celement:geometry%7Ccolor:0x757575&style=feature:administrative.country%7Celement:labels.text.fill%7Ccolor:0x9e9e9e&style=feature:administrative.land_parcel%7Cvisibility:off&style=feature:administrative.locality%7Celement:labels.text.fill%7Ccolor:0xbdbdbd&style=feature:administrative.neighborhood%7Cvisibility:off&style=feature:poi%7Cvisibility:off&style=feature:poi%7Celement:labels.text%7Cvisibility:off&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0x757575&style=feature:poi.business%7Cvisibility:off&style=feature:poi.park%7Celement:geometry%7Ccolor:0x4f4f4f%7Cvisibility:off&style=feature:poi.park%7Celement:labels.text%7Cvisibility:off&style=feature:poi.park%7Celement:labels.text.fill%7Ccolor:0x616161&style=feature:poi.park%7Celement:labels.text.stroke%7Ccolor:0x1b1b1b&style=feature:road%7Celement:geometry.fill%7Ccolor:0x8b8b8b&style=feature:road%7Celement:labels%7Cvisibility:off&style=feature:road%7Celement:labels.text.fill%7Ccolor:0x8a8a8a&style=feature:road.arterial%7Celement:geometry%7Ccolor:0x373737&style=feature:road.arterial%7Celement:labels%7Cvisibility:off&style=feature:road.highway%7Celement:geometry%7Ccolor:0x5f5f5f&style=feature:road.highway%7Celement:labels%7Cvisibility:off&style=feature:road.highway.controlled_access%7Celement:geometry%7Ccolor:0x5e5e5e&style=feature:road.local%7Cvisibility:off&style=feature:road.local%7Celement:labels.text.fill%7Ccolor:0x616161&style=feature:transit%7Celement:labels.text.fill%7Ccolor:0x757575&style=feature:water%7Celement:geometry%7Ccolor:0x212121&style=feature:water%7Celement:labels.text%7Cvisibility:off&style=feature:water%7Celement:labels.text.fill%7Ccolor:0x3d3d3d&key=<?= e(config('services.google-maps.api_key')) ?>"
                    class="absolute w-full h-full object-cover">
            </div>
        <?php endif; ?>

        <div class="dropdown" style="position:absolute; top:<?= e(($show_map) ? '210px' : '10px') ?>; right:0px;">
            <a data-toggle="dropdown" style="font-size:18px;"><i class="fa fa-fw fa-ellipsis-v"></i></a>
            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                <li><a href="<?= e(route('backend.member.edit', $orderModel->member->id)) ?>"><i class="fa fa-fw fa-vcard-o"></i> View Supporter</a></li>
                <?php if($orderModel->member->userCan('login')): ?>
                    <li><a href="<?= e(route('backend.members.login', $orderModel->member->id)) ?>" target="_blank"><i class="fa fa-lock fa-fw"></i> Login as <?= e((trim($orderModel->member->first_name) != '') ? $orderModel->member->first_name : 'Supporter') ?></a></li>
                <?php endif; ?>
                <?php if($orderModel->member->userCan('merge')): ?>
                    <li><a href="#" data-toggle="modal" data-target="#mergeAccount"><i class="fa fa-code-fork fa fa-flip-vertical fa-fw"></i> Merge With...</a></li>
                <?php endif; ?>
                <li><a href="#" data-toggle="modal" data-target="#linkAccount"><i class="fa fa-fw fa-exchange"></i> Switch Supporter</a></li>
                <li><a href="#modal-create-member" data-toggle="modal"><i class="fa fa-fw fa-user-plus"></i> Create Supporter from Contribution</a></li>
            </ul>
        </div>

        <div class="text-center" style="padding-top:<?= e(($show_map) ? '110px' : '10px') ?>;">
            <span class="fa-stack fa-4x">
                <i class="fa fa-circle fa-stack-2x" style="color:#fff;"></i>
                <i class="fa <?= e($orderModel->member->fa_icon) ?> fa-stack-1x"></i>
            </span>
        </div>

        <div class="text-center bottom-gutter">
            <div class="text-lg">
                <a href="<?= e(route('backend.member.edit', $orderModel->member->id)) ?>"><?= e($orderModel->member->display_name) ?></a>
            </div>

            <?php if($orderModel->member->accountType): ?>
                <div class="my-1">
                    <span class="label label-white label-outline"><?= e($orderModel->member->accountType->name) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="bottom-gutter">
            <ul class="fa-ul">
                <?php if($orderModel->member->display_bill_address): ?>
                    <li class="my-1">
                        <i class="fa fa-li fa-map-marker"></i> <?= e($orderModel->member->display_bill_address) ?>
                    </li>
                <?php endif; ?>

                <?php if($orderModel->member->email): ?>
                    <li class="my-1">
                        <i class="fa fa-li fa-envelope-o"></i> <a href="mailto:<?= e($orderModel->member->email) ?>"><?= e($orderModel->member->email) ?></a>
                    </li>
                <?php endif; ?>

                <?php if($orderModel->member->bill_phone): ?>
                    <li class="my-1">
                        <i class="fa fa-li fa-phone"></i> <a href="tel:<?= e($orderModel->member->bill_phone) ?>"><?= e($orderModel->member->bill_phone) ?></a>
                    </li>
                <?php endif; ?>

                <?php $groups = $orderModel->member->groups->where('pivot.is_active', true); ?>

                <?php if($groups->count() > 0): ?>
                    <?php if($groups->count() > 0 && $groups->count() < 6): ?>
                        <?php foreach($groups as $group): ?>
                            <li class="my-1">
                                <i class="fa fa-li fa-users"></i> <?= e($group->name) ?><br>
                                <?php if($group->pivot->end_date): ?><small>(Expires <?= e(fromUtcFormat($group->pivot->end_date, 'auto')) ?>)</small><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php elseif($groups->count() > 0 && $groups->count() > 6): ?>
                        <li class="my-1">
                            <i class="fa fa-li fa-users"></i> (<?= e($groups->count()) ?>) Groups
                        </li>
                    <?php else: ?>
                        <li class="my-1">
                            <i class="fa fa-li fa-users"></i> No Groups
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="section-header">Activity</div>

        <div class="bottom-gutter">
            <div class="row">
                <?php if($orderModel->member->orders->count() > 0): ?>
                    <div class="col-xs-12 stat-xs">
                        <div class="stat-value text-bold"><?= e(toLocalFormat($orderModel->member->orders->min('ordered_at'), 'humans')) ?></div>
                        <div class="stat-label">First Payment</div>
                    </div>
                    <div class="col-xs-6 stat-xs">
                        <div class="stat-value text-bold"><?= e(money($orderModel->member->orders->sum('functional_total'))) ?></div>
                        <div class="stat-label">Lifetime Total</diV>
                    </div>
                    <div class="col-xs-6 stat-xs">
                        <div class="stat-value text-bold"><?= e(number_format($orderModel->member->orders->count())) ?></div>
                        <div class="stat-label">Contributions</diV>
                    </div>
                <?php else: ?>
                    <div class="col-xs-6 stat-xs">
                        <div class="stat-value text-bold"><?= e(money(0)) ?></div>
                        <div class="stat-label">Lifetime Total</diV>
                    </div>
                    <div class="col-xs-6 stat-xs">
                        <div class="stat-value text-bold">0</div>
                        <div class="stat-label">Contributions</diV>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(count($orderModel->member->recurringPaymentProfiles) > 0): ?>
            <div class="bottom-gutter">
                <div class="section-header">Recurring</div>
                <div class="account-list">
                    <?php foreach($orderModel->member->recurringPaymentProfiles as $rpp): ?>
                        <div class="account-item">
                            <div class="pull-right"><i class="fa <?= e($rpp->paymentMethod->fa_icon) ?>"></i> <?= e($rpp->payment_string) ?></div>
                            <div class="text-bold"><a href="/jpanel/recurring_payments/<?= e($rpp->profile_id) ?>"><?= e($rpp->description) ?></a></div>
                            <div class="text-sm">Next charge: <?= e($rpp->next_billing_date) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php $ticket_orders = $orderModel->member->orders()->orderBy('ordered_at', 'desc')->with('items.variant.product', 'items.checkIns')->whereHas('items.variant.product', function ($q) { return $q->where('allow_check_in', 1); })->limit(3)->get(); ?>

        <?php if(count($ticket_orders) > 0): ?>
            <div class="bottom-gutter">
                <div class="section-header">Recent Tickets</div>
                <div class="account-list">
                    <?php foreach($ticket_orders as $order): ?>
                        <?php foreach($order->items as $item): ?>
                            <?php if(!$item->variant->product->allow_check_in) { continue; } ?>
                            <div class="account-item">
                                <div class="text-bold"><a target="_blank" href="<?= e(route('backend.orders.checkin', ['o' => $order->getKey(), 'i' => $item->getKey()])) ?>"><i class="fa fa-ticket"></i> <?= e($item->description) ?></a></div>
                                <div class="text-sm">
                                    <span class="pull-right"><i class="fa <?= e($order->fa_icon) ?>"></i> <?= e(money($item->total, $order->currency_code)) ?></span>
                                    <?= e($order->source) ?> on <?= e($order->ordered_at) ?>
                                </div>
                                <?php if (count($item->checkIns) > 0): ?>
                                    <div><span class="label label-white label-outline"><i class="fa fa-check"></i> Checked In</span></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="bottom-gutter">
            <div class="section-header">Recent Contributions</div>
            <?php $recentPayments = $orderModel->member->orders()->orderBy('ordered_at', 'desc')->limit(3)->get(); ?>
            <?php if(count($recentPayments) == 0): ?>
                No Recent Contributions
            <?php else: ?>
                <div class="account-list">
                    <?php foreach($recentPayments as $o): ?>
                        <div class="account-item">
                            <div class="pull-right"><i class="fa <?= e($o->fa_icon) ?>"></i> <?= e(money($o->totalamount, $o->currency)) ?></div>
                            <div class="text-bold"><a href="<?= e(route('backend.orders.edit', $o)) ?>"><?= e($o->invoicenumber) ?></a></div>
                            <div class="text-sm"><?= e($o->source) ?> on <?= e($o->ordered_at) ?> <small>(<?= e($o->ordered_at->diffForHumans()) ?>)</small></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php $matches = \Ds\Models\Member::findCloseMatchesTo($orderModel, $orderModel->member_id); ?>

        <?php if(count($matches) > 0): ?>
            <div class="bottom-gutter">
                <div class="section-header">
                    <div class="pull-right"><a href="#" data-placement="left" data-toggle="popover" data-trigger="hover" data-content="These accounts are being listed<br>because they share the same:<br>&nbsp;&nbsp;<i class='fa fa-check'></i> Email, or<br>&nbsp;&nbsp;<i class='fa fa-check'></i> First &amp; Last Name, or<br>&nbsp;&nbsp;<i class='fa fa-check'></i> Last Name &amp; ZIP<br>as the contribution placed."><i class="fa fa-question-circle"></i></a></div>
                    Similar Accounts
                </div>
                <?php if(count($matches) == 0): ?><div class="account-list">
                    <div class="text-muted">
                        <i class="fa fa-exclamation-circle"></i> No Close Matches Found
                    </div>
                    <div class="text-center top-gutter">
                        <a href="#" data-toggle="modal" data-target="#linkAccount" class="btn btn-info btn-xs"><i class="fa fa-search"></i> Search Manually</a>
                    </div>
                <?php else: ?>
                    <!--<div class="collapse in" id="show-all-matches-label">
                        <a href="#show-all-matches" data-toggle="collapse">
                            <span class="badge badge-info"><?= e(count($matches)) ?></span> Possible Matches
                        </a>
                    </div>-->
                    <div class="collapse in" id="show-all-matches">
                        <div class="account-list">
                            <?php foreach($matches as $member): ?>
                                <div class="account-item">
                                    <?php if(dpo_is_enabled() && $member->donor_id): ?>
                                        <div class="text-sm pull-right flex items-center"><?= e($member->donor_id) ?> <img src="/jpanel/assets/images/dp-white.png" class="ml-1 -mt-[1px]" style="height:14px; width:auto;"></div>
                                    <?php endif; ?>
                                    <div class="text-bold"><a onclick="$.confirm('Are you sure you want to link this contribution and all associated data to a different supporter?', function(){ window.location = '<?= e(route('backend.orders.link_member', ['id' => $orderModel->getKey(), 'member_id' => $member->id])) ?>'; }, 'warning', 'fa-link');"><i class="fa fa-link"></i> <?= e($member->display_name) ?></a></div>
                                    <div class="text-sm">
                                        <?php if($member->display_bill_address): ?><?= e($member->display_bill_address) ?><br><?php endif; ?>
                                        <?php
                                            $meta = [];
                                            if ($member->email) $meta[] = $member->email;
                                            if ($member->bill_phone) $meta[] = $member->bill_phone;
                                        ?>
                                        <?= e(implode(' - ', $meta)); ?>
                                    </div>
                                    <?php if(!$member->is_active): ?>
                                        <div class="text-sm"><i class="fa fa-trash"></i> DELETED</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (dpo_is_enabled() && $orderModel->member->donor_id): ?>
            <div class="bottom-gutter" style="margin-bottom:65px;">
                <div class="section-header"><img src="/jpanel/assets/images/dp-white.png" style="height:16px; margin-right:5px; vertical-align:bottom; width:auto;"> Donor Record</div>
                <div>
                    <div class="pull-right text-bold"><a href="#" class="dp-donor" data-donor="<?= e($orderModel->member->donor_id) ?>"><?= e($orderModel->member->donor_id) ?></a></div>
                    <div class="">Donor ID</div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal fade modal-primary" id="mergeAccount">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-code-fork fa fa-flip-vertical fa-fw"></i> Merge Supporter</h4>
                </div>
                <form class="form-horizontal" method="post" action="<?= e(route('backend.member.merge', $orderModel->member->id)) ?>">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong><i class="fa fa-exclamation-circle"></i> Caution:</strong>&nbsp;&nbsp;You are about to delete this supporter and merge all its associated data into another supporter.
                            <ul>
                                <li class="text-danger"> This supporter will be merged and all of its information (name, email, billing, shipping, etc) will be deleted</li>
                                <li class="text-danger"> The master supporter's information (name, email, billing, shipping, etc) will not be impacted</li>
                                <li class="text-danger"> All of this supporter's orders, sponsorships, recurring payments, past payments and payment methods will now belong to the master supporter below</li>
                            </ul>
                        </div>

                        <p>Choose the supporter you want to merge this master supporter into:</p>
                        <br>
                        <div class="form-group">
                            <label for="mergeaccount-member_id" class="col-sm-4 control-label">Master Supporter</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control ds-members" id="mastermemberid" name="master_member_id" placeholder="Search for a supporter...">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Merge Supporter</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php endif; ?>

<div class="modal fade modal-primary" id="modal-create-member">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user-plus fa-fw"></i> Create Supporter</h4>
            </div>
            <div class="modal-body" style="color:#000;">
                <p class="text-center">
                    <i class="fa fa-user-circle fa-3x" style="margin-bottom:7px;"></i><br>
                    <?= dangerouslyUseHTML($orderModel->billing_address_html) ?>
                </p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary btn-block" href="<?= e(route('backend.orders.create_member', $orderModel)) ?>"><i class="fa fa-user-plus fa-fw"></i> Create Supporter</a>
                <a class="btn btn-primary btn-block" href="<?= e(route('backend.orders.create_member', ['id' => $orderModel->getKey(), 'redirect'])) ?>"><i class="fa fa-user-plus fa-fw"></i> Create &amp; View Supporter</a>
                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
