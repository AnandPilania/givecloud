<?php

return [
    'pdf' => [
        'enabled' => true,
        'binary' => env('SNAPPY_PDF_BIN', '/usr/local/bin/wkhtmltopdf'),
        'timeout' => false,
        'options' => ['encoding' => 'utf-8'],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => env('SNAPPY_IMG_BIN', '/usr/local/bin/wkhtmltopdf'),
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],
];
