<?php

use Illuminate\Support\Str;

function gc_metadata_template_suffixes(Ds\Eloquent\Metadatable $model, Ds\Domain\Theming\MetadataTemplate $template, $schema)
{
    printf('<fieldset class="%s">', $template->classes);
    gc_metadata_schema($schema->settings, $model->metadata);
    printf('</fieldset>');
}

function gc_metadata_schema($type, Ds\Eloquent\MetadataCollection $metadata)
{
    $prefix = Str::random(8);
    if (is_string($type)) {
        $schema = collect();
    } else {
        $schema = collect($type ?? []);
    }
    if ($schema->isEmpty()) {
        return;
    }
    if ($schema->pluck('type')->contains('nav_menu')) {
        $navMenus = \Ds\Models\Node::menus()->get();
    }
    include base_path('resources/views/_metadata.html.php');
}
