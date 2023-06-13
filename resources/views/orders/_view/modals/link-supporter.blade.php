<div class="modal fade modal-primary" id="linkAccount">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user"></i> Link a Supporter</h4>
            </div>
            <form class="form-horizontal" method="get" action="{{ route('backend.orders.link_member', $order) }}">
                <div class="modal-body">
                    <p>Choose the supporter you want this contribution to become linked to.</p>

                    <p class="text-muted"><i class="fa fa-exclamation-circle"></i> Note: This will not change any information on the contribution. It will simply link the contribution to a supporter.</p>

                    <div class="form-group">
                        <label for="linkAccount-member_id" class="col-sm-2 control-label">Supporter</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control ds-members" id="linkAccount-member_id" name="member_id" placeholder="Search for a supporter...">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Link Supporter</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
