<div class="modal fade modal-danger" id="sync-to-dp-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-exchange fa-fw"></i> {{ trim($order->alt_contact_id) === '' ? 'Sync' : 'Re-Sync' }} With DP</h4>
            </div>

            <form method="get" action="{{ route('backend.orders.push_to_dpo') }}">
                <input type="hidden" name="i" value="{{ $order->id }}">

                <div class="modal-body">

                    <!-- first sync -->
                    @if(!$order->alt_contact_id)
                    Are you sure you want to sync this contribution to DonorPerfect?
                    <!-- re-sync -->
                    @elseif($hasTributes)
                        <span class="text-danger"><i class="fa fa-exclamation-circle"></i>
                            Note: Re-syncing may result in tributes being duplicated in DonorPerfect.</span>
                    <br><br>
                        Are you sure you want to re-sync this contribution to DonorPerfect?
                    @else
                        Are you sure you want to re-sync this contribution to DonorPerfect?
                    @endif

                    <hr>
                    <div class="form-group">
                        <label>Match to Donor ID: <small>(Optional)</small></label>
                        <input type="tel" class="form-control" name="donor_id" value="" maxlength="11" placeholder="DP Donor ID" />
                        <small class="text-muted">If you want Givecloud to use a specific donor, you can specify it here. <span class="text-info">Leave this blank to let Givecloud match the donor for you.</span></small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-exchange"></i> {{ $order->alt_contact_id ? 'Resync' : 'Sync' }}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
