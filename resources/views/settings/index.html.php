
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            Advanced Settings

            <div class="pull-right">
                <a onclick="$('#settings_form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i> Save</a>
            </div>
        </h1>
    </div>
</div>

<?php if (request('s')): ?>
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-check fa-fw"></i> Settings updated successfully!
    </div>
<?php endif; ?>

<?php if (request('f')): ?>
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-times fa-fw"></i> Oops! Looks like something went wrong. If this continues, please contact support.
    </div>
<?php endif; ?>

<div class="alert alert-warning">
    <i class="fa fa-exclamation-triangle fa-fw"></i> Changing the settings on this page can adversely affect your site.
</div>

<form class="form-horizontal" id="settings_form" action="/jpanel/settings/save" method="post">
    <?= dangerouslyUseHTML(csrf_field()) ?>

<?php foreach(config('sys.editable') as $category_name => $settings): ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <?= e($category_name) ?>
        </div>
        <div class="panel-body">

            <?php foreach($settings as $setting_name => $setting): ?>
                <?php $setting = (object) $setting ?>

                <div class="form-group">
                    <label class="col-sm-4 control-label" for="field-<?= e($setting_name) ?>"><?= e($setting->label) ?>:</label>
                    <div class="col-sm-8">

                        <?php if($setting->type === 'select'): ?>
                            <select class="form-control" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>">
                                <?php $options = array_combine(explode(',',$setting->option_values),explode(',',$setting->options)); ?>
                                <?php foreach($options as $option => $value): ?>
                                    <option value="<?= e($option) ?>" <?= dangerouslyUseHTML(($option == sys_get($setting_name))?'selected="selected"':'') ?>><?= e($value) ?></option>
                                <?php endforeach ?>
                            </select>
                        <?php elseif($setting->type === 'multi_select'): ?>
                            <select class="form-control" multiple="multiple" size="4" name="<?= e($setting_name) ?>[]" id="field-<?= e($setting_name) ?>">
                                <?php $options = array_combine(explode(',',$setting->options),explode(',',$setting->option_values)); ?>
                                <?php foreach($options as $option => $value): ?>
                                    <option value="<?= e($value) ?>" <?= dangerouslyUseHTML((in_array($value,explode(',',sys_get($setting_name))))?'selected="selected"':'') ?>><?= e($option) ?></option>
                                <?php endforeach ?>
                            </select>
                        <?php elseif($setting->type === 'selectize'): ?>
                            <select class="form-control selectize" multiple="multiple" size="4" name="<?= e($setting_name) ?>[]" id="field-<?= e($setting_name) ?>">
                                <?php $options = array_combine(explode(',',$setting->options),explode(',',$setting->option_values)); ?>
                                <?php foreach($options as $option => $value): ?>
                                    <option value="<?= e($value) ?>" <?= dangerouslyUseHTML((in_array($value,explode(',',sys_get($setting_name))))?'selected="selected"':'') ?>><?= e($option) ?></option>
                                <?php endforeach ?>
                            </select>
                        <?php elseif($setting->type === 'html'): ?>
                            <textarea name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" class="form-control html"><?= e(sys_get($setting_name)) ?></textarea>
                        <?php elseif($setting->type === 'password'): ?>
                            <input type="password" class="form-control" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" value="<?= e(sys_get($setting_name)) ?>" />
                        <?php elseif($setting->type === 'text'): ?>
                            <input type="text" class="form-control" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" value="<?= e(sys_get($setting_name)) ?>" />
                        <?php elseif($setting->type === 'long_text'): ?>
                            <input type="text" class="form-control" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" value="<?= e(sys_get($setting_name)) ?>" />
                        <?php elseif($setting->type === 'date'): ?>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                <input type="text" class="form-control date" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" value="<?= e(sys_get($setting_name)) ?>" />
                            </div>
                        <?php endif; ?>

                        <!-- hint -->
                        <?php if(trim($setting->hint) !== '' || $setting->type === 'multi_select'): ?>
                            <p><?= dangerouslyUseHTML(($setting->type === 'multi_select') ? 'Use CTRL or CMD to select multiple values.<br />' : '') ?><?= e($setting->hint) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>
    </div>

<?php endforeach; ?>


<?php if(is_super_user()): ?>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Protected Settings
            </h1>
        </div>
    </div>

    <?php foreach(config('sys.protected') as $category_name => $settings): ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?= e($category_name) ?>
            </div>
            <div class="panel-body">

                <?php foreach($settings as $setting_name => $setting): ?>
                    <?php $setting = (object) $setting ?>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="field-<?= e($setting_name) ?>"><?= e($setting->label) ?>:</label>
                        <div class="col-sm-5">

                            <?php if($setting->type === 'select'): ?>
                                <select class="form-control" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>">
                                    <?php $options = array_combine(explode(',',$setting->option_values),explode(',',$setting->options)); ?>
                                    <?php foreach($options as $option => $value): ?>
                                        <option value="<?= e($option) ?>" <?= dangerouslyUseHTML(($option == sys_get($setting_name))?'selected="selected"':'') ?>><?= e($value) ?></option>
                                    <?php endforeach ?>
                                </select>
                            <?php elseif($setting->type === 'multi_select'): ?>
                                <select class="form-control" multiple="multiple" size="4" name="<?= e($setting_name) ?>[]" id="field-<?= e($setting_name) ?>">
                                    <?php $options = array_combine(explode(',',$setting->options),explode(',',$setting->option_values)); ?>
                                    <?php foreach($options as $option => $value): ?>
                                        <option value="<?= e($value) ?>" <?= dangerouslyUseHTML((in_array($value,explode(',',sys_get($setting_name))))?'selected="selected"':'') ?>><?= e($option) ?></option>
                                    <?php endforeach ?>
                                </select>
                            <?php elseif($setting->type === 'selectize'): ?>
                                <select class="form-control selectize" multiple="multiple" size="4" name="<?= e($setting_name) ?>[]" id="field-<?= e($setting_name) ?>">
                                    <?php $options = array_combine(explode(',',$setting->options),explode(',',$setting->option_values)); ?>
                                    <?php foreach($options as $option => $value): ?>
                                        <option value="<?= e($value) ?>" <?= dangerouslyUseHTML((in_array($value,explode(',',sys_get($setting_name))))?'selected="selected"':'') ?>><?= e($option) ?></option>
                                    <?php endforeach ?>
                                </select>
                            <?php elseif($setting->type === 'html'): ?>
                                <textarea name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" class="form-control html"><?= e(sys_get($setting_name)) ?></textarea>
                            <?php elseif($setting->type === 'password'): ?>
                                <input type="password" class="form-control" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" value="<?= e(sys_get($setting_name)) ?>" />
                            <?php elseif($setting->type === 'text'): ?>
                                <input type="text" class="form-control" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" value="<?= e(sys_get($setting_name)) ?>" />
                            <?php elseif($setting->type === 'long_text'): ?>
                                <textarea name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" class="form-control font-mono text-xs" rows="4" spellcheck="false"><?= e(sys_get($setting_name)) ?></textarea>
                            <?php elseif($setting->type === 'date'): ?>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control date" name="<?= e($setting_name) ?>" id="field-<?= e($setting_name) ?>" value="<?= e(sys_get($setting_name)) ?>" />
                                </div>
                            <?php endif; ?>

                            <!-- hint -->
                            <?php if(trim($setting->hint) !== '' || $setting->type === 'multi_select'): ?>
                                <p><?= dangerouslyUseHTML(($setting->type === 'multi_select') ? 'Use CTRL or CMD to select multiple values.<br />' : '') ?><?= e($setting->hint) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>

    <?php endforeach; ?>
<?php endif; ?>

</form>
