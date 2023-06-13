
<form class="form-horizontal" action="/jpanel/settings/gift_aid/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Gift Aid

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-university"></i> Gift Aid
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-university"></i> Gift Aid</div>
                    <div class="panel-sub-desc">
                        Collect Gift Aid eligibility from donors and generate reports.

                        <br /><br />
                        <span class="text-info"><i class="fa fa-exclamation-circle"></i> <strong>Note:</strong> Make sure you examine each of your product's to be sure they are set to support Gift Aid.</span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="gift_aid" <?= e((sys_get('gift_aid') == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('.pdf-only').removeClass('hide'); else $('.pdf-only').addClass('hide');">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

</form>
