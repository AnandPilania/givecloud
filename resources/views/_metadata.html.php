
<div class="form-horizontal">
    <?php foreach ($schema as $i => $setting): ?>
        <?php if ($i === 0 || $setting->type === 'header'): ?>
            <?php if ($i > 0): ?>
                    </div>
                </div>
            <?php endif ?>
                <div class="panel panel-default setting-panel">
                    <?php if ($setting->type === 'header'): ?>
                        <div class="panel-heading"><?= dangerouslyUseHTML($setting->content) ?></div>
                    <?php endif ?>
                    <div class="panel-body">

            <?php if ($setting->type === 'header') continue ?>
        <?php endif ?>

        <?php if ($setting->type === 'info' || $setting->type === 'warning' || $setting->type === 'danger'): ?>
            <div class="alert alert-<?= dangerouslyUseHTML($setting->type) ?>"><?= dangerouslyUseHTML($setting->content) ?></div>

            <?php continue ?>
        <?php endif ?>

        <div class="form-group setting">
            <label class="col-sm-3 control-label <?php if ($setting->info): ?>has-info<?php endif ?>">
                <?= e($setting->label) ?>:

                <?php if ($setting->info): ?>
                    <p style="line-height:1.1"><small class="text-muted"><?= dangerouslyUseHTML($setting->info) ?></small></p>
                <?php endif; ?>
            </label>
            <div class="col-sm-9">

                <?php if ($setting->type == 'text'): ?>
                    <input type="text" class="form-control" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" placeholder="<?= e($setting->placeholder ?? '') ?>" />

                <?php elseif ($setting->type == 'oembed'): ?>
                    <input type="text" class="form-control" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[oembed:<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" />

                <?php elseif ($setting->type == 'select'): ?>
                    <select class="form-control" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>">
                        <?php if (!isset($setting->allow_blank) || $setting->allow_blank): ?>
                            <option value=""><?php if (is_string($setting->allow_blank)) echo $setting->allow_blank ?></option>
                        <?php endif ?>
                        <?php foreach ((array)$setting->options as $value => $option): ?>
                            <?php $option_label = data_get($option, "label", is_string($option) ? $option : '') ?>
                            <?php $option_disabled = data_get($option, "disabled", false) ?>
                            <option value="<?= e($value) ?>" <?php if ($option_disabled) echo "disabled" ?> <?= e(volt_selected($value, $metadata($setting->name, $setting->default))); ?>><?= e($option_label) ?></option>
                        <?php endforeach ?>
                    </select>

                <?php elseif ($setting->type == 'multi'): ?>
                    <select class="form-control selectize auto-height" multiple <?php if (!isset($setting->allow_blank) || $setting->allow_blank): ?>data-allow-blank="true"<?php endif ?> name="metadata[<?= e($setting->name) ?>][]" id="bucket_<?= e($setting->name) ?>">
                        <?php foreach ((array)$setting->options as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= e(volt_selected($value, $metadata($setting->name, $setting->default))); ?>><?= e($label) ?></option>
                        <?php endforeach ?>
                    </select>

                <?php elseif ($setting->type == 'multi-check'): ?>
                    <div class="row">
                        <?php $option = 0; ?>
                        <input type="hidden" name="metadata[<?= e($setting->name) ?>][0]" value="">
                        <?php foreach ((array)$setting->options as $value => $label): ?>
                            <div class="col-xs-12 col-md-6 col-lg-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="metadata[<?= e($setting->name) ?>][<?= e($option++) ?>]" value="<?= e($value) ?>" <?= e(volt_checked($value, $metadata($setting->name, $setting->default))); ?> > <?= e($label) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                <?php elseif ($setting->type == 'selectize'): ?>
                    <input type="text" class="selectize" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" placeholder="<?= e($setting->placeholder ?? '') ?>">

                <?php elseif ($setting->type == 'selectize-tags'): ?>
                    <input type="text" class="selectize-tags" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" placeholder="<?= e($setting->placeholder ?? '') ?>">

                <?php elseif ($setting->type == 'number'): ?>
                    <input type="number" class="form-control" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" style="width:145px;" />

                <?php elseif ($setting->type == 'on-off'): ?>
                    <input type="hidden" name="metadata[bool:<?= e($setting->name) ?>]" value="0"> <!-- HACK <<< ensure that checkbox fields are alwasy returned, even when turned off -->
                    <input type="checkbox" class="switch" value="1" name="metadata[bool:<?= e($setting->name) ?>]" <?= e(($metadata($setting->name, $setting->default) == 1) ? 'checked' : '') ?> id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>">

                <?php elseif ($setting->type == 'link'): ?>
                    <input type="text" class="form-control ds-urls" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" />

                <?php elseif ($setting->type == 'product'): ?>
                    <input type="text" class="form-control ds-products auto-height" <?php if ($setting->multiple) echo 'multiple="multiple"' ?> <?php if (!is_null($setting->is_donation)) echo 'data-is-donation="' . ($setting->is_donation ? 1 : 0) . '"' ?> value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e(($setting->multiple) ? 'products' : 'product') ?>:<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" />

                <?php elseif ($setting->type == 'variant'): ?>
                    <input type="text" class="form-control ds-variants auto-height" <?php if ($setting->multiple) echo 'multiple="multiple"' ?> <?php if (!is_null($setting->is_donation)) echo 'data-is-donation="' . ($setting->is_donation ? 1 : 0) . '"' ?> value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e(($setting->multiple) ? 'variants' : 'variant') ?>:<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" />

                <?php elseif ($setting->type == 'category'): ?>
                    <input type="text" class="form-control ds-categories" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[category:<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" />

                <?php elseif ($setting->type == 'fundraising_page'): ?>
                    <select name="metadata[<?= e($setting->name) ?>]" class="form-control selectize" placeholder="Choose a Fundraiser...">
                        <option value=""></option>
                        <?php foreach (\Ds\Models\FundraisingPage::websiteType()->active()->get() as $fundraiser): ?>
                            <option value="<?= e($fundraiser->id) ?>" <?= e(volt_selected($fundraiser->id, $metadata($setting->name, $setting->default))); ?> ><?= e($fundraiser->title) ?></option>
                        <?php endforeach; ?>
                    </select>

                <?php elseif ($setting->type == 'pledge_campaign' || $setting->type == 'pledge-campaign'): ?>
                    <select name="metadata[<?= e(($setting->multiple) ? 'pledge-campaigns' : 'pledge-campaign') ?>:<?= e($setting->name) ?>]" class="form-control selectize" placeholder="Choose a Pledge Campaign...">
                        <option value=""></option>
                        <?php foreach (\Ds\Models\PledgeCampaign::all() as $_pledge_campaign): ?>
                            <option value="<?= e($_pledge_campaign->id) ?>" <?= e(volt_selected($_pledge_campaign->id, $metadata($setting->name, $setting->default))); ?> ><?= e($_pledge_campaign->name) ?></option>
                        <?php endforeach; ?>
                    </select>

                <?php elseif ($setting->type == 'nav_menu'): ?>
                    <select name="metadata[<?= e($setting->name) ?>]" class="form-control selectize" placeholder="Choose a Menu...">
                        <option value=""></option>
                        <?php foreach ($navMenus as $node): ?>
                            <option value="<?= e($node->id) ?>" <?= e(volt_selected($node->id, $metadata($setting->name, $setting->default))); ?> ><?= e($node->title) ?></option>
                        <?php endforeach; ?>
                    </select>

                <?php elseif ($setting->type == 'textarea'): ?>
                    <textarea class="form-control" style="height:100px;" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>"><?= e($metadata($setting->name, $setting->default)) ?></textarea>

                <?php elseif ($setting->type == 'bigText'): ?>
                    <textarea class="form-control code" style="height:100px;" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>"><?= e($metadata($setting->name, $setting->default)) ?></textarea>

                <?php elseif ($setting->type == 'html'): ?>
                    <textarea class="form-control html" style="height:200px;" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>"><?= e($metadata($setting->name, $setting->default)) ?></textarea>

                <?php elseif ($setting->type == 'image'): ?>
                    <?php if (trim($metadata($setting->name, $setting->default)) !== ''): ?><img src="<?= e($metadata($setting->name, $setting->default)) ?>" height="35" style="margin-bottom:7px;" /><?php endif; ?>
                    <div class="input-group">
                        <input type="text" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" class="form-control" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" />
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default image-browser" data-image-browser-output="metadata_<?= e($prefix) ?><?= e($setting->name) ?>"><i class="fa fa-folder-open-o"></i></button>
                        </div>
                    </div>

                <?php elseif ($setting->type == 'color'): ?>
                    <input type="text" class="form-control color-picker" value="<?= e($metadata($setting->name, $setting->default)) ?>" name="metadata[<?= e($setting->name) ?>]" id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" style="width:145px;" />

                <?php elseif ($setting->type == 'css' || $setting->type == 'js' || $setting->type == 'raw-html'): ?>

                    <input type="hidden" name="metadata[<?= e($setting->name) ?>]" value="<?= e($metadata($setting->name, $setting->default)) ?>" />
                    <div id="metadata_<?= e($prefix) ?><?= e($setting->name) ?>" class="code" style="width:100%; height:300px;"></div>
                    <script>
                    spaContentReady(function(){
                        var editor = ace.edit("metadata_<?= e($prefix) ?><?= e($setting->name) ?>");
                        editor.setTheme("ace/theme/tomorrow_night");
                        editor.getSession().setMode("ace/mode/<?php if ($setting->type == 'js'): ?>javascript<?php elseif ($setting->type == 'css'): ?>css<?php elseif ($setting->type == 'raw-html'): ?>html<?php endif; ?>");
                        editor.setOption("enableEmmet", true);

                        var input = $('#settings_form input[name="metadata[<?= e($setting->name) ?>]"]');

                        editor.getSession().setValue(input.val());
                        input.closest('form').submit(function() {
                            input.val(editor.getSession().getValue());
                        });

                    });
                    </script>

                <?php elseif ($setting->type == 'event-date'): ?>
                    <div class="metadata-event-date-control form-inline">
                        <?php
                            $eventDateStart = toLocalFormat($metadata("{$setting->name}_start", toUtc('8am')), 'datetime');
                            $eventDateEnd = toLocalFormat($metadata("{$setting->name}_end", toUtc('5pm')), 'datetime');
                        ?>
                        <div>
                            <input type="text" class="form-control event-date" name="metadata[datetime:<?= e($setting->name) ?>_start]" value="<?= e($eventDateStart) ?>">
                            &nbsp;to&nbsp;
                            <input type="text" class="form-control event-date" name="metadata[datetime:<?= e($setting->name) ?>_end]" value="<?= e($eventDateEnd) ?>">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="metadata[bool:<?= e($setting->name) ?>_all_day]" value="0">
                                <input type="checkbox" name="metadata[bool:<?= e($setting->name) ?>_all_day]" value="1" <?= e(volt_checked(1, $metadata("{$setting->name}_all_day"))); ?> >
                                All Day Event
                            </label>
                        </div>
                        <div class="summary mt-3 text-muted italic"></div>
                    </div>

                <?php elseif ($setting->type == 'map-pin'): ?>
                    <div class="bottom-gutter-sm">
                        <a href="javascript:void(0);" class="btn btn-xs btn-info" onclick="$(this).parent().toggle(); $(this).parents('.setting').find('.google_search_form').toggle(); $(this).parents('.setting').find('.gllpSearchField').focus(); return false;"><i class="fa fa-search"></i> Search a Location</a>
                    </div>
                    <fieldset class="gllpLatlonPicker" id="metadata-<?= e($setting->name) ?>">

                        <div class="bottom-gutter-sm google_search_form" style="display:none;">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                <input type="text" class="form-control gllpSearchField">
                                <div class="input-group-addon btn gllpSearchButton">Find</div>
                            </div>
                        </div>

                        <div class="gllpMap">Loading Map...</div>
                        <input type="hidden" name="metadata[<?= e($setting->name) ?>]" value="" />
                        <input type="hidden" class="gllpLatitude" name="metadata[<?= e($setting->name) ?>_latitude]" value="<?= e($metadata($setting->name . '_latitude', '')) ?>"/>
                        <input type="hidden" class="gllpLongitude" name="metadata[<?= e($setting->name) ?>_longitude]" value="<?= e($metadata($setting->name . '_longitude', '')) ?>"/>
                        <input type="hidden" class="gllpZoom" value="2"/>
                    </fieldset>
                <?php endif ?>

                <?php if ($setting->hint): ?>
                    <div style="line-height:1.1"><small class="text-muted"><?= dangerouslyUseHTML($setting->hint) ?></small></div>
                <?php endif ?>
            </div>
        </div>
    <?php endforeach ?>

    <?php if (count($schema)): ?>
        </div>
        </div>
    <?php endif ?>
</div>
