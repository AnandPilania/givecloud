@php
$fonts = collect([ volt_setting('sans_font'), volt_setting('serif_font'), volt_setting('btn_font') ])
    ->unique()
    ->map(function($font){
        return urlencode($font).':300,300i,400,400i,700,700i,900,900i';
    });

$settings = [
    'user_can_add'      => user()->can('file.add'),
    'user_can_edit'     => user()->can('file.edit'),
    'dpo_is_enabled'    => dpo_is_enabled(),
    'default_color_1'   => sys_get('default_color_1'),
    'default_color_2'   => sys_get('default_color_2'),
    'default_color_3'   => sys_get('default_color_3'),
    'app_assets_url'    => app_asset_url(null, false),
    'jpanel_assets_url' => jpanel_asset_url(null, false),
    'tinymce_css'       => [
        'https://fonts.googleapis.com/css?family=' . $fonts->implode('|'),
        app('theme')->asset('styles/theme.scss')->public_url,
    ],
    'tinymce_classes'   => implode(' ', (array) ($tinymce_classes ?? null)),
    'tinymce_fonts'     => [volt_setting('sans_font'), volt_setting('serif_font')],
    'tinymce_templates' => app('theme')->getContentTemplates(),
];
@endphp

<!-- Custom Theme JavaScript -->
<script>
var Givecloud = window.Givecloud || {};
Givecloud.settings = @json($settings);
</script>
