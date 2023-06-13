<div class="modal fade modal-info" id="update-item-fields" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Item Data</h4>
            </div>
            <form method="post" action="{{ route('backend.orders.editItemFields', $order) }}">
                @csrf
                <div class="modal-body" id="update-item-fields-container"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info"><i class="fa fa-check"></i> Save Changes</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
