
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <span class="page-header-text"><?= e($kiosk->name) ?></span>

            <div class="pull-right">
                <?php if(user()->can('kiosk.edit')): ?>
                    <button type="button" class="btn btn-success" id="kioskSubmitBtn" onclick="jQuery('#kioskForm button[type=submit]').click()">
                        <i class="fa fa-check fa-fw"></i> Save
                    </button>
                <?php endif ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<?php $settingTemplate = function($label, $type, $name, $value, $options = '', $klass = '', $labelCol = 3, $required = false, $hint = ''){ ?>
    <div class="form-group setting">
        <label class="col-sm-<?= e(max(1, min(11, $labelCol))) ?> control-label">
            <?= e($label) ?>:
        </label>
        <div class="col-sm-<?= e(max(1, 12 - min(11, $labelCol))) ?>">

        <?php if ($type == 'text'): ?>
            <input type="text" class="form-control <?= e($klass) ?>" value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" <?php if ($required) echo 'required' ?>>

        <?php elseif ($type == 'select'): ?>
            <select class="form-control" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>">
                <?php foreach ((array)$options as $option => $label): ?>
                    <option value="<?= e($option) ?>" <?= e(volt_selected($option, $value)); ?>><?= e($label) ?></option>
                <?php endforeach ?>
            </select>

        <?php elseif ($type == 'product'): ?>
            <input type="text" class="form-control ds-products <?= e($klass) ?>" value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" <?php if ($required) echo 'required' ?>>

        <?php elseif ($type == 'products'): ?>
            <input type="text" class="form-control ds-products <?= e($klass) ?>" multiple value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" <?php if ($required) echo 'required' ?>>

        <?php elseif ($type == 'selectize'): ?>
            <input type="text" class="selectize <?= e($klass) ?>" value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" placeholder="<?= e($options) ?>">

        <?php elseif ($type == 'selectize-tags'): ?>
            <input type="text" class="selectize-tags <?= e($klass) ?>" value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" placeholder="<?= e($options) ?>">

        <?php elseif ($type == 'number'): ?>
            <input type="number" class="form-control input-spin" value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" style="width:145px;" />

        <?php elseif ($type == 'pixel'): ?>
            <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>">
            <input type="number" class="form-control input-pixel input-spin" value="<?= e(intval($value) ?: '') ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" style="width:145px;" />

        <?php elseif ($type == 'on-off'): ?>
            <input type="hidden" name="<?= e($name) ?>" value="0">
            <input type="checkbox" class="switch" value="1" name="<?= e($name) ?>" <?= e(($value == 1) ? 'checked' : '') ?>>

        <?php elseif ($type == 'checkbox'): ?>
            <input type="hidden" name="<?= e($name) ?>" value="0">
            <input type="checkbox" value="1" name="<?= e($name) ?>" <?= e(($value == 1) ? 'checked' : '') ?>>

        <?php elseif ($type == 'html'): ?>
            <textarea class="form-control input-html" style="height:200px;" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>"><?= e($value) ?></textarea>

        <?php elseif ($type == 'raw-html'): ?>
            <input type="hidden" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" value="<?= e($value) ?>">
            <div id="code_<?= e(\Illuminate\Support\Str::slug($name)) ?>" class="code" style="width:100%; height:500px;"></div>
            <script>
            spaContentReady(function(){
                var editor = ace.edit("code_<?= e(\Illuminate\Support\Str::slug($name)) ?>");
                editor.setTheme("ace/theme/tomorrow_night");
                editor.getSession().setMode("ace/mode/html");
                editor.setOption("enableEmmet", true);

                var input = $('#<?= e(\Illuminate\Support\Str::slug($name)) ?>');

                editor.getSession().setValue(input.val());
                input.closest('form').submit(function() {
                    input.val(editor.getSession().getValue());
                });
            });
            </script>

        <?php elseif ($type == 'image'): ?>
            <?php if(trim($value) !== ''): ?><img src="<?= e($value) ?>" height="35" style="margin-bottom:7px;" /><?php endif; ?>
            <div class="input-group">
                <input type="text" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" class="form-control" value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" />
                <div class="input-group-btn">
                    <button type="button" class="btn btn-default image-browser" data-image-browser-output="<?= e(\Illuminate\Support\Str::slug($name)) ?>"><i class="fa fa-folder-open-o"></i></button>
                </div>
            </div>

        <?php elseif ($type == 'color'): ?>
            <input type="text" class="form-control color-picker" value="<?= e($value) ?>" name="<?= e($name) ?>" id="<?= e(\Illuminate\Support\Str::slug($name)) ?>" style="width:145px;" />

        <?php elseif ($type == 'padding'): ?>
            <div class="input-padding" style="max-width:300px">
                <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>">
                <div class="row">
                    <div class="col-sm-6">
                        <label for="<?= e(\Illuminate\Support\Str::slug($name)) ?>-top">Top</label>
                        <input type="number" class="form-control input-spin" value="<?= e(intval(data_get(explode(' ',$value),0)) ?: '') ?>" id="<?= e(\Illuminate\Support\Str::slug("$name-top")) ?>">
                    </div>
                    <div class="col-sm-6">
                        <label for="<?= e(\Illuminate\Support\Str::slug($name)) ?>-right">Right</label>
                        <input type="number" class="form-control input-spin" value="<?= e(intval(data_get(explode(' ',$value),1)) ?: '') ?>" id="<?= e(\Illuminate\Support\Str::slug("$name-right")) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="<?= e(\Illuminate\Support\Str::slug($name)) ?>-bottom">Bottom</label>
                        <input type="number" class="form-control input-spin" value="<?= e(intval(data_get(explode(' ',$value),2)) ?: '') ?>" id="<?= e(\Illuminate\Support\Str::slug("$name-bottom")) ?>">
                    </div>
                    <div class="col-sm-6">
                        <label for="<?= e(\Illuminate\Support\Str::slug($name)) ?>-left">Left</label>
                        <input type="number" class="form-control input-spin" value="<?= e(intval(data_get(explode(' ',$value),3)) ?: '') ?>" id="<?= e(\Illuminate\Support\Str::slug("$name-left")) ?>">
                    </div>
                </div>
            </div>

        <?php endif ?>
        <?php if ($hint): ?>
            <div class="text-muted">
                <small><?= dangerouslyUseHTML($hint) ?></small>
            </div>
        <?php endif ?>
        </div>
    </div>
<?php }; ?>

<form id="kioskForm" class="form-horizontal" method="post" action="/jpanel/api/v1/kiosks/<?= e($kiosk->id) ?>" role="form">
    <div class="row">
        <div class="col-sm-4 col-md-3">
            <ul class="list-group" role="tablist" data-tabs="tabs">
                <a href="#kiosk" role="tab" data-toggle="tab" class="list-group-item active">Kiosk</a>
                <?php if (isset($kiosk->product->customFields)): ?>
                    <a href="#custom-fields" role="tab" data-toggle="tab" class="list-group-item">Custom Fields</a>
                <?php endif ?>
                <a href="#theme" role="tab" data-toggle="tab" class="list-group-item">Theme</a>
            </ul>
            <ul class="list-group" role="tablist" data-tabs="tabs">
                <a href="#splash-screen" role="tab" data-toggle="tab" class="list-group-item">Splash Screen</a>
                <a href="#checkout-screen" role="tab" data-toggle="tab" class="list-group-item">Checkout Screen</a>
            </ul>
        </div>
        <div class="col-sm-8 col-md-9">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane setting-tab active" id="kiosk">
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Details</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Name',    'text',   'name',                  $kiosk->name,                   [], 'input-lg', 3, true) ?>
                            <?php $settingTemplate('Enabled', 'on-off', 'enabled',               $kiosk->enabled,                [], null,       3) ?>
                            <?php $settingTemplate('Timeout', 'number', 'config[core][timeout]', $kiosk->config('core.timeout'), [], 'input-lg', 3, true, 'The number of minutes after which<br>a donation should be timed out.') ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Product</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Product(s)',               'products',  'product_ids',                  $kiosk->product_ids, [], 'input-lg', 3, true) ?>
                            <?php $settingTemplate('Enable One-Time',          'on-off',    'config[core][is_onetime]',     $kiosk->config('core.is_onetime'), [], null, 3) ?>
                            <?php $settingTemplate('Enable Recurring Monthly', 'on-off',    'config[core][is_monthly]',     $kiosk->config('core.is_monthly'), [], null, 3) ?>
                            <?php $settingTemplate('Monthly Selected',         'on-off',    'config[core][is_recurring]',   $kiosk->config('core.is_recurring'), [], null, 3) ?>
                            <?php $settingTemplate('Amount Presets',           'selectize-tags', 'config[core][amount_presets]', $kiosk->config('core.amount_presets'), 'Comma-separated amounts...', null, 3) ?>
                            <?php $settingTemplate('Default Amount',           'number',    'config[core][default_amount]', $kiosk->config('core.default_amount'), [], 'input-lg', 3, true) ?>
                            <?php $settingTemplate('Enable Cover the Fees',    'on-off',    'config[core][cover_fees]',     $kiosk->config('core.cover_fees'), [], null, 3) ?>
                            <?php $settingTemplate('Cover the Fees Selected',  'on-off',    'config[core][cover_fees_default]', $kiosk->config('core.cover_fees_default'), [], null, 3) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Checkout Features</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Enable Title',           'on-off', 'config[checkout][enable_title]',           $kiosk->config('checkout.enable_title')) ?>
                            <?php $settingTemplate('Enable Supporter Type',  'on-off', 'config[checkout][enable_account_type]',    $kiosk->config('checkout.enable_account_type')) ?>
                            <?php $settingTemplate('Enable Address',         'on-off', 'config[checkout][enable_address]',         $kiosk->config('checkout.enable_address')) ?>
                            <?php $settingTemplate('Enable Phone',           'on-off', 'config[checkout][enable_phone]',           $kiosk->config('checkout.enable_phone')) ?>
                            <?php $settingTemplate('Enable Referral Source', 'on-off', 'config[checkout][enable_referral_source]', $kiosk->config('checkout.enable_referral_source')) ?>
                            <?php $settingTemplate('Enable Comments',        'on-off', 'config[checkout][enable_comments]',        $kiosk->config('checkout.enable_comments')) ?>
                            <?php $settingTemplate('Request Billing Info',   'on-off', 'config[checkout][request_billing]',        $kiosk->config('checkout.request_billing')) ?>
                            <?php $settingTemplate('Require Billing Info',   'on-off', 'config[checkout][require_billing]',        $kiosk->config('checkout.require_billing')) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Tracking</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Source',          'text', 'config[tracking][source]',          $kiosk->config('tracking.source')) ?>
                            <?php $settingTemplate('Medium',          'text', 'config[tracking][medium]',          $kiosk->config('tracking.medium')) ?>
                            <?php $settingTemplate('Campaign',        'text', 'config[tracking][campaign]',        $kiosk->config('tracking.campaign')) ?>
                            <?php $settingTemplate('Term',            'text', 'config[tracking][term]',            $kiosk->config('tracking.term')) ?>
                            <?php $settingTemplate('Content',         'text', 'config[tracking][content]',         $kiosk->config('tracking.content')) ?>
                            <?php $settingTemplate('Referral Source', 'text', 'config[tracking][referral_source]', $kiosk->config('tracking.referral_source')) ?>
                        </div>
                    </div>
                </div>

                <?php if ($kiosk->product): ?>
                <div role="tabpanel" class="tab-pane setting-tab" id="custom-fields">
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Custom Fields</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Enabled', 'on-off', 'config[core][custom_fields]', $kiosk->config('core.custom_fields'), [], null, 1) ?>
                        </div>
                    </div>
                    <ul class="list-group">
                    <?php foreach ($kiosk->product->customFields as $index => $field): ?>
                        <li class="list-group-item clearfix" data-grouped="<?= e((int) $kiosk->config("custom_fields.$field->id")) ?>">
                            <?php if ($index): ?>
                                <div class="pull-right">
                                    <div class="checkbox" style="padding-top: 0; min-height: 20px;">
                                        <label>
                                            <input type="hidden" name="<?= e("config[custom_fields][$field->id]") ?>" value="0">
                                            <input type="checkbox" value="1" name="<?= e("config[custom_fields][$field->id]") ?>" <?= e(($kiosk->config("custom_fields.$field->id") == 1) ? 'checked' : '') ?>>
                                            group with previous field
                                        </label>
                                    </div>
                                </div>
                            <?php endif ?>
                            <strong><?= e($field->name) ?></strong>
                        </li>
                    <?php endforeach ?>
                    </ul>
                </div>
                <?php endif ?>

                <div role="tabpanel" class="tab-pane setting-tab" id="theme">
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Palette</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Primary', 'color', 'config[theme][palette][primary]', $kiosk->config('theme.palette.primary')) ?>
                            <?php $settingTemplate('Success', 'color', 'config[theme][palette][success]', $kiosk->config('theme.palette.success')) ?>
                            <?php $settingTemplate('Warning', 'color', 'config[theme][palette][warning]', $kiosk->config('theme.palette.warning')) ?>
                            <?php $settingTemplate('Failure', 'color', 'config[theme][palette][failure]', $kiosk->config('theme.palette.failure')) ?>
                            <?php $settingTemplate('System',  'color', 'config[theme][palette][system]',  $kiosk->config('theme.palette.system')) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Background</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Color', 'color', 'config[theme][background][color]',     $kiosk->config('theme.background.color')) ?>
                            <?php $settingTemplate('Image', 'image', 'config[theme][background][image_url]', $kiosk->config('theme.background.image_url')) ?>
                        </div>
                    </div>
                    <?php
                        $fontFamilies = [
                            'Arial'        => 'Arial',
                            'Helvetica'    => 'Helvetica',
                            "'Droid Sans'" => "Droid Sans",
                            'Montserrat'   => 'Montserrat',
                        ];

                        $fontWeigths = [
                            'normal' => 'Normal',
                            'bold'   => 'Bold',
                        ];
                    ?>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Primary Heading</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Font',   'select', 'config[theme][primary_heading][font_family]', $kiosk->config('theme.primary_heading.font_family'), $fontFamilies) ?>
                            <?php $settingTemplate('Weight', 'select', 'config[theme][primary_heading][font_weight]', $kiosk->config('theme.primary_heading.font_weight'), $fontWeigths) ?>
                            <?php $settingTemplate('Size',   'pixel',  'config[theme][primary_heading][font_size]',   $kiosk->config('theme.primary_heading.font_size')) ?>
                            <?php $settingTemplate('Color',  'color',  'config[theme][primary_heading][color]',       $kiosk->config('theme.primary_heading.color')) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Secondary Heading</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Font',   'select', 'config[theme][secondary_heading][font_family]', $kiosk->config('theme.secondary_heading.font_family'), $fontFamilies) ?>
                            <?php $settingTemplate('Weight', 'select', 'config[theme][secondary_heading][font_weight]', $kiosk->config('theme.secondary_heading.font_weight'), $fontWeigths) ?>
                            <?php $settingTemplate('Size',   'pixel',  'config[theme][secondary_heading][font_size]',   $kiosk->config('theme.secondary_heading.font_size')) ?>
                            <?php $settingTemplate('Color',  'color',  'config[theme][secondary_heading][color]',       $kiosk->config('theme.secondary_heading.color')) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Body Text</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Font',   'select', 'config[theme][body_text][font_family]', $kiosk->config('theme.body_text.font_family'), $fontFamilies) ?>
                            <?php $settingTemplate('Weight', 'select', 'config[theme][body_text][font_weight]', $kiosk->config('theme.body_text.font_weight'), $fontWeigths) ?>
                            <?php $settingTemplate('Size',   'pixel',  'config[theme][body_text][font_size]',   $kiosk->config('theme.body_text.font_size')) ?>
                            <?php $settingTemplate('Color',  'color',  'config[theme][body_text][color]',       $kiosk->config('theme.body_text.color')) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Field Labels</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Font',   'select', 'config[theme][field_labels][font_family]', $kiosk->config('theme.field_labels.font_family'), $fontFamilies) ?>
                            <?php $settingTemplate('Weight', 'select', 'config[theme][field_labels][font_weight]', $kiosk->config('theme.field_labels.font_weight'), $fontWeigths) ?>
                            <?php $settingTemplate('Size',   'pixel',  'config[theme][field_labels][font_size]',   $kiosk->config('theme.field_labels.font_size')) ?>
                            <?php $settingTemplate('Color',  'color',  'config[theme][field_labels][color]',       $kiosk->config('theme.field_labels.color')) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Primary Button</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Font',          'select',  'config[theme][primary_btn][font_family]',      $kiosk->config('theme.primary_btn.font_family'), $fontFamilies) ?>
                            <?php $settingTemplate('Weight',        'select',  'config[theme][primary_btn][font_weight]',      $kiosk->config('theme.primary_btn.font_weight'), $fontWeigths) ?>
                            <?php $settingTemplate('Size',          'pixel',   'config[theme][primary_btn][font_size]',        $kiosk->config('theme.primary_btn.font_size')) ?>
                            <?php $settingTemplate('Background',    'color',   'config[theme][primary_btn][background_color]', $kiosk->config('theme.primary_btn.background_color')) ?>
                            <?php $settingTemplate('Color',         'color',   'config[theme][primary_btn][color]',            $kiosk->config('theme.primary_btn.color')) ?>
                            <?php $settingTemplate('Padding',       'padding', 'config[theme][primary_btn][padding]',          $kiosk->config('theme.primary_btn.padding')) ?>
                            <?php $settingTemplate('Border Radius', 'pixel',   'config[theme][primary_btn][border_radius]',    $kiosk->config('theme.primary_btn.border_radius')) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Secondary Button</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Font',          'select',  'config[theme][secondary_btn][font_family]',      $kiosk->config('theme.secondary_btn.font_family'), $fontFamilies) ?>
                            <?php $settingTemplate('Weight',        'select',  'config[theme][secondary_btn][font_weight]',      $kiosk->config('theme.secondary_btn.font_weight'), $fontWeigths) ?>
                            <?php $settingTemplate('Size',          'pixel',   'config[theme][secondary_btn][font_size]',        $kiosk->config('theme.secondary_btn.font_size')) ?>
                            <?php $settingTemplate('Background',    'color',   'config[theme][secondary_btn][background_color]', $kiosk->config('theme.secondary_btn.background_color')) ?>
                            <?php $settingTemplate('Color',         'color',   'config[theme][secondary_btn][color]',            $kiosk->config('theme.secondary_btn.color')) ?>
                            <?php $settingTemplate('Padding',       'padding', 'config[theme][secondary_btn][padding]',          $kiosk->config('theme.secondary_btn.padding')) ?>
                            <?php $settingTemplate('Border Radius', 'pixel',   'config[theme][secondary_btn][border_radius]',    $kiosk->config('theme.secondary_btn.border_radius')) ?>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane setting-tab" id="splash-screen">
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Content</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Enabled', 'on-off', 'config[splash_screen][enabled]', $kiosk->config('splash_screen.enabled')) ?>

                            <?php if ($kiosk->config('splash_screen.type') === 'advanced'): ?>
                                <?php $settingTemplate('Content', 'raw-html', 'config[splash_screen][content]', $kiosk->config('splash_screen.content')) ?>
                            <?php else: ?>
                                <?php $settingTemplate('Content', 'html', 'config[splash_screen][content]', $kiosk->config('splash_screen.content')) ?>
                            <?php endif ?>

                            <?php
                                $splashScreenTypes = [
                                    ''             => 'Default',
                                    'advanced'     => 'Raw HTML',
                                ];
                            ?>

                            <?php $settingTemplate('Type', 'select', 'config[splash_screen][type]',    $kiosk->config('splash_screen.type'), $splashScreenTypes) ?>
                        </div>
                    </div>
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Background</div>
                        <div class="panel-body">
                            <?php $settingTemplate('Color', 'color', 'config[splash_screen][background][color]',     $kiosk->config('splash_screen.background.color')) ?>
                            <?php $settingTemplate('Image', 'image', 'config[splash_screen][background][image_url]', $kiosk->config('splash_screen.background.image_url')) ?>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane setting-tab" id="checkout-screen">
                    <div class="panel panel-default setting-panel">
                        <div class="panel-heading">Content</div>
                        <div class="panel-body">
                            <?php
                                $swipeLocations = [
                                    'top-left'      => 'Top Left',
                                    'top-right'     => 'Top Right',
                                    'middle-left'   => 'Middle Left',
                                    'middle-right'  => 'Middle Right',
                                    'bottom-left'   => 'Bottom Left',
                                    'bottom-right'  => 'Bottom Right',
                                ];
                            ?>
                            <?php $settingTemplate('Heading Text',        'text',   'config[checkout_screen][heading_text]',    $kiosk->config('checkout_screen.heading_text')) ?>
                            <?php $settingTemplate('Pay Now Button Text', 'text',   'config[checkout_screen][paynow_btn_text]', $kiosk->config('checkout_screen.paynow_btn_text')) ?>
                            <?php $settingTemplate('Cancel Button Text',  'text',   'config[checkout_screen][cancel_btn_text]', $kiosk->config('checkout_screen.cancel_btn_text')) ?>
                            <?php $settingTemplate('Thanks Text',         'text',   'config[checkout_screen][thanks_text]',     $kiosk->config('checkout_screen.thanks_text')) ?>
                            <?php $settingTemplate('Donation Text',       'text',   'config[checkout_screen][donation_text]',   $kiosk->config('checkout_screen.donation_text')) ?>
                            <?php $settingTemplate('Swipe Location',      'select', 'config[checkout_screen][swipe_location]',  $kiosk->config('checkout_screen.swipe_location'), $swipeLocations) ?>
                        </div>
                    </div>
                </div>

            </div>
            <button type="submit" class="sr-only">Save</button>
        </div>
    </div>
</form>


<style>
    .form-group.setting .text-muted {line-height: 1.2; margin-top: 4px;}
    li.list-group-item[data-grouped="0"]:first-child {margin-top: 0;}
    li.list-group-item[data-grouped="0"] {margin-top: 10px;}
</style>
<script>
spaContentReady(function($){

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('a[data-toggle="tab"]').removeClass('active');
        $(e.target).addClass('active');
    })

    $('li.list-group-item input[type="checkbox"]').on('change', function(){
        $(this).parents('.list-group-item').attr('data-grouped',
            $(this).is(':checked') ? '1' : '0'
        );
    });


    $('.input-html').givecloudeditor();

    var $form = $('#kioskForm');
    var $submitBtn = Ladda.create(document.getElementById('kioskSubmitBtn'));

    $form.on('submit', function(event) {
        event.preventDefault();

        $submitBtn.start();
        axios({
            url: this.action,
            method: 'PATCH',
            headers: {
                'Content-type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: $form.serialize()
        }).then(function(res) {
            window.toastr.success('Kiosk saved.');
        }).catch(function(err) {
            try {
                window.toastr.error(err.response.data.message);
            } catch(e) {
                try {
                    window.toastr.error(err.message);
                } catch(e) {
                    window.toastr.error(err);
                }
            }
        }).finally(function(){
            $submitBtn.stop();
        });
    });

});
</script>
