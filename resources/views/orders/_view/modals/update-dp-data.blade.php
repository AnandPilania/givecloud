<div class="modal fade modal-info" id="update-dp-data" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Change Donor/Gift Data</h4>
            </div>
            <form class="form-horizontal" method="post" action="{{ route('backend.orders.editDPData', $order) }}">
                @csrf
                <div class="modal-body">

                    <div class="form-group">
                        <label class="col-md-3 control-label">Sync Contribution</label>
                        <div class="col-md-6">
                            <input type="checkbox" class="switch" value="1" name="dp_sync_order" {{ ($order->dp_sync_order) == 1 ? 'checked' : '' }} onchange="if ($(this).is(':checked')) $('.dp-fields-wrapper').removeClass('hide'); else $('.dp-fields-wrapper').addClass('hide');">
                            <br><small class="text-muted">Turn ON to enable syncing this contribution with DonorPerfect.</small>
                        </div>
                    </div>

                    <div class="dp-fields-wrapper {{ $order->dp_sync_order == 0 ? 'hide' : '' }}">
                        <hr>
                        <div class="form-group">
                            <label for="name" class="col-md-3 control-label">Donor ID</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="donor_id" value="{{ $order->alt_contact_id }}" maxlength="11" />
                                <small class="text-muted">This is for reference purposes only. Changing this value will not impact your sync to DonorPerfect.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name" class="col-md-3 control-label">Gift IDs</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control selectize-tags" name="gift_ids" value="{{ $order->alt_transaction_id }}" />
                                <small class="text-muted">This is for reference purposes only. Changing this value will not impact your sync to DonorPerfect.</small>
                            </div>
                        </div>

                        @if($order->alt_data_updated_by)
                        <small class="text-muted">Last updated by {{ $order->altDataUpdatedBy->full_name }} on {{ toLocalFormat($order->alt_data_updated_at, 'M j, Y') }}.</small>
                        @endif
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
