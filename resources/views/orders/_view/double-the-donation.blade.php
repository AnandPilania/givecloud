@if(sys_get('double_the_donation_enabled') && $order->doublethedonation_registered)
    <div class="panel panel-basic">
    <div class="panel-body">
        <div class="panel-sub-title">Double the Donation</div>
        <div class="row">
            <div class="col-sm-12">
                <div class="flex justify-center my-4 text-gray-400" data-dtd="fetching">
                    <div class="text-xl"><i class="fa fa-spinner-third animate-spin mr-2"></i> Fetching...</div>
                </div>
                <div class="flex justify-center my-4 text-gray-400 hide" data-dtd="no-match">
                    <div class="text-xl"><i class="fa fa-circle-xmark mr-2"></i> No Match</div>
                </div>
                <div class="hide" data-dtd="loaded">
                    <p class="text-lg text-gray-800" data-dtd="company-name">N/D</p>
                    <p class="text-xs text-gray-400">Employer</p>

                    <p class="mt-4 text-gray-800" data-dtd="status">N/D</p>
                    <p class="text-xs text-gray-400">Status</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
