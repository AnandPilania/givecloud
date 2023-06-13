

<?php if ($gift): ?>
<form class="form-sm">

    <div class="row">

        <div class="form-group col-sm-6">
            <label for="address">Donor:</label>
            <div class="form-control"><?= e($gift->donor->first_name.' '.$gift->donor->last_name.' ('.$gift->donor->donor_id.')') ?></div>
        </div>

        <div class="form-group col-sm-3">
            <label for="gift_id">Gift ID:</label>
            <div class="form-control"><?= e($gift->gift_id) ?></div>
        </div>

        <div class="form-group col-sm-3">
            <label for="gift_date">Date:</label>
            <div class="form-control"><?= e($gift->gift_date) ?></div>
        </div>

        <div class="form-group col-sm-3">
            <label for="amount">Amount:</label>
            <div class="form-control"><?= e($gift->amount) ?></div>
        </div>

        <div class="form-group col-sm-3">
            <label for="amount">Fair Market Value:</label>
            <div class="form-control"><?= e($gift->fmv) ?></div>
        </div>

        <div class="form-group col-sm-6">
            <label for="amount">Currency:</label>
            <div class="form-control"><?= e($gift->currency) ?></div>
        </div>

        <div class="form-group col-sm-3">
            <label for="rcpt_type">Receipt Type:</label>
            <div class="form-control"><?= e($gift->rcpt_type) ?></div>
        </div>

        <div class="form-group col-sm-3">
            <label for="reference">Record Type:</label>
            <div class="form-control"><?= e($gift->record_type) ?></div>
        </div>

        <div class="form-group col-sm-6">
            <label for="gl_code">GL Code:</label>
            <div class="form-control"><?= e($gift->gl_code) ?></div>
        </div>

        <div class="form-group col-sm-6">
            <label for="solicit_code">Solicit Code:</label>
            <div class="form-control"><?= e($gift->solicit_code) ?></div>
        </div>

        <div class="form-group col-sm-6">
            <label for="sub_solicit_code">Sub-Solicit Code:</label>
            <div class="form-control"><?= e($gift->sub_solicit_code) ?></div>
        </div>

        <div class="form-group col-sm-6">
            <label for="campaign">Campaign:</label>
            <div class="form-control"><?= e($gift->campaign) ?></div>
        </div>

        <div class="form-group col-sm-6">
            <label for="reference">Reference:</label>
            <div class="form-control"><?= e($gift->reference) ?></div>
        </div>

        <div class="form-group col-sm-6">
            <label for="gift_type">Gift Type:</label>
            <div class="form-control"><?= e($gift->gift_type) ?></div>
        </div>

        <div class="form-group col-sm-12">
            <label for="gift_narrative">Gift Memo:</label>
            <div class="form-control"><?= e($gift->gift_narrative) ?></div>
        </div>

        <div class="form-group col-sm-6 <?= e((sys_get('dp_use_nocalc') == '0') ? 'hidden' : '') ?>" >
            <label for="nocalc">Nocalc:</label>
            <div class="form-control"><?= e($gift->nocalc) ?></div>
        </div>

        <?php if(feature('tax_receipt')): ?>

            <div class="form-group col-sm-4">
                <label for="">Receipt Status:</label>
                <div class="form-control"><?= e($gift->rcpt_status) ?></div>
            </div>

            <div class="form-group col-sm-4">
                <label for="">Receipt Number:</label>
                <div class="form-control"><?= e($gift->rcpt_num) ?></div>
            </div>

            <div class="form-group col-sm-4">
                <label for="">Receipt Date:</label>
                <div class="form-control"><?= e($gift->rcpt_date) ?></div>
            </div>

        <?php endif; ?>
    </div>

    <p class="text-sm text-muted"><a href="#gift-advanced" data-toggle="collapse">Raw Data View</a></p>

    <div id="gift-advanced" class="collapse">
        <hr>
        <div class="row">
            <?php foreach($gift as $udf => $value): ?>
                <?php if($value && is_string($value)): ?>
                    <div class="form-group form-group-sm col-sm-2">
                        <label for="nocalc"><?= e($udf) ?>:</label>
                        <div class="form-control"><?= e($value) ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php foreach($gift->udfs as $udf => $value): ?>
                <?php if($value && is_string($value) && $udf != 'gift_id'): ?>
                    <div class="form-group form-group-sm col-sm-2">
                        <label for="nocalc">udf.<?= e($udf) ?>:</label>
                        <div class="form-control"><?= e($value) ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

</form>

<?php else: ?>
    <div class="text-center text-muted">
        <i class="fa fa-4x fa-frown-o"></i><br />
        This gift does not exist in DonorPerfect.
    </div>
<?php endif; ?>

<hr />
<small>
    <i class="fa fa-external-link-square"></i> Polled from DonorPerfect on <?= e(toLocalFormat('now', 'l, F j, Y \a\t g:ia T')) ?>.
</small>
