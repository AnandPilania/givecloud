<div class="modal fade modal-info" id="update-gift-aid_eligibility" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Update Gift Aid Eligibility</h4>
            </div>
            <form method="post" action="{{ route('backend.orders.editGiftAidEligibility', $order) }}">
                @csrf
                <input type="hidden" name="item_id" value="">
                <div class="modal-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Gift Aid Eligibility for this Item:</label>
                                <select class="form-control" name="gift_aid_eligible">
                                    <option value="0">Ineligible</option>
                                    <option value="1">Eligible</option>
                                </select>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Update Eligibility</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
