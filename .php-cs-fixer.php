<?php

$finder = PhpCsFixer\Finder::create()
    ->notPath('bootstrap/app.php')
    ->notPath('bootstrap/cache')
    ->notPath('public')
    ->notPath('scripts')
    ->notPath('storage')
    ->notPath('vendor')
    ->in(__DIR__)
    ->name('*.php')
    ->notName('*.blade.php')
    ->notName('*.html.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->ignoreVCSIgnored(true);

return Givecloud\PhpStyles\Config::make($finder, [
    // we need to temporarily disable these rules to prevent fixer
    // from rewriting any "#[\ReturnTypeWillChange]" attributes we add
    // in preparation for the eventual upgrade to PHP 8.1
    'single_line_comment_style' => null,
    'PhpCsFixerCustomFixers/comment_surrounded_by_spaces' => null,
]);
