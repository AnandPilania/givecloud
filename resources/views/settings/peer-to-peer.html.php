
<form class="form-horizontal" action="/jpanel/settings/peer-to-peer/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Peer-to-Peer

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
            <i class="fa fa-university"></i> Peer-to-Peer
        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div class="panel-sub-title"><i class="fa fa-users"></i> Peer-to-Peer</div>
                    <div class="panel-sub-desc">
                        Allow donors to create their own donation pages.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <div class="form-group">
                        <label for="meta1" class="col-md-4 control-label">Enable</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="p2p_enabled" <?= e((sys_get('p2p_enabled') == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('.pdf-only').removeClass('hide'); else $('.pdf-only').addClass('hide');">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div></div>

</form>
