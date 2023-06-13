<?php

use Phan\Issue;

return [

    'target_php_version'     => '7.3',
    'check_docblock_signature_return_type_match' => false,
    'dead_code_detection'    => false,
    'color_issue_messages'   => true,
    'progress_bar'           => true,
    'backward_compatibility_checks' => false,
    'quick_mode'             => false,
    'minimum_severity'       => Issue::SEVERITY_CRITICAL,
    'exclude_file_regex'     => '@^vendor/.*/(tests?|Tests?)/@',
    //'warn_about_undocumented_throw_statements' => true,

    'suppress_issue_types' => [
        'PhanTypeMismatchArgument',
        'PhanUndeclaredMethod',
    ],

    'plugins' => [
        'AlwaysReturnPlugin',
        'UnreachableCodePlugin',
        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        'PregRegexCheckerPlugin',
    ],

    'directory_list' => [
        '_',
        'app',
        'bootstrap',
        'config',
        'routes',
        'tests',
        'vendor/',
        '.phan/stubs',
    ],

    'exclude_analysis_directory_list' => [
        '.phan/stubs',
        'vendor/',
    ],

];
