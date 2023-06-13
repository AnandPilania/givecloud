<?php $serif_font_list = [
    "Noto Serif JP"    => "Noto Serif JP",
    "Playfair Display" => "Playfair Display",
    "Roboto Slab"      => "Roboto Slab",
    "Source Serif Pro" => "Source Serif Pro"
]; ?>

<?php $sans_serif_font_list = [
    "Lato"             => "Lato",
    "Montserrat"       => "Montserrat",
    "Open Sans"        => "Open Sans",
    "Oswald"           => "Oswald",
    "Raleway"          => "Raleway",
    "Roboto"           => "Roboto",
    "Roboto Condensed" => "Roboto Condensed",
    "Ubuntu"           => "Ubuntu"
]; ?>

<?php foreach ($category->settings as $i => $setting): ?>

    <?php if ($i === 0 || $setting->type === 'header'): ?>
        <?php if ($i > 0): ?>
                </div>
            </div>
        <?php endif ?>
            <div class="panel panel-default setting-panel form-horizontal">
                <?php if ($setting->type === 'header'): ?>
                    <div class="panel-heading"><?= dangerouslyUseHTML($setting->content) ?></div>
                <?php endif ?>
                <div class="panel-body">

        <?php if ($setting->type === 'header') { continue; } ?>
    <?php endif ?>

    <?php if ($setting->type === 'info' || $setting->type === 'warning' || $setting->type === 'danger'): ?>
        <div class="alert alert-<?= dangerouslyUseHTML($setting->type) ?>"><?= dangerouslyUseHTML($setting->content) ?></div>

        <?php continue ?>
    <?php endif ?>

    <div class="form-group setting" data-search="<?= e(strtolower($setting->label)) ?> <?= e(strtolower($setting->info)) ?>">
        <label class="col-md-4 control-label <?= e((in_array($setting->type,['css','js','raw-html','html']))?'text-left':'col-sm-4') ?>">
            <?= e(($setting->label) ? $setting->label.':' : '') ?>

            <?php if($setting->info): ?>
                <p><small class="text-muted"><?= dangerouslyUseHTML($setting->info) ?></small></p>
            <?php endif; ?>

            <?php if(user()->can('template.edit') && $setting->editable): ?>
                <div><a href="/jpanel/design/customize/edit?name=<?= e($setting->name) ?>"><i class="fa fa-pencil-square"></i> Edit</a></div>
            <?php endif; ?>
        </label>
        <div class="col-md-8 <?= e((in_array($setting->type,['css','js','raw-html','html']))?'text-left':'col-sm-8') ?>">

            <?php
                /* sizing is only available for text & select */
                $width = '100%';
                if (isset($setting->size)) {
                    if ($setting->size == 'xs') {
                        $width = '60px';
                    } else if ($setting->size == 'sm') {
                        $width = '180px';
                    } else if ($setting->size == 'md') {
                        $width = '300px';
                    }
                }
            ?>

            <?php if ($setting->type == 'text'): ?>
                <input type="text" style="width:<?= e($width) ?>;" class="form-control" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" placeholder="<?= e($setting->placeholder) ?>" />

            <?php elseif ($setting->type == 'font'): ?>
                <div class="font-form-control" id="bucket_<?= e($setting->name) ?>">
                    <input type="hidden" name="settings[<?= e($setting->name) ?>]" value="<?= e($setting->value) ?>">
                    <select class="form-control d-inline-block" data-sample-text="Sample text" style="width:<?= e($width) ?>;">
                        <optgroup label="Serif">
                            <?php foreach($serif_font_list as $id => $label): ?>
                                <option value="<?= e($id) ?>" <?= e(volt_selected($id, $setting->value)); ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Sans Serif">
                            <?php foreach($sans_serif_font_list as $id => $label): ?>
                                <option value="<?= e($id) ?>" <?= e(volt_selected($id, $setting->value)); ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Other">
                                <option value="">Custom font</option>
                        </optgroup>
                    </select>
                    <input type="text" class="form-control d-inline-block" style="display:none;width:300px;margin-right:1px;vertical-align:top">
                    <div class="font-preview d-inline-block" style="margin-left:10px;"></div>
                </div>

            <?php elseif ($setting->type == 'select'): ?>
                <select class="form-control<?php if (isset($setting->allow_other) && $setting->allow_other) echo ' other-dropdown' ?>" style="width:<?= e($width) ?>;" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" <?php if (isset($setting->allow_other) && $setting->allow_other) echo 'data-value="'.e($setting->value).'"' ?>>
                    <?php if (!isset($setting->allow_blank) || $setting->allow_blank): ?>
                        <option value=""></option>
                    <?php endif ?>
                    <?php foreach ((array)$setting->options as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= e(volt_selected($value, $setting->value)); ?>><?= e($label) ?></option>
                    <?php endforeach ?>
                </select>

            <?php elseif ($setting->type == 'multi'): ?>
                <select class="form-control selectize" multiple style="width:<?= e($width) ?>;" name="settings[<?= e($setting->name) ?>][]" id="bucket_<?= e($setting->name) ?>">
                    <?php if (!isset($setting->allow_blank) || $setting->allow_blank): ?>
                        <option value=""></option>
                    <?php endif ?>
                    <?php foreach ((array)$setting->options as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= e(volt_selected($value, $setting->value)); ?>><?= e($label) ?></option>
                    <?php endforeach ?>
                </select>

            <?php elseif ($setting->type == 'multi-check'): ?>
                <div class="row">
                    <?php foreach ((array)$setting->options as $value => $label): ?>
                        <div class="col-xs-12 col-md-6 col-lg-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="settings[<?= e($setting->name) ?>][]" value="<?= e($value) ?>" <?= e(volt_checked($value, $setting->value)); ?> > <?= e($label) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

            <?php elseif ($setting->type == 'multi-custom'): ?>
                <select class="form-control selectize-tags" multiple style="width:<?= e($width) ?>;" name="settings[<?= e($setting->name) ?>][]" id="bucket_<?= e($setting->name) ?>">
                    <?php if (!isset($setting->allow_blank) || $setting->allow_blank): ?>
                        <option value=""></option>
                    <?php endif ?>
                    <?php $all_values = array_unique(array_merge((array)$setting->value, array_values((array)$setting->options))) ?>
                    <?php foreach ($all_values as $key => $value): ?>
                        <option value="<?= e($value) ?>" <?= e(volt_selected($value, $setting->value)); ?>><?= e($value) ?></option>
                    <?php endforeach ?>
                </select>

            <?php elseif ($setting->type == 'number'): ?>
                <input type="number" class="form-control" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" style="width:145px;" />

            <?php elseif ($setting->type == 'on-off'): ?>
                <input type="hidden" name="settings[<?= e($setting->name) ?>]" value="0"> <!-- HACK <<< ensure that checkbox fields are alwasy returned, even when turned off -->
                <input type="checkbox" class="switch" value="1" name="settings[<?= e($setting->name) ?>]" <?= e(($setting->value == 1) ? 'checked' : '') ?>>

            <?php elseif ($setting->type == 'link'): ?>
                <input type="text" class="form-control ds-urls" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" />

            <?php elseif ($setting->type == 'category'): ?>
                <input type="text" class="form-control ds-categories" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" />

            <?php elseif ($setting->type == 'product'): ?>
                <input type="text" class="form-control ds-products" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" />

            <?php elseif ($setting->type == 'bigText'): ?>
                <textarea class="form-control code" style="height:100px;" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>"><?= e($setting->value) ?></textarea>

            <?php elseif ($setting->type == 'html'): ?>
                <textarea class="form-control html" style="height:200px;" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>"><?= e($setting->value) ?></textarea>

            <?php elseif ($setting->type == 'image'): ?>
                <?php if(trim($setting->value) !== ''): ?><img src="<?= e($setting->value) ?>" style="margin-bottom:7px; height:35px;" /><?php endif; ?>
                <div class="input-group">
                    <input type="text" id="bucket_<?= e($setting->name) ?>" class="form-control" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" />
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default image-browser" data-image-browser-output="bucket_<?= e($setting->name) ?>"><i class="fa fa-folder-open-o"></i></button>
                    </div>
                </div>

            <?php elseif ($setting->type == 'media'): ?>
                <div class="">
                    <?php if(trim($setting->value) !== ''): ?>
                        <?php $_media = \Ds\Models\Media::find($setting->value); ?>
                    <?php else: ?>
                        <?php $_media = null; ?>
                    <?php endif; ?>

                    <div id="bucket_<?= e($setting->name) ?>-preview" style="<?php if($_media): ?>background-image:url('<?= e($_media->thumbnail_url) ?>');<?php endif; ?> background-color:#eee; width:140px; background-size:cover; background-position:center center; height:60px; border-radius:5px; float:left; margin:0px 10px 0px 0px">
                    </div>
                    <div>
                        <input type="hidden" id="bucket_<?= e($setting->name) ?>" class="form-control" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" />
                        <button type="button" class="btn btn-sm btn-info image-browser" data-preview="#bucket_<?= e($setting->name) ?>-preview" data-input="#bucket_<?= e($setting->name) ?>"><i class="fa fa-folder-open-o"></i> Change</button><br>
                        <button type="button" class="btn btn-sm btn-default image-browser-clear"><i class="fa fa-times"></i> Remove</button>
                    </div>
                </div>

            <?php elseif ($setting->type == 'color'): ?>
                <input type="text" class="form-control color-picker" value="<?= e($setting->value) ?>" name="settings[<?= e($setting->name) ?>]" id="bucket_<?= e($setting->name) ?>" style="width:145px;" />

            <?php elseif ($setting->type == 'css' || $setting->type == 'js' || $setting->type == 'raw-html'): ?>
                <?php
                    if ($setting->type === 'js') {
                        $mode = 'javascript';
                    } elseif ($setting->name == 'css_overrides') {
                        $mode = 'scss';
                    } else {
                        $mode = 'html';
                    }
                ?>

                <input id="bucket_<?= e($setting->name) ?>-input" type="hidden" name="settings[<?= e($setting->name) ?>]" value="<?= e($setting->value) ?>" />
                <div id="bucket_<?= e($setting->name) ?>" class="code-editor" style="width:100%; height:300px;" data-input="#bucket_<?= e($setting->name) ?>-input" data-theme="tomorrow_night" data-mode="<?= e($mode) ?>"></div>

            <?php endif ?>
        </div>
    </div>
<?php endforeach ?>

<?php if (count($category->settings)): ?>
    </div>
    </div>
<?php endif ?>
