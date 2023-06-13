<div class="modal fade modal-info" id="update-item" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Change Product</h4>
            </div>
            <form method="post" action="{{ route('backend.orders.editItem', $order) }}">
                @csrf
                <input type="hidden" name="item_id" value="">
                <div class="modal-body">

                    <div class="alert alert-warning">
                        This function was designed to replace an item with an item with the same price. The price CANNOT be adjusted or refunded at the item level. If you need the price to change, you'll need to refund the whole contribution and have it reprocessed.
                    </div>

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>New Product</label>
                                <input class="form-control ds-variants" name="new_variant_id">
                            </div>
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Change Product</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
