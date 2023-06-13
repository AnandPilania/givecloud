<div class="modal fade modal-primary" id="modal-create-member">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user-plus fa-fw"></i> Create Supporter</h4>
            </div>
            <div class="modal-body" style="color:#000;">
                <p class="text-center">
                    <i class="fa fa-user-circle fa-3x mb-2"></i><br>
                    {!! $order->billing_address_html  !!}
                </p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary btn-block" href="{{ route('backend.orders.create_member', $order) }}"><i class="fa fa-user-plus fa-fw"></i> Create Supporter</a>
                <a class="btn btn-primary btn-block" href="{{ route('backend.orders.create_member', ['id' => $order->getKey(), 'redirect']) }}"><i class="fa fa-user-plus fa-fw"></i> Create &amp; View Supporter</a>
                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
