@if(dpo_is_enabled() && $order->is_processed == 1)
<div class="panel panel-basic">
    <div class="panel-body">
        <div class="bottom-gutter-sm">
            @if(user()->can('admin.dpo') && !$order->is_view_only)
            <div class="btn-group pull-right">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-gear"></i>
                </a>
                <ul class="dropdown-menu pull-right">
                    <li><a href="#" data-toggle="modal" data-target="#update-dp-data"><i class="fa fa-fw fa-pencil"></i> Edit Gift/Donor Data</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#" data-toggle="modal" data-target="#sync-to-dp-modal"><i class="fa fa-exchange fa-fw"></i> {{ (trim($order->alt_contact_id) === '') ? 'Sync' : 'Re-Sync' }} to DPO</a></li>
                </ul>
            </div>
            @endif
            <div class="panel-sub-title">DonorPerfect</div>
        </div>
        <div class="row">
            @if(!$order->dp_sync_order)
            <div class="col-sm-12 stat text-muted">
                <div class="stat-value-sm"><i class="fa fa-exclamation-circle"></i> Disabled for this contribution</div>
            </div>
            @elseif($order->is_unsynced)
                <div class="col-sm-12 stat text-center text-danger">
                    <div class="stat-value-sm">
                        <i class="fa fa-exclamation-triangle"></i> Not Synced
                        @if($order->dpo_status_message)
                            <p class="text-sm">{{ $order->dpo_status_message }}</p>
                        @endif

                        @if(!$order->is_view_only && user()->can('admin.dpo'))
                            <div style="margin-top:10px;"><a href="#" data-toggle="modal" data-target="#sync-to-dp-modal" class="btn btn-pill btn-outline btn-danger"><i class="fa fa-refresh"></i> Sync Now</a></div>
                        @endif
                    </div>
                </div>
            @else
                <div class="col-sm-8 stat">
                    @if($order->alt_transaction_id)
                        @php
                            $ids = collect(explode(',',$order->alt_transaction_id));
                            $order->items->each(function($itm)use(&$ids){ if ($itm->alt_transaction_id) { $ids->push($itm->alt_transaction_id); } });
                        @endphp
                        <div class="stat-value-xs">
                           @foreach($ids->unique() as $i => $id)
                                <a href="#" class="btn btn-xs btn-info btn-outline dp-gift" style="margin:0px 3px 3px 0px;" data-gift="{{ $id }}">{{ $id }}</a>
                           @endforeach
                    </div>
                    @else
                        <div class="stat-value-xs text-muted"><i class="fa fa-exclamation-circle"></i> No Gifts</div>
                    @endif

                    <div class="stat-label">Gift IDs</diV>
                </div>
                <div class="col-sm-4 stat">
                    <div class="stat-value-xs"><a href="#" class="btn btn-xs btn-info btn-outline dp-donor" style="margin:0px 3px 3px 0px;" data-donor="{{ $order->alt_contact_id }}">{{ $order->alt_contact_id }}</a></div>
                    <div class="stat-label">Donor ID</diV>
                </div>
            @endif
        </div>
    </div>
</div>
@endif
