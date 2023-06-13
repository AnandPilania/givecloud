
<?php if (! isDev() && app()->bound('bugsnag')): ?>
<script src="//d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
<script>Bugsnag.start(<?= dangerouslyUseHTML(json_encode([
    'apiKey' => config('services.bugsnag.js_api_key'),
    'releaseStage' => site('version'),
    'user' => [
        'id' => sys_get('ds_account_name'),
        'name' => sys_get('ds_account_name'),
    ],
    'metadata' => [
        'cart' => session('cart_uuid'),
    ],
])); ?>);</script>
<?php endif; ?>

<?php if (! isDev() && $logrocket_template_name): ?>
<script src="https://cdn.lr-ingest.com/LogRocket.min.js"></script>
<script>LogRocket.init('rouoyn/classic-forms', <?= dangerouslyUseHTML(json_encode([
    'console' => [
        'isEnabled' => [
            'log' => false,
            'debug' => false,
        ],
    ],
    'dom' => [
        'inputSanitizer' => true,
    ],
    'network' => [
        'isEnabled' => false,
    ],
])); ?>);LogRocket.identify(null, <?= dangerouslyUseHTML(json_encode([
    'site' => sys_get('ds_account_name'),
    'template' => $logrocket_template_name,
])); ?>);</script>
<?php endif; ?>
