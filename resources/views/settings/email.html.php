
<form class="form-horizontal" action="<?= e(route('backend.settings.email_save')) ?>" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Email

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
            <i class="fa fa-lock"></i> Customize
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-lock"></i> Customize</div>
                    <div class="panel-sub-desc">
                        Customize how your emails are sent from the email servers.

                        <br /><br />
                        <span class="text-info">
                            <i class="fa fa-exclamation-circle"></i> <strong>Note:</strong>
                            Emails are sent from notifications@givecloud.co by default in order to guarantee deliverability.
                            You can change who they are sent from with a few technical exceptions. <strong>The address you use can't have a
                            DMARC or SPF configuration that will reject messages.</strong>
                        </span>
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">From Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="email_from_name" value="<?= e(sys_get('email_from_name')) ?>" maxlength="" />
                            <small class="text-muted">This is the name that displays in your supporters inbox when they receive an email notification.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">From Address</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                <input type="text" class="form-control" name="email_from_address" value="<?= e(sys_get('email_from_address')) ?>" maxlength="" />
                            </div>
                            <small class="text-muted">This is the email that displays in your supporters inbox when they receive an email notification.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">Reply-to Address</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                <input type="text" class="form-control" name="email_replyto_address" value="<?= e(sys_get('email_replyto_address')) ?>" maxlength="" />
                            </div>
                            <small class="text-muted">When someone clicks 'Reply' on an email received, this is the email address that will appear in the 'To:' field.</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-gear"></i> System Emails
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-gear"></i> System Emails</div>
                    <div class="panel-sub-desc">
                        Customize each of your email notifications.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">

                    <?php $_cat = ''; ?>
                    <?php foreach ($system_emails as $email): ?>
                        <?php if($email->category != $_cat): ?><?= dangerouslyUseHTML(($_cat !== '') ? '<br>' : '') ?><h5 class="mb-3"><?= e($email->category) ?></h5><?php endif ?>
                        <p class="mb-3"><a href="/jpanel/emails/edit?i=<?= e($email->id) ?>" <?php if(!$email->is_active): ?>class="text-danger"<?php endif; ?>><i class="fa fa-search"></i> <strong><?= e($email->name) ?></strong></a><?php if(!$email->is_active): ?>&nbsp;&nbsp;<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Disabled</span><?php endif; ?><br><small class=" <?php if(!$email->is_active): ?>text-danger<?php else: ?>text-muted<?php endif; ?>"><?= e($email->hint) ?></small></p>

                        <?php $_cat = $email->category ?>
                    <?php endforeach; ?>

                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading visible-xs">
            <i class="fa fa-pencil-square-o"></i> Custom Emails
        </div>
        <div class="panel-body">

            <div class="row">

                <div class="col-sm-6 col-md-4">
                    <div class="panel-sub-title hidden-xs"><i class="fa fa-pencil-square-o"></i> Custom Emails</div>
                    <div class="panel-sub-desc">
                        Customize each of your email notifications.
                    </div>
                </div>

                <div class="col-sm-6 col-md-8">
                <div class="col-md-6 col-md-offset-1">

                    <?php if(count($custom_emails) > 0): ?>
                        <?php foreach ($custom_emails as $email): ?>
                            <p class="mb-3"><a href="/jpanel/emails/edit?i=<?= e($email->id) ?>" <?php if(!$email->is_active): ?>class="text-danger"<?php endif; ?>><i class="fa fa-search"></i> <strong><?= e($email->name) ?></strong></a><?php if(!$email->is_active): ?>&nbsp;&nbsp;<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Disabled</span><?php endif; ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="mb-3 text-muted"><i class="fa fa-frown-o fa-3x pull-left fa-fw"></i> You haven't created any custom emails yet!<br>Get started below.</p><br>
                    <?php endif; ?>

                    <p><a href="/jpanel/emails/add" class="btn btn-info btn-sm"><span class="text-white"><i class="fa fa-plus"></i> New Email</span></a></p>

                </div>
                </div>

            </div>
        </div>
    </div>

<!--
custom_smtp (1/0)
smtp_email
smtp_name
smtp_host
smtp_port
smtp_user
smtp_password
smtp_enc_type (ssl/tls)
-->

</div></div>

</form>
