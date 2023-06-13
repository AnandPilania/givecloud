@if(sys_get('salesforce_enabled') && $order->is_processed == 1)
<div class="panel panel-basic">
    <div class="panel-body">
        <div class="panel-sub-title">Salesforce</div>
        <div class="row">
            <div class="col-sm-12">
                <a
                    href="{{ sprintf('%s/lightning/r/%s/%s/view',
                                    app('forrest')->getInstanceURL(),
                                    app(\Ds\Domain\Salesforce\Models\Contribution::class)->getTable(),
                                    $salesforceReference->reference
                                ) }}"
                    target="_blank">
                    View contribution in Salesforce
                </a>
            </div>
        </div>
    </div>
</div>
@endif
