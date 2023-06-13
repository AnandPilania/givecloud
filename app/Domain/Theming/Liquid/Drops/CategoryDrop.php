<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Str;

class CategoryDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'handle' => Str::slug($source->name),
            'name' => $source->name,
            'body' => do_shortcode($source->description),
            'url' => $source->abs_url,
            'share_url' => $source->share_url,
        ];
    }

    public function feature_image()
    {
        return $this->source->photo ?? null;
    }

    public function products()
    {
        return Drop::collectionFactory($this->source->products, 'Product', [
            'categories' => [],
        ]);
    }

    public function sub_categories()
    {
        return Drop::collectionFactory($this->source->childCategories, 'Category', $this->overrides);
    }

    public function metadata()
    {
        return $this->source->metadata;
    }
}
