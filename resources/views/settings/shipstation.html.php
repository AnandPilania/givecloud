
<form action="/jpanel/settings/shipstation/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            ShipStation

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
            </div>
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12 col-lg-8 col-lg-offset-2">

<div class="form-horizontal">

    <?= dangerouslyUseHTML(app('flash')->output()) ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-lock"></i> Custom Store
        </div>
        <div class="panel-body">
            <p>If you haven't already created a custom store to work with your Givecloud site, follow these steps:</p>
            <ol>
                <li>Log in to your ShipStation account.</li>
                <li>From the Welcome page, click Connect a channel.</li>
                <li>Using the Search box, search for Custom Store, and then click the Custom Store icon. </li>
                <li>
                    In the Connect your Custom Store box, enter the following information:
                    <div class="row" style="max-width:600px">
                        <div class="form-group" style="margin-top:20px">
                            <label for="shipstationUrl" class="col-md-4 control-label">URL to Custom XML Page</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="shipstationUrl" value="<?= e(secure_site_url('shipstation.xml')) ?>" readonly onclick="this.select()">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="shipstationUsername" class="col-md-4 control-label">Username</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="shipstationUsername" value="<?= e(sys_get('shipstation_user')) ?>" readonly onclick="this.select()">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="shipstationPassword" class="col-md-4 control-label">Password</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="shipstationPassword" value="<?= e(sys_get('shipstation_pass')) ?>" readonly onclick="this.select()">
                            </div>
                        </div>
                    </div>
                </li>
                <li>Click Test Connection. ShipStation should respond with a confirmation.</li>
                <li>Click Connect to save the configuration and close the dialogue box.</li>
            </ol>
        </div>
    </div>
</div>
