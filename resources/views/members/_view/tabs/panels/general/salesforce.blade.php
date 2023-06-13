<div class="col-sm-6">
    <div class="panel panel-info">
        <div class="panel-heading">
            Salesforce Integration
        </div>

        <div class="panel-body">
            <div class="row row-padding-sm">
                <div class="form-group col-sm-12">
                    <label>Contact ID</label>
                    <input
                        class="form-control"
                        type="text"
                        disabled
                        readonly
                        value="{{ $salesforceReference->reference ?? null }}"
                        style="max-width: 320px">
                    @if ($salesforceReference)
                        <div class="help-block">
                            <a
                                href="{{ sprintf('%s/lightning/r/%s/%s/view',
                                    app('forrest')->getInstanceURL(),
                                    app(\Ds\Domain\Salesforce\Models\Supporter::class)->getTable(),
                                    $salesforceReference->reference
                                ) }}"
                                target="_blank">
                                <i class="fa fas fa-search"></i> View contact in Salesforce
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
