
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <span class="page-header-text"><?= e($pageTitle) ?></span>

            <div class="pull-right">
                <?php if (! is_super_user($user)): ?><a onclick="$('#user_form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-sm hidden-xs"> Save</span></a><?php endif; ?>
                <?php if ($user->exists && $user->id != user('id') && user()->userCan('user.edit')): ?><a onclick="j.user.onDelete();" class="btn btn-danger"><i class="fa fa-trash"></i></a><?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<?php if ($user->exists && is_super_user($user)): ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> This super user is locked and cannot be edited.</div>
<?php else: ?>


<?php if ($user->exists): ?>

    <!-- Modal -->
    <div class="modal fade modal-info" id="resetPasswordModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-refresh"></i> Reset Password</h4>
                </div>
                <div class="modal-body">
                    A Password Reset email will be sent to <strong><?= e($user->email) ?></strong> with instructions on how to reset their own password.
                </div>
                <div class="modal-footer">
                    <form action="/jpanel/users/<?= e($user->id) ?>/reset" method="post">
                        <?= dangerouslyUseHTML(csrf_field()) ?>
                        <input type="hidden" name="email" value="<?= e($user->email) ?>">
                        <button type="submit" class="btn btn-info"><i class="fa fa-send"></i> Send Email</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade modal-danger" id="disable2faModal" tabindex="-1" role="dialog">
        <div class="modal-dialog max-w-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-shield"></i> Disable 2FA (Two Factor Authentication)</h4>
                </div>
                <div class="modal-body">
                    <?php if (sys_get('two_factor_authentication') === 'force'): ?>
                        After disabling 2FA the user will be forced to enable it again the next time they login.
                    <?php elseif (sys_get('two_factor_authentication') === 'prompt'): ?>
                        After disabling 2FA the user will be prompted to enable it again the every time they login but will not be
                        forced to enable it again.
                    <?php else: ?>
                        After disabling 2FA the user not be forced or prompted to enable it again. They'll need to enable it
                        from their profile.
                    <?php endif ?>
                </div>
                <div class="modal-footer">
                    <form action="<?= e(route('backend.users.disable-two-factor-authentication', [$user->id])) ?>" method="post">
                        <?= dangerouslyUseHTML(csrf_field()) ?>
                        <?= dangerouslyUseHTML(method_field('delete')) ?>
                        <button type="submit" class="btn btn-danger">Disable</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php endif ?>

    <?php if (feature('givecloud_pro')): ?>
    <ul class="nav nav-tabs">
        <li class="active"><a href="#general" data-toggle="tab"><i class="fa fa-pencil fa-fw"></i> General</a></li>
        <li><a href="#permissions" data-toggle="tab"><i class="fa fa-lock fa-fw"></i> Permissions</a></li>
    </ul>

    <br />
    <?php endif; ?>


            <form name="user" id="user_form" method="post" action="<?= e($action) ?>" autocomplete="off">
                <?= dangerouslyUseHTML(csrf_field()) ?>
                <input type="hidden" name="id" value="<?= e($user->id) ?>" />

            <div class="tab-content">

                <div class="tab-pane fade in active" id="general">

                    <div class="row">
                        <div class="col-sm-12">

                            <div class="panel panel-default">
                                <div class="panel-body">

                                    <div class="bottom-gutter">
                                        <div class="panel-sub-title"><i class="fa fa-user"></i> User Info</div>
                                        <div class="panel-sub-desc">
                                            General information specific to the user.
                                        </div>
                                    </div>

                                    <div class="form-horizontal">

                                        <div class="form-group">
                                            <label for="firstName" class="col-sm-3 control-label">Name</label>
                                            <div class="col-sm-3">
                                                <input type="text" class="form-control" name="firstName" id="firstName" placeholder="First Name" value="<?= e(old('firstName', $user->firstname)) ?>" />
                                            </div>
                                            <div class="col-sm-3">
                                                <input type="text" class="form-control" name="lastName" id="lastName" placeholder="Last Name" value="<?= e(old('lastName', $user->lastname)) ?>" />
                                            </div>
                                        </div>



                                        <div class="form-group">
                                            <label for="email" class="col-sm-3 control-label">Phone</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" name="primaryPhoneNumber" id="primaryPhoneNumber" value="<?= e(old('primaryPhoneNumber', $user->primaryphonenumber)) ?>" />
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="email" class="col-sm-3 control-label">Phone (alt)</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" name="alternatePhoneNumber" id="alternatePhoneNumber" value="<?= e(old('alternatePhoneNumber', $user->alternatephonenumber)) ?>" />
                                            </div>
                                        </div>

                                    </div><!--form-horizontal-->
                                </div><!--panel-body-->
                            </div><!--panel-->


                            <div class="panel panel-default">
                                <div class="panel-body">

                                    <div class="bottom-gutter">
                                        <div class="panel-sub-title"><i class="fa fa-lock"></i> Login Info</div>
                                        <div class="panel-sub-desc">
                                            These are the credentials used to log into your account.
                                        </div>
                                    </div>

                                    <div class="form-horizontal">
                                        <div class="form-group hide">
                                            <label for="email" class="col-sm-3 control-label">Option</label>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" class="checkbox" name="isAdminUser" id="isAdminUser" value="1" onclick="j.user.toggleUserType();" checked="checked" /> This is a JPanel user
                                                </label>
                                            </div>
                                        </div>

                                        <style>
                                            #___email, #___password { width:0px; height:0px; position:absolute; top:-3000px; left:-3000px; }
                                        </style>

                                        <input type="email" name="___email" id="___email" value="" tabindex="-1">
                                        <input type="password" name="___password" id="___password" value="" tabindex="-2">

                                        <div class="form-group">
                                            <label for="email" class="col-sm-3 control-label">Email</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" name="email" autocomplete="off" id="email" value="<?= e(old('email', $user->email)) ?>" />
                                                <small class="text-muted">Your login is your email address. The email address must be valid and unique. You cannot have two users with the same email address.</small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="email" class="col-sm-3 control-label">Password</label>
                                            <div class="col-sm-6">
                                                <?php if ($user->exists): ?>
                                                    <a href="#" class="btn btn-info" data-toggle="modal" data-target="#resetPasswordModal"><i class="fa fa-envelope-o"></i> Send Password Reset Notification</a>

                                                    <?php if ($user->two_factor_secret): ?>
                                                        <a href="#" class="ml-3 btn btn-danger" data-toggle="modal" data-target="#disable2faModal">Disable 2FA</a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <input type="password" class="form-control" name="password" autocomplete="off" id="password" value="" />
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div><!--form-horizontal-->
                                </div><!--panel-body-->

                                <?php if (feature('givecloud_pro') && $user->exists && $user->is_account_admin): ?>
                                    <hr style="margin-top:5px">
                                    <div class="panel-body">
                                        <div class="form-horizontal">

                                            <div id="userApiToken" class="form-group has-feedback">
                                                <label for="api_token" class="col-sm-3 control-label">API Key</label>
                                                <div class="col-sm-7">
                                                    <div class="input-wrap <?= e($user->api_token ? '' : 'hide') ?>" style="margin-bottom:8px">
                                                        <input type="password" class="form-control password" name="api_token" id="api_token" value="<?= e($user->api_token); ?>" readonly>
                                                        <i class="glyphicon glyphicon-eye-open form-control-feedback"></i>
                                                    </div>
                                                    <button type="button" class="btn btn-info btn-sm" onclick="j.user.regenerateKey(<?= e($user->id) ?>)">
                                                        <?= e($user->api_token ? 'Regenerate' : 'Generate') ?> key
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div><!--panel-body-->
                                <?php endif; ?>

                            </div><!--panel-->


                            <?php if (feature('givecloud_pro')): ?>
                            <div class="panel panel-default">
                                <div class="panel-body">

                                    <div class="bottom-gutter">
                                        <div class="panel-sub-title"><i class="fa fa-shield"></i> Account Owner</div>
                                        <div class="panel-sub-desc">
                                            Indicates whether this user is an account owner. Account owners are automatically assigned all permissions. You can have more than one account owner.
                                        </div>
                                    </div>

                                        <div class="form-horizontal">
                                            <div class="form-group">
                                                    <label class="col-md-3 control-label">Account Owner</label>
                                                    <div class="col-md-8">
                                                        <input type="hidden" name="is_account_admin" value="0">
                                                        <input type="checkbox" class="switch" value="1" name="is_account_admin" <?= e((old('is_account_admin', $user->is_account_admin) == 1) ? 'checked' : '') ?> onchange="if ($(this).is(':checked')) $('.user-permissions').addClass('hide'); else $('.user-permissions').removeClass('hide');if ($(this).is(':checked')) $('.all-permissions').removeClass('hide'); else $('.all-permissions').addClass('hide');" >
                                                        <small class="text-muted"><br></small>
                                                    </div>
                                            </div>
                                        </div><!--form-horizontal-->
                                </div><!--panel-body-->
                            </div><!--panel-->
                            <?php endif; ?>

                            <div class="panel panel-default">
                                <div class="panel-body">

                                    <div class="bottom-gutter">
                                        <div class="panel-sub-title"><i class="fa fa-envelope"></i> Email Notifications</div>
                                        <div class="panel-sub-desc">
                                            Opt-in to receive email notifications from Givecloud.
                                        </div>
                                    </div>

                                    <div class="form-horizontal">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Givecloud Updates</label>
                                            <div class="col-md-8">
                                                <input type="checkbox" class="switch" value="1" name="ds_corporate_optin" <?php if (old('ds_corporate_optin', $user->ds_corporate_optin)):?> checked ="checked" <?php endif; ?> >
                                                <div class="inline-block ml-2">
                                                    Receive occasional updates from Givecloud when new features are announced.
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (feature('givecloud_pro') && $user->is_account_admin): ?>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Recurring Payment Summary</label>
                                                <div class="col-md-8">
                                                    <input type="checkbox" class="switch" value="1" name="notify_recurring_batch_summary" <?php if (old('notify_recurring_batch_summary', $user->notify_recurring_batch_summary)):?> checked ="checked" <?php endif; ?> >
                                                    <div class="inline-block ml-2">
                                                        Receive summary email from Givecloud after recurring payments have processed.
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div><!--form-horizontal-->

                                </div><!--panel-body-->
                            </div><!--panel-->

                        </div><!--col-sm-12-->
                    </div><!--row-->
                </div><!--tab-pane-->

                <div class="tab-pane fade" id="permissions">

                    <div class="row">

                                <div class="col-md-12">
                                    <div class="alert alert-warning clearfix all-permissions <?= e((($user->is_account_admin) == 0) ? 'hide' : '') ?>">
                                            <i class="pull-left fa fa-exclamation-circle fa-4x"></i>
                                            <h4 style="margin-bottom:10px;">Notice</h4>
                                            <p>As an Account Owner, all permissions are enabled.</p>
                                    </div>
                                </div>

                                <?php $cat_count = -1 ?>
                                <?php foreach(\Ds\Models\User::permissionStructure() as $permission): ?>

                                    <?php $is_first_cat = (!isset($prev_cat)) ?>
                                    <?php $is_new_cat = ($is_first_cat || $prev_cat !== $permission['category']) ?>
                                    <?php $cat_count += (int) $is_new_cat ?>

                                    <?php /* new heading each time the category changes */ ?>
                                    <?php if($is_new_cat): ?>

                                        <?php if (!$is_first_cat): ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if($cat_count % 2 === 0): ?>
                                                </div><div class="row">
                                            <?php endif; ?>
                                        <?php endif; ?>

                                            <div class="col-md-6">

                                                <div class="panel panel-default user-permissions <?= e((($user->is_account_admin) == 1) ? 'hide' : '') ?>" >
                                                    <div class="panel-heading">
                                                        <?= e($permission['category']) ?>

                                                        <div class="pull-right">
                                                            <button type="button" class="btn btn-default btn-xs" onclick="$('*[category=\'<?= e($permission['category']) ?>\'] input[type=checkbox]').prop('checked',true);"><i class="fa fa-check-square-o"></i> All</button>&nbsp;
                                                            <button type="button" class="btn btn-default btn-xs" onclick="$('*[category=\'<?= e($permission['category']) ?>\'] input[type=checkbox]').prop('checked',false);"><i class="fa fa-square-o"></i> None</button>
                                                        </div>
                                                    </div>
                                                    <div class="panel-body" category="<?= e($permission['category']) ?>">

                                    <?php endif ?>

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="permissions_json[]" value="<?= e($permission['id']) ?>" <?= e((in_array($permission['id'], old('permissions_json', [])) || $user->can($permission['id'])) ? 'checked' : '') ?> > <?= e($permission['name']) ?>
                                        </label>
                                    </div>

                                    <?php $prev_cat = $permission['category'] ?>
                                <?php endforeach ?>

                    </div><!--row-->
                </div><!--tab-pane-->



            </div><!--tab-content-->

            </form>

<?php endif; ?>
