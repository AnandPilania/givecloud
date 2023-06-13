<div class="panel panel-default">
    <div class="panel-body">

        <div class="row">
            <div class="col-sm-6 col-md-4 bottom-gutter">
                <div class="panel-sub-title"><i class="fa fa-credit-card"></i> Support</div>
                <div class="panel-sub-desc">
                    Only visible to Givecloud Support Staff
                </div>
            </div>

            <div class="col-sm-6 col-md-8">
                <div class="form-group">
                    <label for="billing_pays_by_cheque" class="col-md-4 control-label">Client Pays By Cheque</label>
                    <div class="col-md-8">
                        <input id="billing_pays_by_cheque" type="checkbox" class="switch" value="1" name="billing_pays_by_cheque" <?= e((sys_get('billing_pays_by_cheque') == 1) ? 'checked' : ''); ?>>
                        <br><small class="text-muted">If enabled, different rules apply when showing past-due amounts in jPanel.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
